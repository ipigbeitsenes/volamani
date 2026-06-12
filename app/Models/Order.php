<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'reference', 'buyer_id', 'vendor_id', 'status', 'payment_status',
        'total_amount', 'platform_fee', 'vendor_earnings',
        'payment_reference', 'payment_method', 'currency',
        'notes', 'paid_at', 'completed_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status'         => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'paid_at'        => 'datetime',
            'completed_at'   => 'datetime',
            'cancelled_at'   => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->reference)) {
                $order->reference = generate_reference('ORD');
            }
        });
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function productDownloads(): HasMany
    {
        return $this->hasMany(ProductDownload::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::Success;
    }

    public function isCompleted(): bool
    {
        return $this->status === OrderStatus::Completed;
    }

    public function canBeDisputed(): bool
    {
        return in_array($this->status, [
            OrderStatus::Paid,
            OrderStatus::Processing,
            OrderStatus::InProgress,
            OrderStatus::Delivered,
        ]);
    }
}
