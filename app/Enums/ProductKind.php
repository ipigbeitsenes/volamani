<?php

namespace App\Enums;

/**
 * The fulfillment discriminator on products: digital (instant download, escrow
 * auto-release) vs physical (stock, shipping, delivery-confirmed release).
 *
 * NOTE: distinct from the existing `type` column (ProductType), which is the
 * DIGITAL sub-type (ebook / ui_kit / software / …). `kind` is the higher-level
 * fulfillment category.
 */
enum ProductKind: string
{
    case Digital = 'digital';
    case Physical = 'physical';

    public function label(): string
    {
        return match ($this) {
            self::Digital => 'Digital Product',
            self::Physical => 'Physical Product',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Digital => 'bi-cloud-download',
            self::Physical => 'bi-box-seam',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Digital => 'primary',
            self::Physical => 'warning',
        };
    }
}
