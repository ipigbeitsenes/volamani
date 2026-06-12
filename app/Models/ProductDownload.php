<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
