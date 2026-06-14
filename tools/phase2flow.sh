#!/usr/bin/env bash
# Phase 2 write-flow: create physical product (w/ variants) via the real service,
# verify detail/variants/stock + cart guard, then clean up.
cd ~/laravelProjects/volamani || exit 1
docker compose exec -T app php artisan tinker --execute='
$vendor = \App\Models\Vendor::where("status","active")->first();
$cat    = \App\Models\PhysicalCategory::whereNotNull("parent_id")->first();
$svc    = app(\App\Services\Products\ProductService::class);

$data = [
    "kind"                 => "physical",
    "name"                 => "PHASE2 TEST Sneakers",
    "description"          => str_repeat("A great test physical product for verification. ", 3),
    "price"                => 25000,
    "physical_category_id" => $cat->id,
    "secondary_categories" => [],
    "condition"            => "new",
    "brand"                => "TestBrand",
    "stock_quantity"       => 0,
    "track_inventory"      => true,
    "variant_names"        => ["Size 42","Size 43"],
    "variant_skus"         => ["S42","S43"],
    "variant_prices"       => ["", 26000],
    "variant_stocks"       => [5, 3],
];

$p = $svc->createProduct($vendor, $data);
$p = $p->fresh(["physicalDetail","variants"]);
echo "kind=".$p->kind->value.PHP_EOL;
echo "is_physical=".($p->isPhysical()?"yes":"no").PHP_EOL;
echo "physical_category_id=".$p->physical_category_id.PHP_EOL;
echo "detail_condition=".$p->physicalDetail->condition->value.PHP_EOL;
echo "variant_count=".$p->variants->count().PHP_EOL;
echo "has_variants=".($p->hasVariants()?"yes":"no").PHP_EOL;
echo "stock_total=".$p->stockQuantity().PHP_EOL;
echo "in_stock=".($p->inStock()?"yes":"no").PHP_EOL;
echo "lowest_price_kobo=".$p->lowestPrice().PHP_EOL;
echo "variant2_effective=".$p->variants[1]->effectivePrice().PHP_EOL;
echo "is_downloadable=".($p->is_downloadable?"yes":"no").PHP_EOL;

// approve it then ensure cart guard refuses it
app(\App\Services\Products\ProductService::class)->approveProduct($p, \App\Models\User::role("admin")->first());
$cart = app(\App\Services\Cart\CartService::class);
$cart->clear();
$cart->addProduct($p->id);
echo "cart_count_after_adding_physical=".$cart->count().PHP_EOL;
$cart->clear();

// digital still fine
$dig = \App\Models\Product::digital()->active()->first();
echo "digital_sample_kind=".($dig?$dig->kind->value:"none").PHP_EOL;
echo "digital_in_stock=".($dig && $dig->inStock()?"yes":"no").PHP_EOL;

// cleanup
$p->variants()->delete();
$p->physicalDetail()->delete();
$p->forceDelete();
echo "CLEANED".PHP_EOL;
'
