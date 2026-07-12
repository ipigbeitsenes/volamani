<?php

namespace Tests\Feature;

use App\Actions\Products\CreateProductAction;
use App\Models\ProductCategory;
use Database\Factories\VendorFactory;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorCurrencyPricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_usd_vendors_product_is_stored_in_base_currency(): void
    {
        $this->seed(CurrencySeeder::class);                 // USD @ ₦1,550
        $vendor = VendorFactory::new()->create(['currency' => 'USD']);
        $category = ProductCategory::create(['name' => 'Templates', 'is_active' => true]);

        $product = app(CreateProductAction::class)->execute($vendor, [
            'kind' => 'digital',
            'name' => 'USD Priced Template',
            'description' => str_repeat('detail ', 12),
            'type' => 'template',
            'category_id' => $category->id,
            'price' => 500,        // $500.00
            'compare_price' => 800, // $800.00
        ]);

        // $500 × ₦1,550 = ₦775,000 = 77,500,000 kobo (base)
        $this->assertSame(77_500_000, (int) $product->price);
        $this->assertSame(124_000_000, (int) $product->compare_price);
    }

    public function test_a_base_currency_vendor_stores_prices_directly(): void
    {
        $this->seed(CurrencySeeder::class);
        $vendor = VendorFactory::new()->create(['currency' => null]);   // null → base (NGN)
        $category = ProductCategory::create(['name' => 'Templates', 'is_active' => true]);

        $product = app(CreateProductAction::class)->execute($vendor, [
            'kind' => 'digital',
            'name' => 'Naira Priced Template',
            'description' => str_repeat('detail ', 12),
            'type' => 'template',
            'category_id' => $category->id,
            'price' => 150000,   // ₦150,000
        ]);

        $this->assertSame(15_000_000, (int) $product->price);   // ₦150,000 = 15,000,000 kobo
    }
}
