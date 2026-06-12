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
}
