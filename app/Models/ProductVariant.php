<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'name', 'sku', 'price_override',
        'stock_quantity', 'attributes', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_override' => 'integer',
            'stock_quantity' => 'integer',
            'attributes'     => 'array',
            'is_active'      => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Effective price in kobo — the override if set, else the product's base price. */
    public function effectivePrice(): int
    {
        return $this->price_override ?? (int) $this->product->price;
    }

    public function inStock(): bool
    {
        return $this->stock_quantity > 0;
    }
}
