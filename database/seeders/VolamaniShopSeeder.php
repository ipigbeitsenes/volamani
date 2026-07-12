<?php

namespace Database\Seeders;

use App\Enums\ProductKind;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductFile;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Seeds the official first-party "Volamani Templates" store with a catalog of
 * website templates so the marketplace isn't empty at launch. Cover images are
 * generated as branded SVGs at run time (written to the public disk), and each
 * product gets a placeholder download so purchases don't break. Idempotent —
 * safe to run more than once. Replace the placeholder files with real template
 * ZIPs from the vendor dashboard when ready.
 */
class VolamaniShopSeeder extends Seeder
{
    /** category slug => [display name, [gradient start, gradient end]] */
    private const CATEGORIES = [
        'business-corporate' => ['Business & Corporate', ['#2563eb', '#1e3a8a']],
        'portfolio' => ['Portfolio', ['#7c3aed', '#4c1d95']],
        'ecommerce' => ['E-commerce', ['#059669', '#065f46']],
        'landing-page' => ['Landing Page', ['#ea580c', '#9a3412']],
        'blog-magazine' => ['Blog & Magazine', ['#db2777', '#9d174d']],
        'admin-dashboard' => ['Admin & Dashboard', ['#0891b2', '#155e75']],
    ];

    /** category slug => [ [name, type, price in cents], ... ] */
    private const CATALOG = [
        'business-corporate' => [
            ['Momentum — Corporate Business Template', 'template', 4900],
            ['Ledger — Consulting & Finance Template', 'template', 3900],
            ['Apex — Digital Agency Template', 'template', 5900],
            ['Meridian — SaaS Company Template', 'template', 4900],
        ],
        'portfolio' => [
            ['Canvas — Creative Portfolio Template', 'template', 2900],
            ['Studio — Photographer Portfolio', 'template', 3900],
            ['Persona — Personal Résumé Template', 'template', 1900],
            ['Showcase — Designer Portfolio', 'template', 2900],
        ],
        'ecommerce' => [
            ['Emporium — Multi-vendor Store Template', 'template', 7900],
            ['Boutique — Fashion Store Template', 'template', 5900],
            ['Cartify — Modern Shop Template', 'template', 4900],
            ['Voltage — Electronics Store Template', 'template', 5900],
        ],
        'landing-page' => [
            ['Launch — Startup Landing Page', 'template', 2900],
            ['Convert — Product Landing Template', 'template', 2900],
            ['Spark — Mobile App Landing Page', 'template', 3900],
            ['Bloom — Newsletter Landing Page', 'template', 1900],
        ],
        'blog-magazine' => [
            ['Chronicle — Online Magazine Template', 'template', 3900],
            ['Journal — Minimal Blog Template', 'template', 2900],
            ['Digest — News & Editorial Template', 'template', 4900],
            ['Muse — Lifestyle Blog Template', 'template', 2900],
        ],
        'admin-dashboard' => [
            ['Nexus — Admin Dashboard UI Kit', 'ui_kit', 7900],
            ['Pulse — Analytics Dashboard Template', 'template', 6900],
            ['Console — SaaS Admin Template', 'template', 7900],
            ['Grid — Bootstrap Admin Template', 'template', 5900],
        ],
    ];

    public function run(): void
    {
        $vendor = $this->officialVendor();
        $categories = $this->ensureCategories();

        $count = 0;
        foreach (self::CATALOG as $catSlug => $products) {
            [$catName, $colors] = self::CATEGORIES[$catSlug];
            $category = $categories[$catSlug];

            foreach ($products as $index => [$name, $type, $price]) {
                $product = Product::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    [
                        'vendor_id' => $vendor->id,
                        'kind' => ProductKind::Digital->value,
                        'category_id' => $category->id,
                        'name' => $name,
                        'short_description' => $this->shortDescription($name, $catName),
                        'description' => $this->description($name, $catName),
                        'type' => $type,
                        'price' => $price,
                        'compare_price' => (int) round($price * 1.6),
                        'thumbnail' => $this->makeCover($name, $catName, $colors),
                        'is_downloadable' => true,
                        'status' => ProductStatus::Active->value,
                        'is_featured' => $index === 0,       // one featured per category
                        'approved_at' => now(),
                    ],
                );

                $this->seedPlaceholderFile($product);
                $count++;
            }
        }

        $this->command->info("Volamani Templates store seeded ({$count} products).");
    }

    /** The official, verified, featured first-party store vendor. */
    private function officialVendor(): Vendor
    {
        Role::findOrCreate('vendor', 'web');

        $user = User::firstOrCreate(
            ['email' => 'templates@volamani.com'],
            [
                'name' => 'Volamani Templates',
                'username' => 'volamani-templates',
                'password' => 'Vlm-'.Str::random(24),   // hashed via cast; reset it to log in as the store
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
                'business_name' => 'Volamani Templates',
                'slug' => 'volamani-templates',
                'tagline' => 'Premium website templates, ready to launch',
                'description' => 'The official Volamani template studio — professionally designed, fully responsive website templates for businesses, portfolios, online stores and more. Instant download, clean code, and lifetime updates.',
                'category' => 'Web Design',
                'status' => 'active',
                'is_featured' => true,
                'approved_at' => now(),
                'verified_at' => now(),
            ],
        );
    }

    /** Ensure the Website Templates category tree exists; return sub-cat models keyed by slug. */
    private function ensureCategories(): array
    {
        $parent = ProductCategory::firstOrCreate(
            ['slug' => 'website-templates'],
            ['name' => 'Website Templates', 'icon' => 'bi-window-desktop', 'is_active' => true, 'sort_order' => 20],
        );

        $map = [];
        $sort = 1;
        foreach (self::CATEGORIES as $slug => [$name]) {
            $map[$slug] = ProductCategory::firstOrCreate(
                ['slug' => $slug],
                ['parent_id' => $parent->id, 'name' => $name, 'is_active' => true, 'sort_order' => $sort++],
            );
        }

        return $map;
    }

    private function shortDescription(string $name, string $category): string
    {
        $title = $this->titleOf($name);

        return "A modern, fully responsive {$category} website template. {$title} ships with clean HTML/CSS, reusable sections and a mobile-first layout — launch in minutes.";
    }

    private function description(string $name, string $category): string
    {
        $title = $this->titleOf($name);

        return "{$title} is a premium {$category} website template from Volamani. "
            ."Built responsive and mobile-first, it includes ready-made sections, a clean and well-commented codebase, and easy customization so you can go live fast.\n\n"
            ."• Fully responsive across desktop, tablet and mobile\n"
            ."• Clean, well-structured, easy to edit\n"
            ."• Reusable sections and components\n"
            ."• Instant download with lifetime updates\n\n"
            .'Delivered instantly after purchase and protected by Volamani escrow.';
    }

    /** The short name before the em dash, e.g. "Momentum — Corporate…" → "Momentum". */
    private function titleOf(string $name): string
    {
        return trim(explode('—', $name)[0]);
    }

    /**
     * Generate a branded SVG cover, store it on the public disk, and return its
     * path (resolved for display via media_url / thumbnail_url).
     */
    private function makeCover(string $name, string $category, array $colors): string
    {
        $path = 'templates/'.Str::slug($name).'.svg';

        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, $this->coverSvg($this->titleOf($name), $category, $colors));
        }

        return $path;
    }

    private function coverSvg(string $title, string $category, array $colors): string
    {
        [$c1, $c2] = $colors;
        $title = htmlspecialchars($title, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $category = htmlspecialchars(strtoupper($category), ENT_QUOTES | ENT_XML1, 'UTF-8');

        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 600" width="800" height="600">
          <defs>
            <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
              <stop offset="0" stop-color="{$c1}"/>
              <stop offset="1" stop-color="{$c2}"/>
            </linearGradient>
          </defs>
          <rect width="800" height="600" fill="url(#bg)"/>
          <text x="48" y="66" font-family="Segoe UI, Arial, sans-serif" font-size="20" font-weight="700" letter-spacing="3" fill="#ffffff" opacity="0.85">VOLAMANI</text>
          <rect x="120" y="118" width="560" height="330" rx="16" fill="#ffffff" opacity="0.97"/>
          <rect x="120" y="118" width="560" height="44" rx="16" fill="#f1f5f9"/>
          <rect x="120" y="150" width="560" height="12" fill="#f1f5f9"/>
          <circle cx="150" cy="140" r="6" fill="#ef4444"/>
          <circle cx="172" cy="140" r="6" fill="#f59e0b"/>
          <circle cx="194" cy="140" r="6" fill="#22c55e"/>
          <rect x="150" y="192" width="220" height="22" rx="6" fill="#e2e8f0"/>
          <rect x="150" y="228" width="360" height="12" rx="6" fill="#eef2f6"/>
          <rect x="150" y="250" width="300" height="12" rx="6" fill="#eef2f6"/>
          <rect x="150" y="292" width="150" height="96" rx="10" fill="{$c1}" opacity="0.16"/>
          <rect x="320" y="292" width="150" height="96" rx="10" fill="#eef2f6"/>
          <rect x="490" y="292" width="150" height="96" rx="10" fill="#eef2f6"/>
          <rect x="150" y="404" width="132" height="30" rx="8" fill="{$c1}"/>
          <text x="400" y="506" text-anchor="middle" font-family="Segoe UI, Arial, sans-serif" font-size="34" font-weight="700" fill="#ffffff">{$title}</text>
          <text x="400" y="540" text-anchor="middle" font-family="Segoe UI, Arial, sans-serif" font-size="15" letter-spacing="2" fill="#ffffff" opacity="0.85">{$category}</text>
        </svg>
        SVG;
    }

    /**
     * Give each product a placeholder download so the purchase flow works. Swap
     * these for the real template ZIPs from the vendor dashboard when ready.
     */
    private function seedPlaceholderFile(Product $product): void
    {
        $path = 'product-files/'.$product->id.'/'.Str::slug($product->name).'-starter.txt';

        if (! Storage::disk('private')->exists($path)) {
            $contents = "Thank you for purchasing \"{$product->name}\" on Volamani.\n\n"
                ."This is a placeholder delivery file. The store owner will replace it\n"
                ."with the real template package (HTML/CSS/assets ZIP) from the vendor\n"
                ."dashboard. If you're seeing this on a live purchase, please contact\n"
                ."support and we'll make it right.\n";

            Storage::disk('private')->put($path, $contents);
        }

        ProductFile::firstOrCreate(
            ['product_id' => $product->id, 'path' => $path],
            [
                'label' => 'Template package (placeholder)',
                'original_name' => Str::slug($product->name).'-starter.txt',
                'mime_type' => 'text/plain',
                'file_size' => Storage::disk('private')->size($path),
                'sort_order' => 0,
            ],
        );
    }
}
