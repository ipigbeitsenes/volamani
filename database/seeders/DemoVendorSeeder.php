<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoVendorSeeder extends Seeder
{
    public function run(): void
    {
        // [owner email, business name, tagline, description, category, city, state, featured]
        $vendors = [
            ['pixel@example.com', 'Pixel Forge Studio', 'Premium design assets & templates', 'We craft pixel-perfect templates, UI kits and design assets for modern brands.', 'Design', 'Austin', 'Texas', true],
            ['naijadev@example.com', 'Northwind Studio', 'Web & software development', 'A full-stack studio building web apps, software and automation for startups.', 'Development', 'Denver', 'Colorado', true],
            ['brandcraft@example.com', 'BrandCraft Agency', 'Branding & identity design', 'Logos, brand identity and visual systems that help businesses stand out.', 'Branding', 'Portland', 'Oregon', false],
            ['growthlab@example.com', 'GrowthLab', 'Digital marketing & growth', 'Performance marketing, social media and growth strategy for SMEs.', 'Marketing', 'Chicago', 'Illinois', false],
            ['adeola@example.com', 'Summit Consulting', 'Startup & business strategy', 'Helping founders validate, launch and scale.', 'Consulting', 'Seattle', 'Washington', true],
            ['emeka@example.com', 'Meridian Advisory', 'Ecommerce & pricing advisory', 'Ecommerce setup, pricing strategy and operations consulting.', 'Consulting', 'Boston', 'Massachusetts', false],
        ];

        foreach ($vendors as [$email, $name, $tagline, $desc, $cat, $city, $state, $featured]) {
            $user = User::where('email', $email)->first();
            if (! $user) {
                continue;
            }

            Vendor::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'business_name' => $name,
                    'slug' => Str::slug($name),
                    'tagline' => $tagline,
                    'description' => $desc,
                    'category' => $cat,
                    'city' => $city,
                    'state' => $state,
                    'status' => 'active',
                    'is_featured' => $featured,
                    'approved_at' => now(),
                    'verified_at' => now(),
                ],
            );
        }

        $this->command->info('Demo vendors seeded.');
    }
}
