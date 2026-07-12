<?php

namespace App\Enums;

enum ProductCondition: string
{
    case New = 'new';
    case Used = 'used';
    case Refurbished = 'refurbished';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Brand New',
            self::Used => 'Used',
            self::Refurbished => 'Refurbished',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::New => 'success',
            self::Used => 'secondary',
            self::Refurbished => 'info',
        };
    }
}
