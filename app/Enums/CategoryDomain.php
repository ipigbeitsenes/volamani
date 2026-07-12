<?php

namespace App\Enums;

use App\Models\PhysicalCategory;
use App\Models\ProductCategory;
use App\Models\ServiceCategory;

/**
 * Which taxonomy a category belongs to. Each domain has its own dedicated
 * categories table (separate-tables-per-domain design).
 */
enum CategoryDomain: string
{
    case Digital = 'digital';
    case Physical = 'physical';
    case Service = 'service';

    public function label(): string
    {
        return match ($this) {
            self::Digital => 'Digital Product',
            self::Physical => 'Physical Product',
            self::Service => 'Service',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Digital => 'primary',
            self::Physical => 'warning',
            self::Service => 'success',
        };
    }

    /** The Eloquent model class backing this domain's category tree. */
    public function modelClass(): string
    {
        return match ($this) {
            self::Digital => ProductCategory::class,
            self::Physical => PhysicalCategory::class,
            self::Service => ServiceCategory::class,
        };
    }
}
