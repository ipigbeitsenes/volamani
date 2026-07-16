<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_id
 * @property string $path
 * @property string|null $alt_text
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $url
 * @property-read Product|null $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductGallery newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductGallery newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductGallery query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductGallery whereAltText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductGallery whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductGallery whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductGallery wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductGallery whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductGallery whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductGallery whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ProductGallery extends Model
{
    protected $table = 'product_gallery';

    protected $fillable = ['product_id', 'path', 'alt_text', 'sort_order'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrlAttribute(): string
    {
        return media_url($this->path, '');
    }
}
