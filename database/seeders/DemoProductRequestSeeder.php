<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Models\ProductRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoProductRequestSeeder extends Seeder
{
    public function run(): void
    {
        $catId = ProductCategory::value('id');
        $buyers = User::whereIn('email', ['chioma@example.com', 'tunde@example.com', 'amaka@example.com'])
            ->pluck('id', 'email');

        // [buyer email, title, description, budget_min, budget_max (kobo), location]
        $requests = [
            ['chioma@example.com', 'Need a logo for my fashion brand', 'Looking for a modern, minimal logo and brand colours for a women’s fashion label.', 5_000_00, 15_000_00, 'Austin'],
            ['tunde@example.com', 'Build a simple e-commerce website', 'I need a small online store with online checkout and product management.', 30_000_00, 80_000_00, 'Denver'],
            ['amaka@example.com', 'Social media manager for one month', 'Need someone to handle Instagram and TikTok content for my skincare business.', 10_000_00, 25_000_00, 'Remote'],
        ];

        foreach ($requests as [$email, $title, $desc, $min, $max, $location]) {
            $buyerId = $buyers[$email] ?? null;
            if (! $buyerId) {
                continue;
            }

            ProductRequest::firstOrCreate(
                ['title' => $title, 'buyer_id' => $buyerId],
                [
                    'category_id' => $catId,
                    'description' => $desc,
                    'budget_min' => $min,
                    'budget_max' => $max,
                    'deadline_at' => now()->addDays(14),
                    'status' => 'open',
                    'is_public' => true,
                    'location' => $location,
                ],
            );
        }

        $this->command->info('Demo product requests seeded.');
    }
}
