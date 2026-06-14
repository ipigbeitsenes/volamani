#!/usr/bin/env bash
cd ~/laravelProjects/volamani || exit 1

echo "--- home page plans ---"
curl -s http://localhost:8000/ -o /tmp/home.html
echo "home code        -> $(curl -s -o /dev/null -w '%{http_code}' http://localhost:8000/)"
echo "plans section    -> $(grep -c 'Plans that grow' /tmp/home.html)"
echo "plan CTA buttons -> $(grep -c 'Get started\|Choose ' /tmp/home.html)"

echo "--- referral commission flow (rolled back) ---"
docker compose exec -e MAIL_MAILER=log -T app php artisan tinker --execute='
use Illuminate\Support\Facades\DB;
DB::beginTransaction();
$ws=app(\App\Services\Wallet\WalletService::class);

// referrers A (buyer-side) and A2 (vendor-side)
$A =\App\Models\User::create(["name"=>"Ref A","email"=>"refa_".uniqid()."@t.co","password"=>"x"]);
$A2=\App\Models\User::create(["name"=>"Ref A2","email"=>"refa2_".uniqid()."@t.co","password"=>"x"]);
// referred buyer B
$B =\App\Models\User::create(["name"=>"Buyer B","email"=>"buyb_".uniqid()."@t.co","password"=>"x","referred_by"=>$A->id]);
// referred vendor: point an existing vendors user to A2
$vendor=\App\Models\Vendor::where("slug","pixel-forge-studio")->first();
$vendor->user->update(["referred_by"=>$A2->id]);

$bal=fn($u)=>(int) (\App\Models\Wallet::where("user_id",$u->id)->value("balance") ?? 0);
$balA0=$bal($A); $balA20=$bal($A2);

$order=\App\Models\Order::create(["buyer_id"=>$B->id,"vendor_id"=>$vendor->id,"status"=>"paid","payment_status"=>"success","total_amount"=>1000000,"platform_fee"=>100000,"vendor_earnings"=>900000,"currency"=>"NGN"]);
$pay=\App\Models\Payment::create(["user_id"=>$B->id,"payable_type"=>\App\Models\Order::class,"payable_id"=>$order->id,"gateway"=>"wallet","gateway_reference"=>"T".uniqid(),"status"=>"success","currency"=>"NGN","amount"=>1000000,"paid_at"=>now()]);

$rate=(float) settings("affiliate_commission",5);
$res=app(\App\Services\Affiliate\AffiliateService::class)->recordConversion($pay);
echo "commissions_created=".count($res)." (expect 2: buyer + vendor referrer)".PHP_EOL;
echo "rate=".$rate."% of platform_fee 100000 => expect ".((int)round(100000*$rate/100))." each".PHP_EOL;
echo "A_credited=".($bal($A)-$balA0)." (buyer referrer)".PHP_EOL;
echo "A2_credited=".($bal($A2)-$balA20)." (vendor referrer)".PHP_EOL;
$c=$res[0]??null; echo "commission_status=".($c?$c->status->value:"none").PHP_EOL;
// idempotency: re-run should create 0
$res2=app(\App\Services\Affiliate\AffiliateService::class)->recordConversion($pay->fresh());
echo "rerun_created=".count($res2)." (expect 0 — idempotent)".PHP_EOL;

DB::rollBack();
echo "ROLLED_BACK".PHP_EOL;
'
