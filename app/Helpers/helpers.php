<?php

use App\Models\Setting;
use App\Support\BusinessDayCalculator;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (! function_exists('currency_symbol')) {
    /** The platform's display currency symbol (admin-configurable, default $). */
    function currency_symbol(): string
    {
        return (string) settings('currency_symbol', '$');
    }
}

if (! function_exists('currency_code')) {
    /** The platform's ISO currency code (admin-configurable, default USD). */
    function currency_code(): string
    {
        return (string) settings('currency_code', 'USD');
    }
}

if (! function_exists('money')) {
    /**
     * Format a minor-unit amount (e.g. cents) for display. The symbol defaults to
     * the configured platform currency; pass one explicitly to override.
     */
    function money(int|float $minor, ?string $symbol = null): string
    {
        return ($symbol ?? currency_symbol()).number_format($minor / 100, 2);
    }
}

if (! function_exists('to_kobo')) {
    function to_kobo(int|float $naira): int
    {
        return (int) round($naira * 100);
    }
}

if (! function_exists('from_kobo')) {
    function from_kobo(int $kobo): float
    {
        return $kobo / 100;
    }
}

if (! function_exists('generate_reference')) {
    function generate_reference(string $prefix = 'VLM'): string
    {
        return strtoupper($prefix).'-'.strtoupper(Str::random(10)).'-'.time();
    }
}

if (! function_exists('active_route')) {
    function active_route(string|array $routes, string $class = 'active'): string
    {
        return request()->routeIs($routes) ? $class : '';
    }
}

if (! function_exists('active_prefix')) {
    function active_prefix(string $prefix, string $class = 'active'): string
    {
        return request()->is($prefix.'*') ? $class : '';
    }
}

if (! function_exists('add_business_days')) {
    function add_business_days(int $days, ?CarbonInterface $from = null): Carbon
    {
        return app(BusinessDayCalculator::class)
            ->addBusinessDays($from ?? now(), $days);
    }
}

if (! function_exists('settings')) {
    function settings(string $key, mixed $default = null): mixed
    {
        return cache()->rememberForever('settings.'.$key, function () use ($key, $default) {
            return Setting::where('key', $key)->value('value') ?? $default;
        });
    }
}

if (! function_exists('feature')) {
    /**
     * Whether a toggleable platform feature is enabled. Defaults to ON when the
     * flag hasn't been seeded yet. Keys are defined in config/features.php.
     */
    function feature(string $key): bool
    {
        return (bool) settings('feature_'.$key, true);
    }
}

if (! function_exists('media_url')) {
    /**
     * Public URL for a stored media path, resolved through the configured disk
     * so it works under both the local 'public' disk and Amazon S3. Returns the
     * given fallback when the path is empty.
     */
    function media_url(?string $path, ?string $fallback = null): ?string
    {
        if (empty($path)) {
            return $fallback;
        }

        // Already a full URL (e.g. an external avatar) — return untouched.
        if (Str::startsWith($path, ['http://', 'https://', '//', 'data:'])) {
            return $path;
        }

        $url = Storage::disk('public')->url($path);

        // For local storage, strip the host so images load from whatever address
        // the page is served on (localhost, 127.0.0.1, a forwarded port or a
        // tunnel) instead of the hard-coded APP_URL host. S3/CDN URLs stay absolute.
        if (settings('storage_driver', 'local') !== 's3'
            && Str::startsWith($url, ['http://', 'https://'])) {
            $relative = parse_url($url, PHP_URL_PATH);

            return $relative !== false && $relative !== null ? $relative : $url;
        }

        return $url;
    }
}
