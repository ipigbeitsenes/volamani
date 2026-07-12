<?php

namespace Database\Seeders;

use App\Models\PhysicalCategory;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoPhysicalProductSeeder extends Seeder
{
    public function run(): void
    {
        // Resolve a physical category id by (case-insensitive) name.
        $cat = fn (string $name) => PhysicalCategory::where('name', $name)->value('id');

        // [vendorSlug => [ [name, categoryName, price(kobo), short, condition, brand, stock, weight_g, variants[]], ... ]]
        $catalog = [
            'pixel-forge-studio' => [
                ['Mechanical Keyboard — Creator Edition', 'Computer Accessories', 45_000_00, 'Hot-swappable 75% mechanical keyboard with PBT keycaps.', 'new', 'ForgeGear', 25, 1100, [
                    ['Black', 0, 12],
                    ['White', 2_000_00, 13],
                ]],
                ['4K Webcam Pro', 'Cameras & Photography', 38_000_00, 'Ultra HD webcam with auto-focus and dual mics.', 'new', 'ForgeGear', 40, 250, []],
            ],
            'brandcraft-agency' => [
                ['Premium Cotton Brand Tee', "Men's Fashion", 12_000_00, 'Soft 100% cotton tee with minimalist branding.', 'new', 'BrandCraft', 0, 200, [
                    ['Small', 0, 0],
                    ['Medium', 0, 0],
                    ['Large', 0, 0],
                ]],
                ['Leather Laptop Sleeve 15"', 'Bags & Luggage', 18_000_00, 'Full-grain leather sleeve fits 15-inch laptops.', 'new', 'BrandCraft', 15, 600, []],
            ],
            'growthlab' => [
                ['Wireless Noise-Cancelling Headphones', 'Audio & Headphones', 65_000_00, 'Over-ear ANC headphones, 30-hour battery.', 'new', 'GrowthAudio', 12, 320, []],
            ],
        ];

        foreach ($catalog as $vendorSlug => $products) {
            $vendor = Vendor::where('slug', $vendorSlug)->first();
            if (! $vendor) {
                continue;
            }

            // These demo stores now sell physical too, with a flat shipping fee.
            $vendor->update([
                'store_focus' => 'hybrid',
                'shipping_fee' => 2_000_00,   // 2,000 flat
                'free_shipping_threshold' => 50_000_00,  // free over 50,000
                'ships_to' => 'Nationwide · 2–5 business days',
            ]);

            foreach ($products as $idx => [$name, $catName, $price, $short, $condition, $brand, $stock, $weight, $variants]) {
                $product = Product::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    [
                        'vendor_id' => $vendor->id,
                        'kind' => 'physical',
                        'physical_category_id' => $cat($catName),
                        'name' => $name,
                        'short_description' => $short,
                        'description' => $short.' '.str_repeat('Carefully sourced and quality-checked before shipping, with a hassle-free returns window. ', 3),
                        'type' => 'other',
                        'price' => $price,
                        'compare_price' => (int) round($price * 1.3),
                        'status' => 'active',
                        'is_featured' => $idx === 0,
                        'is_downloadable' => false,
                        'approved_at' => now(),
                    ],
                );

                $product->physicalDetail()->firstOrCreate(
                    ['product_id' => $product->id],
                    [
                        'stock_quantity' => $variants ? 0 : $stock,
                        'track_inventory' => true,
                        'allow_backorder' => false,
                        'condition' => $condition,
                        'brand' => $brand,
                        'weight_grams' => $weight,
                    ],
                );

                foreach ($variants as $vIdx => [$vName, $override, $vStock]) {
                    $product->variants()->firstOrCreate(
                        ['product_id' => $product->id, 'name' => $vName],
                        [
                            'price_override' => $override ?: null,
                            'stock_quantity' => $vStock,
                            'is_active' => true,
                            'sort_order' => $vIdx,
                        ],
                    );
                }
            }
        }

        $this->command->info('Demo physical products seeded.');
    }
}
