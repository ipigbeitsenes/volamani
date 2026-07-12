<?php

namespace App\Enums;

/**
 * What a store primarily sells. Drives which catalog tools and category
 * trees the vendor sees, and auto-suggests categories during onboarding.
 */
enum StoreFocus: string
{
    case Physical = 'physical';
    case Digital = 'digital';
    case Service = 'service';
    case Hybrid = 'hybrid';

    public function label(): string
    {
        return match ($this) {
            self::Physical => 'Physical products only',
            self::Digital => 'Digital products only',
            self::Service => 'Services only',
            self::Hybrid => 'Hybrid store (mix of everything)',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Physical => 'bi-box-seam',
            self::Digital => 'bi-cloud-download',
            self::Service => 'bi-briefcase',
            self::Hybrid => 'bi-shuffle',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Physical => 'warning',
            self::Digital => 'primary',
            self::Service => 'success',
            self::Hybrid => 'dark',
        };
    }

    public function sellsPhysical(): bool
    {
        return $this === self::Physical || $this === self::Hybrid;
    }

    public function sellsDigital(): bool
    {
        return $this === self::Digital || $this === self::Hybrid;
    }

    public function sellsServices(): bool
    {
        return $this === self::Service || $this === self::Hybrid;
    }
}
