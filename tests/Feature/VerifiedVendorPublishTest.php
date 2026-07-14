<?php

namespace Tests\Feature;

use App\Actions\Products\CreateProductAction;
use App\Actions\Products\UpdateProductAction;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Vendor;
use Database\Factories\VendorFactory;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifiedVendorPublishTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CurrencySeeder::class);
    }

    private function makeProduct(Vendor $vendor): Product
    {
        $category = ProductCategory::create(['name' => 'Templates '.uniqid(), 'is_active' => true]);

        return app(CreateProductAction::class)->execute($vendor, [
            'kind' => 'digital',
            'name' => 'Test Product',
            'description' => str_repeat('detail ', 12),
            'type' => 'template',
            'category_id' => $category->id,
            'price' => 100_000,
        ]);
    }

    public function test_verified_vendor_product_is_published_immediately(): void
    {
        $vendor = VendorFactory::new()->create(['verified_at' => now()]);

        $this->assertSame(ProductStatus::Active, $this->makeProduct($vendor)->status);
    }

    public function test_unverified_vendor_product_awaits_review(): void
    {
        $vendor = VendorFactory::new()->create(['verified_at' => null]);

        $this->assertSame(ProductStatus::Pending, $this->makeProduct($vendor)->status);
    }

    public function test_verified_vendor_edit_keeps_the_listing_live(): void
    {
        $vendor = VendorFactory::new()->create(['verified_at' => now()]);
        $product = $this->makeProduct($vendor);   // Active

        app(UpdateProductAction::class)->execute($product, ['price' => 250_000, 'name' => 'Renamed']);

        $this->assertSame(ProductStatus::Active, $product->fresh()->status);
    }

    public function test_unverified_vendor_material_edit_returns_to_review(): void
    {
        $vendor = VendorFactory::new()->create(['verified_at' => null]);
        $product = $this->makeProduct($vendor);           // Pending
        $product->update(['status' => ProductStatus::Active]);   // simulate admin approval

        app(UpdateProductAction::class)->execute($product, ['price' => 250_000]);

        $this->assertSame(ProductStatus::Pending, $product->fresh()->status);
    }
}
