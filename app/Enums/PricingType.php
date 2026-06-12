<?php

namespace App\Enums;

enum PricingType: string
{
    case Fixed     = 'fixed';
    case Hourly    = 'hourly';
    case Milestone = 'milestone';

    public function label(): string
    {
        return match ($this) {
            self::Fixed     => 'Fixed Price',
            self::Hourly    => 'Hourly Rate',
            self::Milestone => 'Milestone-based',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Fixed     => 'One agreed price for the entire project',
            self::Hourly    => 'Charge per hour worked',
            self::Milestone => 'Split project into phases with separate payments',
        };
    }
}
