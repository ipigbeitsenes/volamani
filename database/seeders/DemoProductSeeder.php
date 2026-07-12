<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductFile;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoProductSeeder extends Seeder
{
    public function run(): void
    {
        $catIds = ProductCategory::pluck('id')->all();

        // Keyed by vendor slug → [name, type, price (kobo), short description]
        $catalog = [
            'pixel-forge-studio' => [
                ['Modern SaaS Dashboard UI Kit', 'ui_kit', 15_000_00, 'A complete Figma + HTML dashboard UI kit with 60+ components.'],
                ['Startup Pitch Deck Template', 'template', 8_000_00, '30-slide investor-ready pitch deck in PowerPoint & Keynote.'],
                ['E-commerce Landing Page Pack', 'template', 12_000_00, '10 conversion-focused, responsive landing pages.'],
            ],
            'northwind-studio' => [
                ['Laravel SaaS Starter Kit', 'software', 35_000_00, 'Auth, billing, teams and multi-tenancy boilerplate.'],
                ['WhatsApp Order Bot Script', 'software', 20_000_00, 'Automate customer orders directly through WhatsApp.'],
            ],
            'brandcraft-agency' => [
                ['Brand Identity Guidelines Template', 'template', 9_000_00, 'A fully editable brand book template.'],
                ['Logo Design Mega Bundle', 'asset', 15_000_00, '200 editable vector logos for any niche.'],
            ],
            'growthlab' => [
                ['90-Day Social Media Content Calendar', 'template', 5_000_00, 'A done-for-you content calendar with captions.'],
                ['Digital Marketing Playbook', 'ebook', 7_000_00, 'Growth tactics tailored to modern SMEs.'],
            ],
        ];

        $i = 0;
        foreach ($catalog as $vendorSlug => $products) {
            $vendor = Vendor::where('slug', $vendorSlug)->first();
            if (! $vendor) {
                continue;
            }

            foreach ($products as $idx => [$name, $type, $price, $short]) {
                $product = Product::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    [
                        'vendor_id' => $vendor->id,
                        'category_id' => $catIds ? $catIds[$i % count($catIds)] : null,
                        'name' => $name,
                        'short_description' => $short,
                        'description' => $short.' '.str_repeat('This premium digital product is delivered instantly after purchase, with lifetime updates and friendly support. ', 3),
                        'type' => $type,
                        'price' => $price,
                        'compare_price' => (int) round($price * 1.4),
                        'status' => 'active',
                        'is_featured' => $idx === 0,
                        'is_downloadable' => true,
                        'approved_at' => now(),
                    ],
                );

                $this->seedDownloadFile($product, $short);
                $i++;
            }
        }

        $this->command->info('Demo products seeded.');
    }

    /**
     * Give a downloadable product a real file on the private disk so buyers have
     * something to download. The DownloadService streams from disk('private'),
     * so the bytes must physically exist — not just a product_files row.
     */
    private function seedDownloadFile(Product $product, string $short): void
    {
        $original = $product->name.' - Download.txt';
        $path = 'product-files/'.$product->id.'/'.Str::slug($product->name).'.txt';

        $contents = "Thank you for purchasing \"{$product->name}\" on Volamani!\n\n"
            .$short."\n\n"
            ."This is a demo download file. In a live store this would be your\n"
            ."actual product (ZIP, PDF, design files, source code, etc.).\n";

        if (! Storage::disk('private')->exists($path)) {
            Storage::disk('private')->put($path, $contents);
        }

        ProductFile::firstOrCreate(
            ['product_id' => $product->id, 'path' => $path],
            [
                'label' => 'Main File',
                'original_name' => $original,
                'mime_type' => 'text/plain',
                'file_size' => strlen($contents),
                'sort_order' => 0,
            ],
        );
    }
}
