<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_id
 * @property string $label
 * @property string $path
 * @property string $original_name
 * @property string|null $mime_type
 * @property int $file_size
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $file_size_formatted
 * @property-read Product|null $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile whereFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile whereOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFile whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ProductFile extends Model
{
    protected $fillable = [
        'product_id', 'label', 'path', 'original_name',
        'mime_type', 'file_size', 'sort_order',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2).' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        }

        return $bytes.' bytes';
    }
}
