<?php

namespace App\Enums;

enum TrustTier: string
{
    case New       = 'new';
    case Rising    = 'rising';
    case Trusted   = 'trusted';
    case TopRated  = 'top_rated';

    public static function fromScore(int $score): self
    {
        return match (true) {
            $score >= 85 => self::TopRated,
            $score >= 65 => self::Trusted,
            $score >= 40 => self::Rising,
            default      => self::New,
        };
    }

    public function label(): string
    {
        return match($this) {
            self::New      => 'New Seller',
            self::Rising   => 'Rising Seller',
            self::Trusted  => 'Trusted Seller',
            self::TopRated => 'Top Rated Seller',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::New      => 'secondary',
            self::Rising   => 'info',
            self::Trusted  => 'primary',
            self::TopRated => 'success',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::New      => 'bi-person',
            self::Rising   => 'bi-graph-up-arrow',
            self::Trusted  => 'bi-patch-check',
            self::TopRated => 'bi-award',
        };
    }

    /**
     * Resolved guardrails for this tier: withdrawal cap (kobo, null = unlimited),
     * escrow release window (business days) and active-listing cap (null =
     * unlimited). Reads config/protection.php with per-key admin overrides from
     * the `protection` settings group.
     */
    public function limits(): array
    {
        $defaults = config('protection.tiers.' . $this->value, [
            'withdrawal_cap_daily' => null,
            'escrow_release_days'  => (int) config('business_days.release_days', 3),
            'max_active_listings'  => null,
        ]);

        $override = fn (string $key, $fallback) => ($v = settings("tier_{$this->value}_{$key}")) === null || $v === ''
            ? $fallback
            : $v;

        return [
            'withdrawal_cap_daily' => ($cap = $override('withdrawal_cap_daily', $defaults['withdrawal_cap_daily'])) === null ? null : (int) $cap,
            'escrow_release_days'  => (int) $override('escrow_release_days', $defaults['escrow_release_days']),
            'max_active_listings'  => ($max = $override('max_active_listings', $defaults['max_active_listings'])) === null ? null : (int) $max,
        ];
    }

    public function withdrawalCapDaily(): ?int
    {
        return $this->limits()['withdrawal_cap_daily'];
    }

    public function escrowReleaseDays(): int
    {
        return $this->limits()['escrow_release_days'];
    }

    public function maxActiveListings(): ?int
    {
        return $this->limits()['max_active_listings'];
    }
}
