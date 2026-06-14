#!/usr/bin/env bash
# Phase 3 end-to-end: physical Buy Now (wallet) -> escrow hold (no timer) ->
# ship -> deliver (fallback timer) -> buyer confirm -> escrow released.
# Wrapped in a transaction and rolled back so demo data is untouched.
cd ~/laravelProjects/volamani || exit 1
docker compose exec -T app php artisan tinker --execute='
use Illuminate\Support\Facades\DB;
DB::beginTransaction();

$buyer   = \App\Models\User::where("email","chioma@example.com")->first();
$product = \App\Models\Product::physical()->whereHas("vendor",fn($q)=>$q->where("slug","pixel-forge-studio"))->with("variants","vendor","physicalDetail")->first();
$variant = $product->variants->firstWhere("stock_quantity",">",0);
$vendorUser = $product->vendor->user;

$walletSvc = app(\App\Services\Wallet\WalletService::class);
$bw = $walletSvc->getOrCreate($buyer); $bw->update(["balance"=>100000000]); // ₦1m
$vw = $walletSvc->getOrCreate($vendorUser);
$vBalBefore = $vw->fresh()->balance; $vEscBefore = $vw->fresh()->escrow_balance;
$stockBefore = $variant->stock_quantity;

$addr = ["ship_to_name"=>"Chioma O","ship_to_phone"=>"08030000000","ship_to_address"=>"12 Test St","ship_to_city"=>"Lagos","ship_to_state"=>"Lagos"];
$res = app(\App\Services\Checkout\PhysicalCheckoutService::class)->place($buyer,$product,$variant,1,$addr,"wallet");
echo "place_status=".$res["status"].PHP_EOL;
$order = $res["order"]->fresh();
echo "order_requires_shipping=".($order->requires_shipping?"yes":"no").PHP_EOL;
echo "order_status_afterpay=".$order->status->value.PHP_EOL;
echo "order_total=".$order->total_amount." (item 4500000 + ship 200000 expected)".PHP_EOL;
echo "shipping_fee=".$order->shipping_fee.PHP_EOL;

$escrow = app(\App\Services\Escrow\EscrowService::class)->forPayable($order);
echo "escrow_status=".$escrow->status->value.PHP_EOL;
echo "escrow_auto_release_at_NULL=".($escrow->auto_release_at===null?"yes":"no")." (physical => should be yes)".PHP_EOL;
echo "stock_delta=".($variant->fresh()->stock_quantity-$stockBefore)." (expect -1)".PHP_EOL;
echo "buyer_balance_after=".$bw->fresh()->balance." (expect 100000000-4700000=95300000)".PHP_EOL;
echo "vendor_escrow_delta=".($vw->fresh()->escrow_balance-$vEscBefore)." (expect +".$order->vendor_earnings.")".PHP_EOL;

$os = app(\App\Services\Orders\OrderService::class);
echo "--- ship ---".PHP_EOL;
echo "markShipped=".($os->markShipped($order->fresh(),"TRK123","GIG")?"ok":"fail").PHP_EOL;
echo "status=".$order->fresh()->status->value." shipped_at=".($order->fresh()->shipped_at?"set":"null").PHP_EOL;

echo "--- deliver ---".PHP_EOL;
echo "markDelivered=".($os->markDelivered($order->fresh())?"ok":"fail").PHP_EOL;
echo "status=".$order->fresh()->status->value." delivered_at=".($order->fresh()->delivered_at?"set":"null").PHP_EOL;
echo "escrow_fallback_armed=".($escrow->fresh()->auto_release_at!==null?"yes":"no")." (after delivery => yes)".PHP_EOL;

echo "--- buyer confirm ---".PHP_EOL;
echo "markComplete=".($os->markComplete($order->fresh(),$buyer)?"ok":"fail").PHP_EOL;
echo "order_status=".$order->fresh()->status->value.PHP_EOL;
echo "escrow_status=".$escrow->fresh()->status->value." (expect released)".PHP_EOL;
echo "vendor_balance_delta=".($vw->fresh()->balance-$vBalBefore)." (expect +".$order->vendor_earnings.")".PHP_EOL;
echo "vendor_escrow_delta_final=".($vw->fresh()->escrow_balance-$vEscBefore)." (expect 0)".PHP_EOL;

DB::rollBack();
echo "ROLLED_BACK".PHP_EOL;
'
