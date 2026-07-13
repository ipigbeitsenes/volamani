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
            ['key' => 'site_tagline',     'value' => 'Your Digital Business Ecosystem', 'type' => 'string', 'group' => 'general', 'label' => 'Tagline'],
            ['key' => 'support_email',    'value' => 'support@volamani.com',        'type' => 'string',  'group' => 'general', 'label' => 'Support Email'],
            ['key' => 'support_phone',    'value' => '+1 000 000 0000',             'type' => 'string',  'group' => 'general', 'label' => 'Support Phone'],
            ['key' => 'currency_symbol',  'value' => '₦',                           'type' => 'string',  'group' => 'general', 'label' => 'Currency Symbol'],
            ['key' => 'currency_code',    'value' => 'NGN',                         'type' => 'string',  'group' => 'general', 'label' => 'Currency Code'],
            ['key' => 'social_facebook',  'value' => '',                            'type' => 'string',  'group' => 'general', 'label' => 'Facebook URL'],
            ['key' => 'social_instagram', 'value' => '',                            'type' => 'string',  'group' => 'general', 'label' => 'Instagram URL'],
            ['key' => 'social_twitter',   'value' => '',                            'type' => 'string',  'group' => 'general', 'label' => 'X / Twitter URL'],
            ['key' => 'maintenance_mode', 'value' => '0',                           'type' => 'boolean', 'group' => 'general', 'label' => 'Maintenance Mode'],
            ['key' => 'registration_enabled', 'value' => '1',                       'type' => 'boolean', 'group' => 'general', 'label' => 'New Registrations Enabled'],

            // Branding
            ['key' => 'site_logo',    'value' => '', 'type' => 'string', 'group' => 'branding', 'label' => 'Site Logo'],
            ['key' => 'site_favicon', 'value' => '', 'type' => 'string', 'group' => 'branding', 'label' => 'Favicon'],

            // Storage
            ['key' => 'storage_driver', 'value' => 'local', 'type' => 'string',  'group' => 'storage', 'label' => 'Storage Driver'],
            ['key' => 's3_key',         'value' => '',      'type' => 'string',  'group' => 'storage', 'label' => 'AWS Access Key ID'],
            ['key' => 's3_secret',      'value' => '',      'type' => 'string',  'group' => 'storage', 'label' => 'AWS Secret Access Key'],
            ['key' => 's3_region',      'value' => 'us-east-1', 'type' => 'string', 'group' => 'storage', 'label' => 'AWS Region'],
            ['key' => 's3_bucket',      'value' => '',      'type' => 'string',  'group' => 'storage', 'label' => 'S3 Bucket'],
            ['key' => 's3_url',         'value' => '',      'type' => 'string',  'group' => 'storage', 'label' => 'S3 Public URL (CDN, optional)'],
            ['key' => 's3_endpoint',    'value' => '',      'type' => 'string',  'group' => 'storage', 'label' => 'S3 Endpoint (optional, for S3-compatible)'],
            ['key' => 's3_path_style',  'value' => '0',     'type' => 'boolean', 'group' => 'storage', 'label' => 'Use Path-Style Endpoint'],

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
            ['key' => 'affiliate_min_payout',   'value' => '100000', 'type' => 'integer', 'group' => 'affiliate', 'label' => 'Minimum Payout (kobo)'],

            // Pre-orders (coming-soon products bought with a deposit)
            ['key' => 'preorder_deposit_percent', 'value' => '50', 'type' => 'integer', 'group' => 'marketplace', 'label' => 'Pre-order Deposit (% of price)'],

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

            // Live chat widget
            ['key' => 'chat_enabled',         'value' => '1', 'type' => 'boolean', 'group' => 'chat', 'label' => 'Live Chat Enabled'],
            ['key' => 'chat_greeting',        'value' => "👋 Hi there! Need any help? We're here for you.", 'type' => 'string', 'group' => 'chat', 'label' => 'Greeting Bubble'],
            ['key' => 'chat_welcome',         'value' => 'Hello! Send us a message and our team will reply right here.', 'type' => 'string', 'group' => 'chat', 'label' => 'Welcome Message'],
            ['key' => 'chat_support_email',   'value' => 'support@volamani.com', 'type' => 'string', 'group' => 'chat', 'label' => 'Fallback Support Email'],
            ['key' => 'chat_bot_delay',       'value' => '60', 'type' => 'integer', 'group' => 'chat', 'label' => 'Offline Bot Delay (seconds)'],
            ['key' => 'chat_offline_message', 'value' => "Thanks for reaching out! 🙏 All our chat agents are busy at the moment. Please email us at :email and we'll get back to you as soon as we can.", 'type' => 'string', 'group' => 'chat', 'label' => 'Offline Bot Message'],
            ['key' => 'chat_team_name',       'value' => 'Volamani Support', 'type' => 'string', 'group' => 'chat', 'label' => 'Chat Team Name'],

            // Buyer protection — chargeback reserve, dispute SLAs, strikes + policy page copy
            ['key' => 'chargeback_reserve_percent',   'value' => '0',  'type' => 'integer', 'group' => 'protection', 'label' => 'Chargeback Reserve (% of each payout, 0 = off)'],
            ['key' => 'chargeback_reserve_days',      'value' => '30', 'type' => 'integer', 'group' => 'protection', 'label' => 'Reserve Hold Period (days)'],
            ['key' => 'dispute_response_hours',       'value' => '48', 'type' => 'integer', 'group' => 'protection', 'label' => 'Dispute Response Window (hours)'],
            ['key' => 'dispute_admin_sla_hours',      'value' => '72', 'type' => 'integer', 'group' => 'protection', 'label' => 'Staff SLA Before Auto-escalate (hours)'],
            ['key' => 'dispute_auto_refund_on_breach', 'value' => '0',  'type' => 'boolean', 'group' => 'protection', 'label' => 'Auto-refund Buyer When Seller Misses SLA'],
            ['key' => 'strike_suspend_threshold',     'value' => '3',  'type' => 'integer', 'group' => 'protection', 'label' => 'Strikes Before Auto-suspend'],
            ['key' => 'protection_support_email',     'value' => 'support@volamani.com', 'type' => 'string', 'group' => 'protection', 'label' => 'Protection Contact Email'],
            ['key' => 'protection_intro',             'value' => 'Every purchase on Volamani is protected. Buy from verified sellers, pay the way that suits you, and our team steps in whenever something goes wrong.', 'type' => 'text', 'group' => 'protection', 'label' => 'Policy: Intro'],
            ['key' => 'protection_escrow_summary',    'value' => 'When you pay, your money is held in escrow — not released to the seller — until the order is delivered and the protection window passes. If you never receive your order, you get a full refund to your wallet.', 'type' => 'text', 'group' => 'protection', 'label' => 'Policy: Escrow'],
            ['key' => 'protection_return_summary',    'value' => 'Physical items can be returned within the return window if they arrive damaged, wrong, or not as described. Once the seller confirms the returned item, you are refunded in full.', 'type' => 'text', 'group' => 'protection', 'label' => 'Policy: Returns'],
            ['key' => 'protection_dispute_process',   'value' => 'If an order goes wrong, open a support ticket or dispute from the order page. The seller has a fixed window to respond; if they do not, our team escalates and resolves it for you with a fair outcome.', 'type' => 'text', 'group' => 'protection', 'label' => 'Policy: Disputes'],
            ['key' => 'protection_chargeback_note',   'value' => 'If you paid by card and something is seriously wrong, your bank can also raise a chargeback. We honour valid chargebacks and recover the funds from the seller, so you are never left out of pocket.', 'type' => 'text', 'group' => 'protection', 'label' => 'Policy: Chargebacks'],
        ];

        // Feature toggles — one boolean per entry in config/features.php (all ON).
        foreach (config('features', []) as $key => $meta) {
            $settings[] = [
                'key' => 'feature_'.$key,
                'value' => '1',
                'type' => 'boolean',
                'group' => 'features',
                'label' => $meta[0] ?? ucfirst($key),
            ];
        }

        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }

        $this->command->info('Platform settings seeded.');
    }
}
