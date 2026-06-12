<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // System / configuration data (safe for every environment).
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            SettingsSeeder::class,
            ProductCategorySeeder::class,
            PricingTemplateSeeder::class,
            SubscriptionPlanSeeder::class,
        ]);

        // Demo / sample content — skip in production so live data isn't polluted.
        if (! app()->environment('production')) {
            $this->call([
                DemoUserSeeder::class,
                DemoVendorSeeder::class,
                DemoProductSeeder::class,
                DemoServiceSeeder::class,
                DemoConsultantSeeder::class,
                DemoProductRequestSeeder::class,
            ]);
        }
    }
}
