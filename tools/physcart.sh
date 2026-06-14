#!/usr/bin/env bash
# Full physical-cart flow over HTTP: add digital + physical to cart -> checkout
# (wallet) with delivery address -> verify split orders/shipping/escrow/stock,
# then clean up everything it created.
cd ~/laravelProjects/volamani || exit 1
BASE="http://localhost:8000"
JAR=/tmp/physcart.jar
rm -f "$JAR"

DIGITAL_ID=$(docker compose exec -T app php artisan tinker --execute='echo \App\Models\Product::digital()->whereHas("vendor",fn($q)=>$q->where("slug","pixel-forge-studio"))->value("id");' | tr -dc '0-9')
PHYS_ID=13   # 4k-webcam-pro (no variants), pixel-forge-studio

# Baseline + fund buyer
docker compose exec -T app php artisan tinker --execute='
$b=\App\Models\User::where("email","chioma@example.com")->first();
$ws=app(\App\Services\Wallet\WalletService::class);
$bw=$ws->getOrCreate($b);
file_put_contents("/tmp/baseline.txt",json_encode([
  "buyer_bal"=>$bw->balance,
  "max_order"=>(int)\App\Models\Order::max("id"),
  "webcam_stock"=>(int)\App\Models\Product::find('"$PHYS_ID"')->physicalDetail->stock_quantity,
]));
$bw->update(["balance"=>100000000]);
echo "funded; digital_id='"$DIGITAL_ID"' phys_id='"$PHYS_ID"'".PHP_EOL;
'

login() { local T; T=$(curl -s -c "$JAR" $BASE/login | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//'); curl -s -b "$JAR" -c "$JAR" -o /dev/null -d "_token=$T" -d "email=chioma@example.com" -d "password=password" $BASE/login; }
csrf() { curl -s -b "$JAR" "$1" | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//'; }
login

T=$(csrf "$BASE/marketplace/products"); curl -s -b "$JAR" -o /dev/null -d "_token=$T" -d "qty=1" $BASE/cart/products/$DIGITAL_ID
T=$(csrf "$BASE/marketplace/products"); curl -s -b "$JAR" -o /dev/null -d "_token=$T" -d "qty=1" $BASE/cart/physical/$PHYS_ID

echo "cart page          -> $(curl -s -b "$JAR" -o /dev/null -w '%{http_code}' $BASE/cart)"
echo "checkout shows addr-> $(curl -s -b "$JAR" $BASE/cart/checkout | grep -c 'Delivery Address')"
echo "checkout shows ship-> $(curl -s -b "$JAR" $BASE/cart/checkout | grep -c 'Shipping')"

T=$(csrf "$BASE/cart/checkout")
CODE=$(curl -s -b "$JAR" -o /dev/null -w '%{http_code}' -L \
  -d "_token=$T" -d "gateway=wallet" \
  -d "ship_to_name=Chioma O" -d "ship_to_phone=08030000000" -d "ship_to_address=12 Test St" -d "ship_to_city=Lagos" -d "ship_to_state=Lagos" \
  $BASE/cart/checkout)
echo "checkout POST      -> HTTP $CODE"

# Verify + clean up
docker compose exec -T app php artisan tinker --execute='
$bl=json_decode(file_get_contents("/tmp/baseline.txt"),true);
$new=\App\Models\Order::where("id",">",$bl["max_order"])->with("items")->get();
echo "orders_created=".$new->count()." (expect 2)".PHP_EOL;
$phys=$new->firstWhere("requires_shipping",true);
$dig =$new->first(fn($o)=>!$o->requires_shipping);
echo "physical_order: ship_fee=".($phys?->shipping_fee)." status=".($phys?->status->value)." addr=".($phys?->ship_to_address).PHP_EOL;
$esc=$phys? app(\App\Services\Escrow\EscrowService::class)->forPayable($phys):null;
echo "physical_escrow: status=".($esc?->status->value)." auto_release_NULL=".($esc && $esc->auto_release_at===null?"yes":"no").PHP_EOL;
echo "digital_order: requires_shipping=".($dig?->requires_shipping?"1":"0")." status=".($dig?->status->value).PHP_EOL;
$stock=(int)\App\Models\Product::find('"$PHYS_ID"')->physicalDetail->stock_quantity;
echo "stock_delta=".($stock-$bl["webcam_stock"])." (expect -1)".PHP_EOL;
$b=\App\Models\User::where("email","chioma@example.com")->first();
$bw=app(\App\Services\Wallet\WalletService::class)->getOrCreate($b);
echo "buyer_debited=".(100000000-$bw->balance)." (expect digital+physical+2000 shipping)".PHP_EOL;

// ---- cleanup ----
foreach($new as $o){
  $e=\App\Models\Escrow::where("escrowable_type",\App\Models\Order::class)->where("escrowable_id",$o->id)->first();
  if($e){ \App\Models\EscrowTransaction::where("escrow_id",$e->id)->delete(); $e->forceDelete(); }
  \App\Models\Payment::where("payable_type",\App\Models\Order::class)->where("payable_id",$o->id)->forceDelete();
  $o->items()->delete(); $o->forceDelete();
}
\App\Models\Product::find('"$PHYS_ID"')->physicalDetail()->update(["stock_quantity"=>$bl["webcam_stock"]]);
$bw->update(["balance"=>$bl["buyer_bal"],"escrow_balance"=>0]);
// vendor wallet: zero out the escrow added by this test (pixel had 0 demo escrow baseline)
$vw=app(\App\Services\Wallet\WalletService::class)->getOrCreate(\App\Models\Vendor::where("slug","pixel-forge-studio")->first()->user);
$vw->update(["escrow_balance"=>0]);
echo "CLEANED".PHP_EOL;
'
