<?php

namespace Tests\Feature;

use App\Enums\EscrowStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Escrow\EscrowService;
use Database\Factories\VendorFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The escrow money model: hold (vendor escrow_balance, no ledger row) →
 * release (escrow → spendable balance, ledger EscrowRelease) or
 * refund (buyer wallet credit, vendor escrow reversed).
 */
class EscrowLifecycleTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: Order, 1: User, 2: \App\Models\Vendor} */
    private function paidOrder(int $total = 200_000): array
    {
        $buyer  = User::factory()->create();
        $vendor = VendorFactory::new()->create();
        Wallet::create(['user_id' => $buyer->id, 'balance' => 0, 'escrow_balance' => 0]);

        $fee   = (int) round($total * 0.10);
        $order = Order::create([
            'buyer_id'        => $buyer->id,
            'vendor_id'       => $vendor->id,
            'status'          => OrderStatus::Paid,
            'payment_status'  => PaymentStatus::Success,
            'total_amount'    => $total,
            'platform_fee'    => $fee,
            'vendor_earnings' => $total - $fee,
            'currency'        => 'NGN',
        ]);

        return [$order, $buyer, $vendor];
    }

    public function test_hold_opens_escrow_and_parks_vendor_earnings_outside_spendable_balance(): void
    {
        [$order, , $vendor] = $this->paidOrder(200_000);

        $escrow = app(EscrowService::class)->holdForPayable($order);

        $this->assertNotNull($escrow);
        $this->assertSame(EscrowStatus::Holding, $escrow->status);
        $this->assertSame(180_000, (int) $escrow->vendor_earnings);

        $vendorWallet = $vendor->user->wallet()->first();
        $this->assertSame(180_000, (int) $vendorWallet->escrow_balance);
        $this->assertSame(0, (int) $vendorWallet->balance);
        $this->assertCount(0, $vendorWallet->ledgers); // holds never write ledger rows

        // Product order ⇒ business-day auto-release scheduled.
        $this->assertNotNull($escrow->auto_release_at);
        $this->assertTrue($escrow->auto_release_at->isFuture());
    }

    public function test_holding_is_idempotent_per_payable(): void
    {
        [$order] = $this->paidOrder();

        $service = app(EscrowService::class);
        $first   = $service->holdForPayable($order);
        $second  = $service->holdForPayable($order);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, \App\Models\Escrow::count());
    }

    public function test_release_moves_earnings_into_vendor_spendable_balance_with_ledger_entry(): void
    {
        [$order, , $vendor] = $this->paidOrder(200_000);
        $escrow = app(EscrowService::class)->holdForPayable($order);

        app(EscrowService::class)->release($escrow);

        $escrow->refresh();
        $this->assertSame(EscrowStatus::Released, $escrow->status);
        $this->assertSame(0, $escrow->heldAmount());

        $vendorWallet = $vendor->user->wallet()->first();
        $this->assertSame(180_000, (int) $vendorWallet->balance);
        $this->assertSame(0, (int) $vendorWallet->escrow_balance);

        $ledger = $vendorWallet->ledgers()->first();
        $this->assertSame(TransactionType::EscrowRelease, $ledger->type);
        $this->assertSame(180_000, (int) $ledger->amount);
    }

    public function test_refund_credits_buyer_wallet_and_reverses_vendor_escrow(): void
    {
        [$order, $buyer, $vendor] = $this->paidOrder(200_000);
        $escrow = app(EscrowService::class)->holdForPayable($order);

        app(EscrowService::class)->refund($escrow, null, 'Item not as described');

        $escrow->refresh();
        $this->assertSame(EscrowStatus::Refunded, $escrow->status);

        // Buyer gets the full fee-inclusive amount back, as a Refund ledger credit.
        $buyerWallet = $buyer->wallet()->first();
        $this->assertSame(200_000, (int) $buyerWallet->balance);
        $this->assertSame(TransactionType::Refund, $buyerWallet->ledgers()->first()->type);

        // Vendor's pending earnings are gone and nothing became spendable.
        $vendorWallet = $vendor->user->wallet()->first();
        $this->assertSame(0, (int) $vendorWallet->escrow_balance);
        $this->assertSame(0, (int) $vendorWallet->balance);
    }

    public function test_settled_escrow_cannot_be_released_or_refunded_again(): void
    {
        [$order] = $this->paidOrder();
        $escrow  = app(EscrowService::class)->holdForPayable($order);
        app(EscrowService::class)->release($escrow);

        $this->assertFalse($escrow->fresh()->canRelease());
        $this->assertFalse($escrow->fresh()->canRefund());
    }
}
