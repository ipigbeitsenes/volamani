<?php

namespace App\Enums;

enum MatchTargetType: string
{
    case Vendor     = 'vendor';
    case Service    = 'service';
    case Consultant = 'consultant';
    case Partner    = 'partner';

    public function label(): string
    {
        return match ($this) {
            self::Vendor     => 'A vendor / seller',
            self::Service    => 'A service provider',
            self::Consultant => 'A consultant',
            self::Partner    => 'A business partner',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Vendor     => 'bi-shop',
            self::Service    => 'bi-briefcase',
            self::Consultant => 'bi-person-video3',
            self::Partner    => 'bi-people',
        };
    }
}
