#!/usr/bin/env bash
# Verify: paid promotion (charge wallet + feature + expiry) and direct-to-vendor request.
cd ~/laravelProjects/volamani || exit 1
docker compose exec -e MAIL_MAILER=log -T app php artisan tinker --execute='
use Illuminate\Support\Facades\DB;
DB::beginTransaction();

// ---- Promotion ----
$vendor=\App\Models\Vendor::where("slug","pixel-forge-studio")->first();
$vu=$vendor->user;
$ws=app(\App\Services\Wallet\WalletService::class);
$w=$ws->getOrCreate($vu); $w->update(["balance"=>100000000]);
$product=\App\Models\Product::digital()->where("vendor_id",$vendor->id)->active()->first();
$fee=(int)config("payment.promotion.fee");
$bal0=$w->fresh()->balance;
$until=app(\App\Services\Products\ProductService::class)->promoteProduct($product->fresh(),$vu);
$p=$product->fresh();
echo "promoted_is_featured=".($p->is_featured?"yes":"no")." featured_until_future=".($p->featured_until->isFuture()?"yes":"no").PHP_EOL;
echo "wallet_charged=".($bal0-$w->fresh()->balance)." (expect ".$fee.")".PHP_EOL;
echo "isPromoted=".($p->isPromoted()?"yes":"no").PHP_EOL;
// expiry
$p->update(["featured_until"=>now()->subDay()]);
\Illuminate\Support\Facades\Artisan::call("products:expire-promotions");
echo "after_expiry_is_featured=".($p->fresh()->is_featured?"yes":"no")." (expect no)".PHP_EOL;

// ---- Direct-to-vendor request ----
$buyer=\App\Models\User::where("email","chioma@example.com")->first();
$svc=app(\App\Services\Requests\ProductRequestService::class);
$req=$svc->createRequest($buyer,["title"=>"Custom dashboard build","description"=>str_repeat("I need a custom admin dashboard built for my startup. ",3),"vendor_id"=>$vendor->id]);
echo "direct_vendor_id=".$req->vendor_id." is_public=".($req->is_public?"1":"0")." isDirect=".($req->isDirect()?"yes":"no").PHP_EOL;
$onBoard=app(\App\Repositories\Requests\ProductRequestRepository::class)->openRequests();
$onBoardHas=$onBoard->getCollection()->contains("id",$req->id);
echo "appears_on_open_board=".($onBoardHas?"yes":"no")." (expect no)".PHP_EOL;
$vendorSees=\App\Models\ProductRequest::forVendor($vendor->id)->where("id",$req->id)->exists();
echo "target_vendor_sees=".($vendorSees?"yes":"no")." (expect yes)".PHP_EOL;

DB::rollBack();
echo "ROLLED_BACK".PHP_EOL;
'
