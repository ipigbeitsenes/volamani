<?php

namespace App\Models;

use App\Enums\ProductCondition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_id
 * @property int $stock_quantity
 * @property bool $track_inventory
 * @property bool $allow_backorder
 * @property ProductCondition $condition
 * @property string|null $brand
 * @property int|null $weight_grams
 * @property int|null $length_mm
 * @property int|null $width_mm
 * @property int|null $height_mm
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product|null $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPhysicalDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPhysicalDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPhysicalDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPhysicalDetail whereAllowBackorder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPhysicalDetail whereBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPhysicalDetail whereCondition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPhysicalDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPhysicalDetail whereHeightMm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPhysicalDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPhysicalDetail whereLengthMm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPhysicalDetail whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPhysicalDetail whereStockQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPhysicalDetail whereTrackInventory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPhysicalDetail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPhysicalDetail whereWeightGrams($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPhysicalDetail whereWidthMm($value)
 *
 * @mixin \Eloquent
 */
class ProductPhysicalDetail extends Model
{
    protected $fillable = [
        'product_id', 'stock_quantity', 'track_inventory', 'allow_backorder',
        'condition', 'brand', 'weight_grams', 'length_mm', 'width_mm', 'height_mm',
    ];

    protected function casts(): array
    {
        return [
            'stock_quantity' => 'integer',
            'track_inventory' => 'boolean',
            'allow_backorder' => 'boolean',
            'condition' => ProductCondition::class,
            'weight_grams' => 'integer',
            'length_mm' => 'integer',
            'width_mm' => 'integer',
            'height_mm' => 'integer',
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
            ? number_format($this->weight_grams / 1000, 2).' kg'
            : $this->weight_grams.' g';
    }

    public function dimensionsLabel(): ?string
    {
        if (! $this->length_mm && ! $this->width_mm && ! $this->height_mm) {
            return null;
        }

        return sprintf('%d × %d × %d mm', $this->length_mm, $this->width_mm, $this->height_mm);
    }
}
