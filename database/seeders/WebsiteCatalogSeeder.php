<?php

namespace Database\Seeders;

use App\Enums\ProductKind;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Seeds the official Volamani first-party catalog of website & system builds as
 * "coming soon" pre-order products (buyers reserve with a deposit and pay the
 * balance on delivery). Prices are in Naira. Branded SVG covers are generated at
 * run time — no image assets to ship. Idempotent (firstOrCreate).
 */
class WebsiteCatalogSeeder extends Seeder
{
    /** group => [category label, gradient c1, c2, cover layout, product type] */
    private const GROUPS = [
        'business' => ['Business & Professional', '#2563eb', '#1e3a8a', 'site', 'template'],
        'realestate' => ['Real Estate & Property', '#0d9488', '#134e4a', 'site', 'template'],
        'hospitality' => ['Hospitality & Travel', '#e11d48', '#881337', 'site', 'template'],
        'healthcare' => ['Health & Wellness', '#0891b2', '#155e75', 'site', 'template'],
        'education' => ['Education & Learning', '#7c3aed', '#4c1d95', 'site', 'template'],
        'community' => ['Community & Non-profit', '#16a34a', '#14532d', 'site', 'template'],
        'portfolio' => ['Portfolio & Creative', '#db2777', '#831843', 'site', 'template'],
        'media' => ['Media, News & Streaming', '#ea580c', '#7c2d12', 'grid', 'template'],
        'ecommerce' => ['Online Stores', '#059669', '#065f46', 'grid', 'template'],
        'marketplace' => ['Marketplaces & Platforms', '#4f46e5', '#312e81', 'grid', 'software'],
        'fintech' => ['Finance & Fintech', '#0369a1', '#0c4a6e', 'dashboard', 'software'],
        'saas' => ['SaaS & Software', '#6d28d9', '#4c1d95', 'dashboard', 'software'],
        'systems' => ['Management Systems', '#475569', '#1e293b', 'dashboard', 'software'],
        'government' => ['Government & Enterprise', '#1d4ed8', '#172554', 'site', 'software'],
        'industrial' => ['Industry & Agriculture', '#b45309', '#78350f', 'site', 'template'],
    ];

    /** group => [ [name, price in Naira], ... ] */
    private const CATALOG = [
        'business' => [
            ['Corporate Business Website', 150000], ['Construction Company', 180000],
            ['Law Firm Website', 200000], ['Accounting Firm', 180000],
            ['Consulting Agency', 180000], ['Digital Marketing Agency', 250000],
            ['Printing Company', 200000], ['Immigration Consultancy', 500000],
            ['Software Company', 300000], ['IT Company', 300000], ['Hosting Company', 500000],
        ],
        'realestate' => [
            ['Real Estate Agency', 350000],
        ],
        'hospitality' => [
            ['Hotel Booking', 450000], ['Restaurant Ordering', 250000], ['Wedding Website', 120000],
            ['Event Planner', 180000], ['Conference Website', 180000], ['Car Rental', 500000],
            ['Flight Booking', 2500000], ['Bus Booking', 1500000], ['Travel Agency System', 2500000],
            ['Visa Processing Website', 700000],
        ],
        'healthcare' => [
            ['Hospital Website', 300000], ['Pharmacy Website', 250000], ['Beauty Salon', 200000],
            ['Spa Website', 200000], ['Gym Website', 250000], ['Dentist Website', 250000],
            ['Veterinary Clinic', 250000], ['Medical Laboratory', 300000],
        ],
        'education' => [
            ['School Website', 250000], ['University Portal', 2500000], ['Driving School', 250000],
            ['Online Course Platform', 2000000],
        ],
        'community' => [
            ['Church Website', 200000], ['NGO Website', 180000], ['Donation Platform', 700000],
            ['Political Campaign Website', 300000], ['Social Network', 6000000], ['Dating Website', 4000000],
            ['Forum Website', 1500000], ['Community Platform', 2000000], ['Membership Website', 2000000],
        ],
        'portfolio' => [
            ['Portfolio Website', 120000], ['Photography Website', 150000], ['Music Artist Website', 180000],
            ['Podcast Website', 180000], ['Architect Portfolio', 180000], ['Interior Design Website', 250000],
        ],
        'media' => [
            ['News Website', 500000], ['Magazine Website', 700000], ['Sports News Website', 600000],
            ['Entertainment Blog', 450000], ['Finance Blog', 600000], ['Crypto Blog', 700000],
            ['Video Streaming Platform', 5000000], ['Music Streaming Platform', 5000000],
            ['Podcast Platform', 2500000], ['Sports Prediction Website', 1500000],
            ['Lottery Results Website', 800000], ['Creator Platform', 5000000],
            ['Publishing Platform', 6000000],
        ],
        'ecommerce' => [
            ['E-commerce Store', 800000], ['Digital Products Store', 700000], ['Fashion Store', 500000],
            ['Electronics Store', 600000], ['Supermarket Store', 600000], ['Book Store', 500000],
            ['Bakery Website', 200000], ['Cake Shop', 180000], ['Wholesale Store', 500000],
            ['Jewelry Store', 400000], ['Cosmetics Store', 350000], ['Auto Parts Store', 500000],
        ],
        'marketplace' => [
            ['Affiliate Website', 500000], ['Coupon Website', 600000], ['Price Comparison Website', 1200000],
            ['Job Board', 1200000], ['Freelancer Marketplace', 3000000], ['Service Marketplace', 2500000],
            ['Classified Ads Website', 1500000], ['Property Marketplace', 2500000], ['Car Marketplace', 2500000],
            ['Agriculture Marketplace', 2000000], ['Multi-vendor Marketplace', 4000000], ['Rental Marketplace', 2000000],
            ['Equipment Rental', 700000], ['Auction Website', 3000000], ['Digital Marketplace', 3000000],
            ['Food Delivery Platform', 4000000], ['Ride Hailing Platform', 6000000],
            ['Marketplace for Services', 4000000], ['Healthcare Marketplace', 4000000],
            ['Tutor Marketplace', 3000000], ['Doctor Booking Platform', 4000000], ['Lawyer Marketplace', 3000000],
            ['Procurement Marketplace', 5000000],
        ],
        'fintech' => [
            ['Insurance Company', 500000], ['Microfinance Website', 700000], ['Loan Management System', 1500000],
            ['Cooperative Society System', 1200000], ['POS Agent Website', 350000], ['Fintech Landing Page', 300000],
            ['Investment Company', 500000], ['Crowdfunding Platform', 3000000], ['Escrow Platform', 4000000],
            ['Forex Signal Website', 700000], ['Expense Tracker', 1200000], ['Savings Platform', 2000000],
            ['Digital Banking UI', 1500000], ['NeoBank Platform', 8000000], ['Payment Gateway Dashboard', 5000000],
        ],
        'saas' => [
            ['SaaS Landing Page', 250000], ['AI Startup Website', 350000], ['CRM Software', 2500000],
            ['Invoice Software', 1200000], ['Accounting Software', 3000000], ['Help Desk Software', 2000000],
            ['Appointment Booking', 700000], ['Cloud File Storage', 2500000], ['Subscription Platform', 2500000],
            ['QR Code Generator', 700000], ['URL Shortener', 1000000], ['Bio Link Website', 500000],
            ['AI Content Generator', 5000000], ['AI Chatbot SaaS', 7000000], ['Email Marketing Platform', 5000000],
            ['SMS Marketing Platform', 5000000], ['WhatsApp Marketing Platform', 5000000],
            ['Website Builder SaaS', 10000000], ['No-Code Builder', 10000000], ['Business Management SaaS', 7000000],
            ['Customer Support Portal', 2000000], ['Freelancer SaaS', 4000000], ['CRM SaaS', 5000000],
            ['Accounting SaaS', 6000000], ['HR SaaS', 5000000], ['POS SaaS', 5000000], ['Inventory SaaS', 5000000],
            ['Invoice SaaS', 4000000], ['Booking SaaS', 4000000], ['Survey Platform', 2000000],
            ['Forms Builder', 2500000], ['Project Management SaaS', 5000000], ['Task Management SaaS', 4000000],
            ['Enterprise Multi-Tenant SaaS Platform', 15000000],
        ],
        'systems' => [
            ['School Management System', 1500000], ['Hospital Management System', 2000000], ['Clinic Management', 1500000],
            ['Learning Management System', 2500000], ['CBT Exam System', 2000000], ['Library Management', 1500000],
            ['Attendance Management', 1200000], ['Student Result Portal', 1500000], ['Hostel Management', 1500000],
            ['HR Management System', 2000000], ['Payroll System', 2000000], ['Inventory Management', 2000000],
            ['POS Software', 2000000], ['Warehouse Management', 2500000], ['Queue Management', 1500000],
            ['Visitor Management', 1500000], ['Document Management', 2000000], ['ERP System', 8000000],
            ['Courier Management System', 2500000], ['Fleet Management System', 3000000], ['Hotel Management System', 3000000],
            ['Restaurant POS System', 2500000], ['Clinic Appointment System', 1500000], ['Medical Records System', 2500000],
            ['Legal Case Management', 2500000], ['Recruitment Portal', 2000000], ['Employee Self-Service Portal', 2000000],
            ['Vendor Management System', 2500000], ['Procurement System', 3000000], ['Asset Management System', 2500000],
            ['Maintenance Management System', 3000000], ['Hotel Channel Manager', 4000000], ['Property Management System', 3000000],
            ['Estate Management System', 3500000], ['Rental Management System', 2500000], ['Dispatch Management System', 3000000],
            ['CRM + ERP Suite', 8000000], ['Education ERP', 7000000], ['Hospital ERP', 8000000],
            ['Corporate Intranet', 3000000],
        ],
        'government' => [
            ['Government Portal', 6000000], ['Local Government Portal', 5000000], ['State Government Portal', 8000000],
            ['Tender Management System', 4000000],
        ],
        'industrial' => [
            ['Manufacturing Company', 400000], ['Oil & Gas Website', 500000], ['Solar Company', 400000],
            ['Telecommunication Company', 500000], ['Agriculture Company', 350000], ['Poultry Farm', 250000],
            ['Fish Farm', 250000], ['Courier Website', 500000], ['Logistics Company', 500000],
            ['Laundry Website', 200000],
        ],
    ];

    public function run(): void
    {
        $vendor = $this->officialVendor();
        $parent = ProductCategory::firstOrCreate(
            ['slug' => 'websites-systems'],
            ['name' => 'Websites & Systems', 'icon' => 'bi-window-stack', 'is_active' => true, 'sort_order' => 10],
        );

        $count = 0;
        foreach (self::CATALOG as $groupKey => $items) {
            [$catName, $c1, $c2, $layout, $type] = self::GROUPS[$groupKey];
            $category = ProductCategory::firstOrCreate(
                ['slug' => Str::slug($catName)],
                ['parent_id' => $parent->id, 'name' => $catName, 'is_active' => true, 'sort_order' => $count],
            );

            foreach ($items as $index => [$name, $naira]) {
                Product::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    [
                        'vendor_id' => $vendor->id,
                        'kind' => ProductKind::Digital->value,
                        'category_id' => $category->id,
                        'name' => $name,
                        'short_description' => "A production-ready {$name} built on Laravel 12 + Bootstrap 5 — responsive, SEO-ready, role-based access, clean documented code.",
                        'description' => $this->description($name),
                        'type' => $type,
                        'price' => $naira * 100,               // Naira → kobo
                        'compare_price' => (int) round($naira * 100 * 1.3),
                        'thumbnail' => $this->makeCover($name, $catName, [$c1, $c2], $layout),
                        'is_downloadable' => true,
                        'status' => ProductStatus::Active->value,
                        'availability' => 'coming_soon',
                        'is_featured' => $index === 0,
                        'approved_at' => now(),
                    ],
                );
                $count++;
            }
        }

        $this->command->info("Volamani website catalog seeded ({$count} coming-soon products).");
    }

    private function officialVendor(): Vendor
    {
        Role::findOrCreate('vendor', 'web');

        $user = User::firstOrCreate(
            ['email' => 'studio@volamani.com'],
            [
                'name' => 'Volamani Studio',
                'username' => 'volamani-studio',
                'password' => 'Vlm-'.Str::random(24),
                'user_type' => 'individual',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );
        $user->syncRoles(['vendor']);
        Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0, 'escrow_balance' => 0]);

        return Vendor::firstOrCreate(
            ['user_id' => $user->id],
            [
                'business_name' => 'Volamani Studio',
                'slug' => 'volamani-studio',
                'tagline' => 'Production-ready websites, systems & platforms',
                'description' => 'The official Volamani build studio. We design and develop production-ready websites, management systems and SaaS platforms on Laravel 12 + Bootstrap 5 — clean, documented code, responsive, SEO-ready, with role-based access and 6–12 months of updates.',
                'category' => 'Web Development',
                'status' => 'active',
                'is_featured' => true,
                'approved_at' => now(),
                'verified_at' => now(),
            ],
        );
    }

    private function description(string $name): string
    {
        return "{$name} — a production-ready build from Volamani Studio.\n\n"
            .'Built on modern Laravel 12 architecture with a clean Bootstrap 5 UI, it ships responsive, '
            ."SEO-ready and with role-based access control, well-documented code and production-grade quality.\n\n"
            ."• Modern, responsive UI/UX (Bootstrap 5)\n"
            ."• Laravel 12 architecture, clean documented code\n"
            ."• Role-based access control\n"
            ."• SEO-ready and production-grade\n"
            ."• 6–12 months of updates & support included\n\n"
            .'This build is available for pre-order: reserve it with a deposit and pay the balance on delivery.';
    }

    private function makeCover(string $name, string $category, array $colors, string $layout): string
    {
        $path = 'catalog/'.Str::slug($name).'.svg';

        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, $this->coverSvg($name, $category, $colors, $layout));
        }

        return $path;
    }

    private function coverSvg(string $title, string $category, array $colors, string $layout): string
    {
        [$c1, $c2] = $colors;
        $body = match ($layout) {
            'dashboard' => $this->dashboardBody($c1, $c2),
            'grid' => $this->gridBody($c1),
            default => $this->siteBody($c1),
        };

        $len = mb_strlen($title);
        $fs = $len > 30 ? 20 : ($len > 22 ? 24 : 30);
        $t = htmlspecialchars($title, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $cat = htmlspecialchars(strtoupper($category), ENT_QUOTES | ENT_XML1, 'UTF-8');

        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 600" width="800" height="600">
          <defs><linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="{$c1}"/><stop offset="1" stop-color="{$c2}"/></linearGradient></defs>
          <rect width="800" height="600" fill="url(#bg)"/>
          <text x="48" y="64" font-family="Segoe UI, Arial, sans-serif" font-size="19" font-weight="700" letter-spacing="3" fill="#ffffff" opacity="0.85">VOLAMANI</text>
          <rect x="120" y="112" width="560" height="322" rx="16" fill="#ffffff" opacity="0.97"/>
          <rect x="120" y="112" width="560" height="40" rx="16" fill="#f1f5f9"/>
          <rect x="120" y="140" width="560" height="12" fill="#f1f5f9"/>
          <circle cx="148" cy="132" r="5.5" fill="#ef4444"/><circle cx="168" cy="132" r="5.5" fill="#f59e0b"/><circle cx="188" cy="132" r="5.5" fill="#22c55e"/>
          {$body}
          <text x="400" y="498" text-anchor="middle" font-family="Segoe UI, Arial, sans-serif" font-size="{$fs}" font-weight="700" fill="#ffffff">{$t}</text>
          <text x="400" y="532" text-anchor="middle" font-family="Segoe UI, Arial, sans-serif" font-size="14" letter-spacing="2" fill="#ffffff" opacity="0.8">{$cat}</text>
        </svg>
        SVG;
    }

    /** Marketing-site mockup: nav + hero + three feature blocks. */
    private function siteBody(string $c1): string
    {
        return <<<SVG
          <rect x="150" y="176" width="70" height="14" rx="4" fill="{$c1}"/>
          <rect x="360" y="179" width="44" height="8" rx="4" fill="#e2e8f0"/><rect x="420" y="179" width="44" height="8" rx="4" fill="#e2e8f0"/><rect x="480" y="179" width="44" height="8" rx="4" fill="#e2e8f0"/>
          <rect x="580" y="173" width="60" height="20" rx="6" fill="{$c1}"/>
          <rect x="150" y="210" width="330" height="76" rx="10" fill="{$c1}" opacity="0.14"/>
          <rect x="500" y="210" width="140" height="76" rx="10" fill="#eef2f6"/>
          <rect x="150" y="302" width="150" height="88" rx="10" fill="#eef2f6"/><rect x="320" y="302" width="150" height="88" rx="10" fill="#eef2f6"/><rect x="490" y="302" width="150" height="88" rx="10" fill="#eef2f6"/>
        SVG;
    }

    /** App dashboard mockup: sidebar + top bar + stat cards + chart. */
    private function dashboardBody(string $c1, string $c2): string
    {
        return <<<SVG
          <rect x="150" y="172" width="74" height="220" rx="8" fill="{$c2}"/>
          <rect x="166" y="188" width="42" height="8" rx="4" fill="#ffffff" opacity="0.6"/><rect x="166" y="208" width="42" height="8" rx="4" fill="#ffffff" opacity="0.35"/><rect x="166" y="228" width="42" height="8" rx="4" fill="#ffffff" opacity="0.35"/>
          <rect x="238" y="172" width="402" height="30" rx="6" fill="#f1f5f9"/>
          <rect x="238" y="214" width="122" height="56" rx="8" fill="{$c1}" opacity="0.16"/><rect x="378" y="214" width="122" height="56" rx="8" fill="#eef2f6"/><rect x="518" y="214" width="122" height="56" rx="8" fill="#eef2f6"/>
          <rect x="238" y="284" width="402" height="108" rx="8" fill="#f8fafc"/>
          <rect x="260" y="360" width="26" height="16" fill="{$c1}"/><rect x="300" y="342" width="26" height="34" fill="{$c1}" opacity="0.7"/><rect x="340" y="322" width="26" height="54" fill="{$c1}"/><rect x="380" y="350" width="26" height="26" fill="{$c1}" opacity="0.7"/><rect x="420" y="330" width="26" height="46" fill="{$c1}"/><rect x="460" y="316" width="26" height="60" fill="{$c1}" opacity="0.7"/>
        SVG;
    }

    /** Marketplace / listing mockup: search bar + grid of cards. */
    private function gridBody(string $c1): string
    {
        return <<<SVG
          <rect x="150" y="176" width="380" height="24" rx="8" fill="#f1f5f9"/>
          <rect x="548" y="176" width="92" height="24" rx="8" fill="{$c1}"/>
          <rect x="150" y="214" width="150" height="84" rx="10" fill="#eef2f6"/><rect x="320" y="214" width="150" height="84" rx="10" fill="#eef2f6"/><rect x="490" y="214" width="150" height="84" rx="10" fill="#eef2f6"/>
          <rect x="150" y="308" width="150" height="84" rx="10" fill="#eef2f6"/><rect x="320" y="308" width="150" height="84" rx="10" fill="#eef2f6"/><rect x="490" y="308" width="150" height="84" rx="10" fill="#eef2f6"/>
          <rect x="150" y="214" width="150" height="8" rx="4" fill="{$c1}"/><rect x="320" y="308" width="150" height="8" rx="4" fill="{$c1}"/>
        SVG;
    }
}
