<?php

namespace App\Enums;

enum KYCType: string
{
    case Individual = 'individual';
    case Business = 'business';

    public function label(): string
    {
        return match ($this) {
            self::Individual => 'Individual',
            self::Business => 'Business',
        };
    }
}
