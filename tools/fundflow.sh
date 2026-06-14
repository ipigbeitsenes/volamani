#!/usr/bin/env bash
# Bank-transfer wallet funding -> admin approve -> wallet credited. Rolled back.
cd ~/laravelProjects/volamani || exit 1
docker compose exec -e MAIL_MAILER=log -T app php artisan tinker --execute='
use Illuminate\Support\Facades\DB;
DB::beginTransaction();

$u = \App\Models\User::create(["name"=>"Funder","email"=>"fund_".uniqid()."@t.co","password"=>"x"]);
$w = \App\Models\Wallet::create(["user_id"=>$u->id,"balance"=>0,"escrow_balance"=>0]);
$amount = 5000000; // N50,000 in kobo

$res = app(\App\Services\Wallet\WalletService::class)->initiateFunding($u, $amount, "bank_transfer");
$funding = $res["funding"];
$payment = \App\Models\Payment::find($funding->payment_id);
echo "funding_status=".$funding->status." payment_gateway=".$payment->gateway->value." payment_status=".$payment->status->value.PHP_EOL;
echo "redirect=".(str_contains($res["redirect"],"bank-transfer")?"bank-transfer page":"?").PHP_EOL;
echo "balance_before=".$w->fresh()->balance.PHP_EOL;

// buyer uploads proof, admin approves
$proof = \App\Models\BankTransferProof::create(["payment_id"=>$payment->id,"user_id"=>$u->id,"bank_name"=>"Access","account_name"=>"Funder","amount"=>$amount,"transfer_date"=>now()->toDateString(),"status"=>"pending"]);
$admin = \App\Models\User::role("admin")->first();
app(\App\Actions\Payment\ApproveBankTransferAction::class)->approve($proof, $admin);

echo "--- after admin approval ---".PHP_EOL;
echo "payment_status=".$payment->fresh()->status->value.PHP_EOL;
echo "funding_status=".$funding->fresh()->status." (expect completed)".PHP_EOL;
echo "wallet_balance=".$w->fresh()->balance." (expect ".$amount.")".PHP_EOL;
$led = \App\Models\WalletLedger::where("wallet_id",$w->id)->latest()->first();
echo "ledger_type=".($led?->type?->value)." amount=".($led?->amount)." balance_after=".($led?->balance_after).PHP_EOL;

DB::rollBack();
echo "ROLLED_BACK".PHP_EOL;
'
