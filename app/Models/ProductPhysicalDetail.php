<?php

namespace App\Models;

use App\Enums\ProductCondition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPhysicalDetail extends Model
{
    protected $fillable = [
        'product_id', 'stock_quantity', 'track_inventory', 'allow_backorder',
        'condition', 'brand', 'weight_grams', 'length_mm', 'width_mm', 'height_mm',
    ];

    protected function casts(): array
    {
        return [
            'stock_quantity'  => 'integer',
            'track_inventory' => 'boolean',
            'allow_backorder' => 'boolean',
            'condition'       => ProductCondition::class,
            'weight_grams'    => 'integer',
            'length_mm'       => 'integer',
            'width_mm'        => 'integer',
            'height_mm'       => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function weightLabel(): ?string
    {
        if (! $this->weight_grams) {
            return null;
        }

        return $this->weight_grams >= 1000
            ? number_format($this->weight_grams / 1000, 2) . ' kg'
            : $this->weight_grams . ' g';
    }

    public function dimensionsLabel(): ?string
    {
        if (! $this->length_mm && ! $this->width_mm && ! $this->height_mm) {
            return null;
        }

        return sprintf('%d × %d × %d mm', $this->length_mm, $this->width_mm, $this->height_mm);
    }
}
