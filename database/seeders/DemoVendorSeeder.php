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
            ['pixel@example.com', 'Pixel Forge Studio', 'Premium design assets & templates', 'We craft pixel-perfect templates, UI kits and design assets for modern African brands.', 'Design', 'Lagos', 'Lagos', true],
            ['naijadev@example.com', 'NaijaDev Studio', 'Web & software development', 'A full-stack studio building web apps, software and automation for African startups.', 'Development', 'Abuja', 'FCT', true],
            ['brandcraft@example.com', 'BrandCraft Agency', 'Branding & identity design', 'Logos, brand identity and visual systems that help businesses stand out.', 'Branding', 'Lagos', 'Lagos', false],
            ['growthlab@example.com', 'GrowthLab', 'Digital marketing & growth', 'Performance marketing, social media and growth strategy for SMEs.', 'Marketing', 'Port Harcourt', 'Rivers', false],
            ['adeola@example.com', 'Adeola Consulting', 'Startup & business strategy', 'Helping founders validate, launch and scale across Africa.', 'Consulting', 'Lagos', 'Lagos', true],
            ['emeka@example.com', 'Emeka Advisory', 'Ecommerce & pricing advisory', 'Ecommerce setup, pricing strategy and operations consulting.', 'Consulting', 'Enugu', 'Enugu', false],
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
                    'slug'          => Str::slug($name),
                    'tagline'       => $tagline,
                    'description'   => $desc,
                    'category'      => $cat,
                    'city'          => $city,
                    'state'         => $state,
                    'status'        => 'active',
                    'is_featured'   => $featured,
                    'approved_at'   => now(),
                    'verified_at'   => now(),
                ],
            );
        }

        $this->command->info('Demo vendors seeded.');
    }
}
