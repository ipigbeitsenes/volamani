<?php

namespace App\Enums;

/**
 * Identity of WHO is selling — distinct from what they sell (store focus).
 */
enum StoreType: string
{
    case Individual   = 'individual';
    case Business     = 'business';
    case Agency       = 'agency';
    case Expert       = 'expert';
    case Manufacturer = 'manufacturer';

    public function label(): string
    {
        return match ($this) {
            self::Individual   => 'Individual Seller',
            self::Business     => 'Small Business',
            self::Agency       => 'Agency / Company',
            self::Expert       => 'Expert / Professional',
            self::Manufacturer => 'Manufacturer / Wholesaler',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Individual   => 'Freelancers, creators and independent sellers',
            self::Business     => 'Local shops, retail and online stores',
            self::Agency       => 'Marketing agencies, tech companies, creative studios',
            self::Expert       => 'Coaches, consultants, trainers and tutors',
            self::Manufacturer => 'Bulk suppliers, distributors and factories',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Individual   => 'bi-person',
            self::Business     => 'bi-shop',
            self::Agency       => 'bi-building',
            self::Expert       => 'bi-mortarboard',
            self::Manufacturer => 'bi-boxes',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Individual   => 'secondary',
            self::Business     => 'primary',
            self::Agency       => 'info',
            self::Expert       => 'success',
            self::Manufacturer => 'warning',
        };
    }
}
