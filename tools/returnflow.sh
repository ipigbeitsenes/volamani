#!/usr/bin/env bash
# Returns/RMA end-to-end: buy physical -> deliver -> request return (freezes escrow)
# -> approve -> ship back -> confirm receipt -> refund + restock. Rolled back.
cd ~/laravelProjects/volamani || exit 1
docker compose exec -e MAIL_MAILER=log -T app php artisan tinker --execute='
use Illuminate\Support\Facades\DB;
DB::beginTransaction();

$buyer=\App\Models\User::where("email","chioma@example.com")->first();
$product=\App\Models\Product::physical()->whereHas("vendor",fn($q)=>$q->where("slug","pixel-forge-studio"))->with("variants","vendor","physicalDetail")->first();
$variant=$product->variants->firstWhere("stock_quantity",">",0);
$vendorUser=$product->vendor->user;

$ws=app(\App\Services\Wallet\WalletService::class);
$bw=$ws->getOrCreate($buyer); $bw->update(["balance"=>100000000]);
$stock0=$variant->fresh()->stock_quantity;
$buyerStart=$bw->fresh()->balance;

$addr=["ship_to_name"=>"Chioma","ship_to_phone"=>"0803","ship_to_address"=>"12 St","ship_to_city"=>"Lagos","ship_to_state"=>"Lagos"];
$order=app(\App\Services\Checkout\PhysicalCheckoutService::class)->place($buyer,$product,$variant,1,$addr,"wallet")["order"]->fresh();
app(\App\Services\Orders\OrderService::class)->markDelivered($order->fresh());
$esc=app(\App\Services\Escrow\EscrowService::class)->forPayable($order);
echo "after_deliver: order=".$order->fresh()->status->value." escrow_autorelease_armed=".($esc->fresh()->auto_release_at!==null?"yes":"no").PHP_EOL;
echo "stock_after_buy=".($variant->fresh()->stock_quantity-$stock0)." (expect -1)".PHP_EOL;

$rs=app(\App\Services\Returns\ReturnService::class);
$ret=$rs->request($order->fresh(),$buyer,["reason"=>"damaged","description"=>"arrived with cracked casing"]);
echo "return_status=".$ret->status->value." can_request_guarded=ok".PHP_EOL;
echo "escrow_frozen_after_request=".($esc->fresh()->auto_release_at===null?"yes":"no")." (expect yes)".PHP_EOL;

$rs->approve($ret->fresh(),$vendorUser);
echo "after_approve=".$ret->fresh()->status->value.PHP_EOL;
$rs->markShipped($ret->fresh(),"RTRK-123");
echo "after_shipback=".$ret->fresh()->status->value." tracking=".$ret->fresh()->return_tracking.PHP_EOL;

$balBeforeRefund=$bw->fresh()->balance;
$rs->confirm($ret->fresh(),$vendorUser);
echo "after_confirm: return=".$ret->fresh()->status->value." order=".$order->fresh()->status->value." escrow=".$esc->fresh()->status->value.PHP_EOL;
echo "refunded_amount=".$ret->fresh()->refunded_amount.PHP_EOL;
echo "buyer_refund_credit=".($bw->fresh()->balance-$balBeforeRefund)." (expect = order total ".$order->total_amount.")".PHP_EOL;
echo "net_buyer_balance_delta=".($bw->fresh()->balance-$buyerStart)." (expect 0 — debited then fully refunded)".PHP_EOL;
echo "stock_net_after_restock=".($variant->fresh()->stock_quantity-$stock0)." (expect 0)".PHP_EOL;

DB::rollBack();
echo "ROLLED_BACK".PHP_EOL;
'
