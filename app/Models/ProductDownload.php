<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int $product_file_id
 * @property int $user_id
 * @property int $download_count
 * @property Carbon|null $last_downloaded_at
 * @property string|null $ip_address
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ProductFile $file
 * @property-read Order|null $order
 * @property-read Product|null $product
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductDownload newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductDownload newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductDownload query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductDownload whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductDownload whereDownloadCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductDownload whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductDownload whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductDownload whereLastDownloadedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductDownload whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductDownload whereProductFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductDownload whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductDownload whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductDownload whereUserId($value)
 *
 * @mixin \Eloquent
 */
class ProductDownload extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_file_id',
        'user_id', 'download_count', 'last_downloaded_at', 'ip_address',
    ];

    protected function casts(): array
    {
        return ['last_downloaded_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(ProductFile::class, 'product_file_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasReachedLimit(): bool
    {
        $limit = $this->product->download_limit
            ?? (int) settings('max_download_attempts', 5);

        return $this->download_count >= $limit;
    }
}
