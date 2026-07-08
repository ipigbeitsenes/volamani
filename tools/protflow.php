<?php
// Buyer-protection flow checks — run via: php artisan tinker < tools/protflow.php
use App\Models\{User, Vendor, Wallet, Escrow, Product, Payment, Chargeback, WalletReserve, WalletLedger, Dispute, Setting};
use App\Enums\{EscrowStatus, ChargebackStatus, Status, DisputeStatus, DisputeReason, StrikeReason};
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\Artisan;

$ws   = app(WalletService::class);
$prod = Product::first();
$vendors = Vendor::with('user')->limit(2)->get();
$V1 = $vendors[0];
$V2 = $vendors[1] ?? $vendors[0];
$buyer = User::whereNotIn('id', [$V1->user_id, $V2->user_id])->first();

$freshWallet = function (Vendor $v) use ($ws) {
    $w = $ws->getOrCreate($v->user);
    WalletLedger::where('wallet_id', $w->id)->delete();
    $w->update(['balance' => 0, 'escrow_balance' => 0, 'reserve_balance' => 0, 'pending_withdrawal' => 0]);
    return $w->fresh();
};

$mkEscrow = function (Vendor $v, Wallet $w, int $earn, string $status) use ($prod, $buyer) {
    return Escrow::create([
        'escrowable_type' => Product::class, 'escrowable_id' => $prod->id,
        'buyer_id' => $buyer->id, 'vendor_id' => $v->id, 'wallet_id' => $w->id,
        'total_amount' => $earn, 'platform_fee' => 0, 'vendor_earnings' => $earn,
        'released_amount' => $status === 'released' ? $earn : 0,
        'status' => $status, 'held_at' => now(),
        'released_at' => $status === 'released' ? now() : null,
    ]);
};

echo "===== TEST 1: reserve split + payout =====\n";
try {
    Setting::set('chargeback_reserve_percent', '10', 'integer');
    Setting::set('chargeback_reserve_days', '30', 'integer');
    cache()->flush();

    $w1 = $freshWallet($V1);
    $ws->incrementEscrow($w1, 100000);
    $e1 = $mkEscrow($V1, $w1, 100000, 'holding');
    app(\App\Actions\Escrow\ReleaseEscrowAction::class)->execute($e1);
    $w1->refresh();
    echo "balance=$w1->balance (exp 90000)  reserve=$w1->reserve_balance (exp 10000)\n";
    $r1 = WalletReserve::where('escrow_id', $e1->id)->first();
    echo "reserve_row amount={$r1->amount} status={$r1->status}\n";
    echo "reconcile=" . $w1->reconcile() . " (exp 90000)\n";

    $r1->update(['release_at' => now()->subDay()]);
    Artisan::call('reserve:release');
    $w1->refresh();
    echo "after payout: balance=$w1->balance (exp 100000)  reserve=$w1->reserve_balance (exp 0)  row=" . $r1->fresh()->status . "\n";
    echo "reconcile=" . $w1->reconcile() . " (exp 100000)\n";
} catch (\Throwable $ex) { echo "TEST1 ERROR: " . $ex->getMessage() . "\n"; }

echo "\n===== TEST 2: chargeback clawback (reserve then balance) + strikes =====\n";
try {
    $V2->update(['status' => Status::Active, 'strikes' => 0, 'suspended_for_strikes' => false, 'trust_score' => 0]);
    $w2 = $freshWallet($V2);
    // seed balance 5000 via ledger credit + reserve 3000 via a held row
    $ws->credit($w2, 5000, \App\Enums\TransactionType::Credit, 'seed');
    $ws->incrementReserve($w2, 3000);
    WalletReserve::create(['wallet_id' => $w2->id, 'vendor_id' => $V2->id, 'amount' => 3000, 'status' => 'held', 'release_at' => now()->addDays(30)]);
    $w2->refresh();
    echo "seed: balance=$w2->balance reserve=$w2->reserve_balance\n";

    $pay = Payment::create(['user_id' => $buyer->id, 'payable_type' => Product::class, 'payable_id' => $prod->id, 'gateway' => 'paystack', 'status' => 'success', 'amount' => 7000, 'gateway_reference' => 'CBTEST-1']);
    $e2 = $mkEscrow($V2, $w2, 7000, 'released');
    $e2->update(['status' => EscrowStatus::Released, 'payment_id' => $pay->id]);

    $cb = app(\App\Actions\Chargebacks\OpenChargebackAction::class)->execute($pay, 'CBTEST-1', 7000, 'fraud');
    $w2->refresh();
    echo "chargeback={$cb->reference} clawed={$cb->clawed_back_amount} (exp 7000) unrecovered={$cb->unrecovered_amount} (exp 0)\n";
    echo "wallet after clawback: balance=$w2->balance (exp 1000) reserve=$w2->reserve_balance (exp 0)\n";

    app(\App\Actions\Chargebacks\ResolveChargebackAction::class)->execute($cb->fresh(), ChargebackStatus::Lost, null, 'lost test');
    echo "chargeback status=" . $cb->fresh()->status->value . " (exp lost)  V2 strikes=" . $V2->fresh()->strikes . " (exp 1)\n";

    // two more strikes -> auto-suspend at threshold 3
    $add = app(\App\Actions\Vendors\AddStrikeAction::class);
    $add->execute($V2, StrikeReason::Manual, 'x');
    $add->execute($V2, StrikeReason::Manual, 'y');
    echo "V2 strikes=" . $V2->fresh()->strikes . " (exp 3)  status=" . $V2->fresh()->status->value . " (exp suspended)  suspended_flag=" . ($V2->fresh()->suspended_for_strikes ? '1' : '0') . "\n";
} catch (\Throwable $ex) { echo "TEST2 ERROR: " . $ex->getMessage() . "\n"; }

echo "\n===== TEST 3: dispute SLA auto-escalate =====\n";
try {
    $w1b = $ws->getOrCreate($V1->user);
    $e3 = $mkEscrow($V1, $w1b, 5000, 'disputed');
    $e3->update(['status' => EscrowStatus::Disputed]);
    $d = Dispute::create([
        'escrow_id' => $e3->id, 'buyer_id' => $buyer->id, 'vendor_id' => $V1->id, 'raised_by' => $buyer->id,
        'reason' => DisputeReason::NotDelivered->value, 'description' => 'no delivery at all here',
        'status' => DisputeStatus::AwaitingResponse, 'response_due_at' => now()->subHours(2), 'sla_breached' => false,
    ]);
    Artisan::call('disputes:enforce-sla');
    $d->refresh();
    echo "dispute status=" . $d->status->value . " (exp escalated)  sla_breached=" . ($d->sla_breached ? '1' : '0') . " (exp 1)\n";
} catch (\Throwable $ex) { echo "TEST3 ERROR: " . $ex->getMessage() . "\n"; }

echo "\n===== TEST 4: tier withdrawal cap (New = 100k/day) =====\n";
try {
    $V1->update(['trust_score' => 0]); // New tier
    $w1c = $ws->getOrCreate($V1->user);
    $w1c->update(['balance' => 100000000, 'pending_withdrawal' => 0]); // ₦1,000,000
    $data = ['amount' => 200000, 'bank_name' => 'GTB', 'account_name' => 'V1', 'account_number' => '0123456789'];
    try {
        app(\App\Actions\Wallet\RequestWithdrawalAction::class)->execute($V1->user, $data);
        echo "FAIL: withdrawal of N200,000 was allowed for a New-tier seller (cap N100,000)\n";
    } catch (\Throwable $inner) {
        echo "PASS: blocked -> " . $inner->getMessage() . "\n";
    }
} catch (\Throwable $ex) { echo "TEST4 ERROR: " . $ex->getMessage() . "\n"; }

echo "\n===== TEST 5: tier listing cap =====\n";
try {
    Setting::set('tier_new_max_active_listings', '0', 'integer');
    cache()->flush();
    $V1->update(['trust_score' => 0]);
    echo "New tier max listings now = " . ($V1->trustTier()->maxActiveListings()) . " (exp 0)\n";
    try {
        app(\App\Actions\Products\CreateProductAction::class)->execute($V1, []);
        echo "FAIL: product creation allowed past listing cap\n";
    } catch (\Throwable $inner) {
        echo "PASS: blocked -> " . $inner->getMessage() . "\n";
    }
    Setting::where('key', 'tier_new_max_active_listings')->delete();
} catch (\Throwable $ex) { echo "TEST5 ERROR: " . $ex->getMessage() . "\n"; }

echo "\n===== DONE =====\n";
