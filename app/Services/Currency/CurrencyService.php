<?php

namespace App\Services\Currency;

use App\Models\Currency;
use Illuminate\Support\Facades\Schema;

/**
 * Converts money between a vendor's pricing currency and the platform base
 * currency. Amounts are integer minor units (kobo/cents). Conversion assumes
 * 2-decimal currencies and rates expressed as "base units per 1 unit of the
 * currency" (base currency == 1.0).
 */
class CurrencyService
{
    /** @var array<string, array{symbol: string, name: string, rate: float}>|null */
    private ?array $map = null;

    /** The platform's base/settlement currency code (what actually gets charged). */
    public function base(): string
    {
        return (string) settings('currency_code', 'NGN');
    }

    /** All active currencies, keyed by code. */
    public function map(): array
    {
        if ($this->map !== null) {
            return $this->map;
        }

        if (! Schema::hasTable('currencies')) {
            return $this->map = [];
        }

        return $this->map = Currency::active()->get()
            ->mapWithKeys(fn (Currency $c) => [$c->code => [
                'symbol' => $c->symbol,
                'name' => $c->name,
                'rate' => (float) $c->rate_to_base,
            ]])->all();
    }

    public function isSupported(string $code): bool
    {
        return $code === $this->base() || isset($this->map()[$code]);
    }

    public function rate(string $code): float
    {
        if ($code === $this->base()) {
            return 1.0;
        }

        return $this->map()[$code]['rate'] ?? 1.0;
    }

    public function symbol(string $code): string
    {
        if ($code === $this->base()) {
            return (string) settings('currency_symbol', '₦');
        }

        return $this->map()[$code]['symbol'] ?? $code.' ';
    }

    /** Convert a minor amount in $from currency into base-currency minor units. */
    public function toBase(int $minor, string $from): int
    {
        if ($from === $this->base()) {
            return $minor;
        }

        return (int) round($minor * $this->rate($from));
    }

    /** Convert a base-currency minor amount into $to currency minor units. */
    public function fromBase(int $baseMinor, string $to): int
    {
        if ($to === $this->base() || $this->rate($to) <= 0) {
            return $baseMinor;
        }

        return (int) round($baseMinor / $this->rate($to));
    }

    /** Format a minor amount that is already expressed in $code currency. */
    public function format(int $minor, string $code): string
    {
        return $this->symbol($code).number_format($minor / 100, 2);
    }

    /** Format a base-currency minor amount shown in $code (converted). */
    public function displayFromBase(int $baseMinor, string $code): string
    {
        return $this->format($this->fromBase($baseMinor, $code), $code);
    }
}
