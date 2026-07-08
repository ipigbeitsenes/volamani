<?php

namespace App\Http\Controllers;

use App\Models\ConsultantProfile;
use App\Models\FreelanceService;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Response;

/**
 * Dynamic XML sitemap served at /sitemap.xml. Lists every publicly indexable
 * URL — static content pages plus active products, services, consultants and
 * vendor storefronts — so Google can discover and re-crawl them efficiently.
 *
 * Each section is guarded independently: a missing table or a bad row degrades
 * that section to empty rather than 500-ing the whole file.
 */
class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [];

        // ── Static, high-value pages ─────────────────────────────────────
        $static = [
            ['home', 'daily', '1.0'],
            ['marketplace.products.index', 'daily', '0.9'],
            ['marketplace.services.index', 'daily', '0.9'],
            ['marketplace.consultants.index', 'weekly', '0.7'],
            ['marketplace.requests.index', 'weekly', '0.6'],
            ['vendors.index', 'daily', '0.8'],
            ['buyer-protection', 'monthly', '0.6'],
            ['pages.about', 'monthly', '0.5'],
            ['pages.seller-guide', 'monthly', '0.6'],
            ['pages.help', 'monthly', '0.4'],
            ['pages.contact', 'monthly', '0.4'],
        ];
        foreach ($static as [$name, $freq, $priority]) {
            if (\Route::has($name)) {
                $urls[] = ['loc' => route($name), 'changefreq' => $freq, 'priority' => $priority];
            }
        }
        foreach (['privacy', 'terms', 'cookies', 'refunds', 'disputes'] as $slug) {
            $urls[] = ['loc' => route('pages.legal', $slug), 'changefreq' => 'yearly', 'priority' => '0.3'];
        }

        // ── Active products ──────────────────────────────────────────────
        try {
            Product::active()->select(['slug', 'updated_at'])->orderByDesc('updated_at')
                ->limit(5000)->each(function ($p) use (&$urls) {
                    if ($p->slug) {
                        $urls[] = [
                            'loc'        => route('marketplace.products.show', $p->slug),
                            'lastmod'    => optional($p->updated_at)->toAtomString(),
                            'changefreq' => 'weekly',
                            'priority'   => '0.8',
                        ];
                    }
                });
        } catch (\Throwable $e) {
        }

        // ── Active freelance services ────────────────────────────────────
        try {
            FreelanceService::active()->select(['slug', 'updated_at'])->orderByDesc('updated_at')
                ->limit(5000)->each(function ($s) use (&$urls) {
                    if ($s->slug) {
                        $urls[] = [
                            'loc'        => route('marketplace.services.show', $s->slug),
                            'lastmod'    => optional($s->updated_at)->toAtomString(),
                            'changefreq' => 'weekly',
                            'priority'   => '0.8',
                        ];
                    }
                });
        } catch (\Throwable $e) {
        }

        // ── Available consultants ────────────────────────────────────────
        try {
            ConsultantProfile::where('is_available', true)->select(['slug', 'updated_at'])
                ->limit(2000)->each(function ($c) use (&$urls) {
                    if ($c->slug) {
                        $urls[] = [
                            'loc'        => route('marketplace.consultants.show', $c->slug),
                            'lastmod'    => optional($c->updated_at)->toAtomString(),
                            'changefreq' => 'weekly',
                            'priority'   => '0.7',
                        ];
                    }
                });
        } catch (\Throwable $e) {
        }

        // ── Vendor storefronts (URL uses the owner's username) ───────────
        try {
            Vendor::where('status', 'active')->with('user:id,username')
                ->limit(5000)->get()->each(function ($v) use (&$urls) {
                    $username = optional($v->user)->username;
                    if ($username) {
                        $urls[] = [
                            'loc'        => route('storefront.show', $username),
                            'lastmod'    => optional($v->updated_at)->toAtomString(),
                            'changefreq' => 'weekly',
                            'priority'   => '0.7',
                        ];
                    }
                });
        } catch (\Throwable $e) {
        }

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
