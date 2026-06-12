<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'site_name',        'value' => 'Volamani',                   'type' => 'string',  'group' => 'general', 'label' => 'Site Name'],
            ['key' => 'site_tagline',     'value' => "Africa's Digital Business Ecosystem", 'type' => 'string', 'group' => 'general', 'label' => 'Tagline'],
            ['key' => 'support_email',    'value' => 'support@volamani.com',        'type' => 'string',  'group' => 'general', 'label' => 'Support Email'],
            ['key' => 'support_phone',    'value' => '+234 000 000 0000',           'type' => 'string',  'group' => 'general', 'label' => 'Support Phone'],
            ['key' => 'maintenance_mode', 'value' => '0',                           'type' => 'boolean', 'group' => 'general', 'label' => 'Maintenance Mode'],

            // Finance
            ['key' => 'platform_commission',   'value' => '10',  'type' => 'integer', 'group' => 'finance', 'label' => 'Platform Commission (%)'],
            ['key' => 'affiliate_commission',  'value' => '5',   'type' => 'integer', 'group' => 'finance', 'label' => 'Affiliate Commission (%)'],
            ['key' => 'min_withdrawal',        'value' => '200000', 'type' => 'integer', 'group' => 'finance', 'label' => 'Minimum Withdrawal (kobo)'],
            ['key' => 'withdrawal_fee',        'value' => '5000',   'type' => 'integer', 'group' => 'finance', 'label' => 'Withdrawal Fee (kobo)'],

            // Affiliate program
            ['key' => 'affiliate_enabled',      'value' => '1',     'type' => 'boolean', 'group' => 'affiliate', 'label' => 'Affiliate Program Enabled'],
            ['key' => 'affiliate_signup_bonus', 'value' => '0',     'type' => 'integer', 'group' => 'affiliate', 'label' => 'Referral Signup Bonus (kobo)'],
            ['key' => 'affiliate_cookie_days',  'value' => '30',    'type' => 'integer', 'group' => 'affiliate', 'label' => 'Attribution Window (days)'],
            ['key' => 'affiliate_auto_approve', 'value' => '0',     'type' => 'boolean', 'group' => 'affiliate', 'label' => 'Auto-approve Commissions'],
            ['key' => 'affiliate_min_payout',   'value' => '100000','type' => 'integer', 'group' => 'affiliate', 'label' => 'Minimum Payout (kobo)'],

            // Subscriptions
            ['key' => 'subscription_grace_days', 'value' => '3', 'type' => 'integer', 'group' => 'subscription', 'label' => 'Past-due Grace Period (days)'],

            // Business matching
            ['key' => 'match_min_score',   'value' => '40', 'type' => 'integer', 'group' => 'matching', 'label' => 'Minimum Match Score (0-100)'],
            ['key' => 'match_max_results', 'value' => '20', 'type' => 'integer', 'group' => 'matching', 'label' => 'Max Matches Per Brief'],

            // Notifications
            ['key' => 'notifications_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'notifications', 'label' => 'Notifications Enabled (global)'],

            // Security
            ['key' => 'max_login_attempts', 'value' => '5',  'type' => 'integer', 'group' => 'security', 'label' => 'Max Failed Login Attempts'],
            ['key' => 'lockout_minutes',   'value' => '15', 'type' => 'integer', 'group' => 'security', 'label' => 'Account Lockout Duration (minutes)'],

            // Marketplace
            ['key' => 'min_product_price', 'value' => '10000', 'type' => 'integer', 'group' => 'marketplace', 'label' => 'Min Product Price (kobo)'],
            ['key' => 'download_expiry_hours', 'value' => '48', 'type' => 'integer', 'group' => 'marketplace', 'label' => 'Download Link Expiry (hours)'],
            ['key' => 'max_download_attempts', 'value' => '5',  'type' => 'integer', 'group' => 'marketplace', 'label' => 'Max Download Attempts'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }

        $this->command->info('Platform settings seeded.');
    }
}
