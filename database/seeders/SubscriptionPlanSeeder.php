<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'             => 'Free',
                'tagline'          => 'Get started selling on Volamani',
                'description'      => 'Everything you need to list your first products and make sales.',
                'price'            => 0,
                'billing_interval' => 'monthly',
                'commission_rate'  => null,        // platform default (10%)
                'trial_days'       => 0,
                'max_products'     => 5,
                'max_services'     => 2,
                'featured_listing' => false,
                'perks'            => ['Up to 5 products', 'Up to 2 services', 'Standard 10% commission', 'Wallet & payouts'],
                'is_active'        => true,
                'is_popular'       => false,
                'sort_order'       => 1,
            ],
            [
                'name'             => 'Starter',
                'tagline'          => 'For growing sellers',
                'description'      => 'More room to grow your catalogue with a lower commission.',
                'price'            => 250000,      // ₦2,500 / mo
                'billing_interval' => 'monthly',
                'commission_rate'  => 8.00,
                'trial_days'       => 7,
                'max_products'     => 50,
                'max_services'     => 20,
                'featured_listing' => false,
                'perks'            => ['Up to 50 products', 'Up to 20 services', 'Reduced 8% commission', '7-day free trial', 'Priority support'],
                'is_active'        => true,
                'is_popular'       => false,
                'sort_order'       => 2,
            ],
            [
                'name'             => 'Pro',
                'tagline'          => 'For serious businesses',
                'description'      => 'Unlimited listings, featured placement, and the best commission rate.',
                'price'            => 750000,      // ₦7,500 / mo
                'billing_interval' => 'monthly',
                'commission_rate'  => 5.00,
                'trial_days'       => 14,
                'max_products'     => null,        // unlimited
                'max_services'     => null,
                'featured_listing' => true,
                'perks'            => ['Unlimited products & services', 'Lowest 5% commission', 'Featured storefront placement', '14-day free trial', 'Priority support'],
                'is_active'        => true,
                'is_popular'       => true,
                'sort_order'       => 3,
            ],
            [
                'name'             => 'Business',
                'tagline'          => 'Scale with confidence',
                'description'      => 'Annual plan with our lowest commission for high-volume sellers.',
                'price'            => 7500000,     // ₦75,000 / yr
                'billing_interval' => 'yearly',
                'commission_rate'  => 3.00,
                'trial_days'       => 0,
                'max_products'     => null,
                'max_services'     => null,
                'featured_listing' => true,
                'perks'            => ['Everything in Pro', 'Lowest 3% commission', 'Billed annually (2 months free)', 'Featured placement', 'Dedicated support'],
                'is_active'        => true,
                'is_popular'       => false,
                'sort_order'       => 4,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::firstOrCreate(['slug' => \Illuminate\Support\Str::slug($plan['name'])], $plan);
        }

        $this->command->info('Subscription plans seeded.');
    }
}
