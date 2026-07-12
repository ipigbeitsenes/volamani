<?php

namespace Database\Seeders;

use App\Models\FreelanceService;
use App\Models\ServicePackage;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoServiceSeeder extends Seeder
{
    public function run(): void
    {
        // vendor slug → [title, short description]
        $services = [
            'northwind-studio' => ['I will build a custom Laravel web application', 'Custom web apps built to your spec with clean, maintainable code.'],
            'pixel-forge-studio' => ['I will design a modern website UI in Figma', 'Beautiful, conversion-focused UI designs delivered in Figma.'],
            'brandcraft-agency' => ['I will design a professional logo and brand identity', 'A memorable logo plus a complete brand identity kit.'],
            'growthlab' => ['I will manage your social media for one month', 'Full social media management: content, scheduling and growth.'],
        ];

        // tier value → [package name, price (kobo), delivery days, revisions, features]
        $tiers = [
            'basic' => ['Basic', 15_000_00, 7, 1, ['1 concept', 'Source files']],
            'standard' => ['Standard', 30_000_00, 5, 3, ['3 concepts', 'Source files', 'Priority support']],
            'premium' => ['Premium', 60_000_00, 3, 10, ['Unlimited concepts', 'Source files', 'Priority support', 'Commercial license']],
        ];

        foreach ($services as $vendorSlug => [$title, $short]) {
            $vendor = Vendor::where('slug', $vendorSlug)->first();
            if (! $vendor) {
                continue;
            }

            $service = FreelanceService::firstOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'vendor_id' => $vendor->id,
                    'category_id' => null,
                    'title' => $title,
                    'short_description' => $short,
                    'description' => $short.' '.str_repeat('Delivered with professionalism, clear communication and on-time delivery. ', 3),
                    'status' => 'active',
                    'is_featured' => true,
                    'approved_at' => now(),
                ],
            );

            foreach ($tiers as $tier => [$name, $price, $days, $revisions, $features]) {
                ServicePackage::firstOrCreate(
                    ['service_id' => $service->id, 'tier' => $tier],
                    [
                        'name' => $name,
                        'description' => "{$name} package — everything you need at the {$name} level.",
                        'price' => $price,
                        'delivery_days' => $days,
                        'revisions' => $revisions,
                        'features' => $features,
                        'is_active' => true,
                    ],
                );
            }
        }

        $this->command->info('Demo services & packages seeded.');
    }
}
