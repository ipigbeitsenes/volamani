<?php

namespace Database\Seeders;

use App\Models\ConsultantAvailability;
use App\Models\ConsultantProfile;
use App\Models\ConsultationPackage;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoConsultantSeeder extends Seeder
{
    public function run(): void
    {
        // vendor slug → [display name, niche, bio, expertise[], years]
        $consultants = [
            'adeola-consulting' => ['Adeola Adewale', 'Startup Strategy', 'I help founders validate ideas, build MVPs and raise funding.', ['Startup Strategy', 'Fundraising', 'Product'], 8],
            'emeka-advisory'    => ['Emeka Obi', 'Ecommerce & Pricing', 'Ecommerce setup, pricing strategy and operations for growing brands.', ['Ecommerce', 'Pricing', 'Operations'], 6],
        ];

        foreach ($consultants as $vendorSlug => [$display, $niche, $bio, $expertise, $years]) {
            $vendor = Vendor::where('slug', $vendorSlug)->first();
            if (! $vendor) {
                continue;
            }

            $profile = ConsultantProfile::firstOrCreate(
                ['vendor_id' => $vendor->id],
                [
                    'slug'             => Str::slug($display),
                    'display_name'     => $display,
                    'bio'              => $bio,
                    'niche'            => $niche,
                    'expertise'        => $expertise,
                    'experience_years' => $years,
                    'is_available'     => true,
                ],
            );

            $packages = [
                ['30-min Discovery Call', 'A focused 30-minute session to unpack your challenge.', 30, 10_000_00, 1],
                ['60-min Strategy Session', 'A deep-dive strategy session with a written action plan.', 60, 25_000_00, 2],
            ];

            foreach ($packages as [$name, $desc, $minutes, $price, $order]) {
                ConsultationPackage::firstOrCreate(
                    ['profile_id' => $profile->id, 'name' => $name],
                    [
                        'description'      => $desc,
                        'type'             => 'one_time',
                        'duration_minutes' => $minutes,
                        'price'            => $price,
                        'is_active'        => true,
                        'sort_order'       => $order,
                    ],
                );
            }

            // Weekday availability, 09:00–17:00 (day_of_week 1=Mon … 5=Fri)
            for ($day = 1; $day <= 5; $day++) {
                ConsultantAvailability::firstOrCreate(
                    ['profile_id' => $profile->id, 'day_of_week' => $day],
                    ['start_time' => '09:00', 'end_time' => '17:00', 'is_active' => true],
                );
            }
        }

        $this->command->info('Demo consultants seeded.');
    }
}
