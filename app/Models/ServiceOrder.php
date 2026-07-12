<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\ServiceOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference', 'service_id', 'package_id', 'buyer_id', 'vendor_id',
        'status', 'payment_status', 'total_amount', 'platform_fee',
        'vendor_earnings', 'payment_reference', 'payment_method',
        'requirements', 'revisions_allowed', 'revisions_used',
        'due_at', 'paid_at', 'started_at', 'delivered_at',
        'completed_at', 'cancelled_at', 'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => ServiceOrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'due_at' => 'datetime',
            'paid_at' => 'datetime',
            'started_at' => 'datetime',
            'delivered_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ServiceOrder $order) {
            if (empty($order->reference)) {
                $order->reference = generate_reference('SVC');
            }
        });
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function service(): BelongsTo
    {
        return $this->belongsTo(FreelanceService::class, 'service_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(ServicePackage::class, 'package_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ServiceOrderMessage::class)->orderBy('created_at');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::Success;
    }

    public function isCompleted(): bool
    {
        return $this->status === ServiceOrderStatus::Completed;
    }

    public function canRequestRevision(): bool
    {
        return $this->status === ServiceOrderStatus::Delivered
            && $this->revisions_used < $this->revisions_allowed;
    }

    public function canAcceptDelivery(): bool
    {
        return $this->status === ServiceOrderStatus::Delivered;
    }

    public function hasRequirements(): bool
    {
        return ! empty($this->requirements);
    }

    public function remainingRevisions(): int
    {
        return max(0, $this->revisions_allowed - $this->revisions_used);
    }

    public function isOverdue(): bool
    {
        return $this->due_at
            && $this->due_at->isPast()
            && ! in_array($this->status, [ServiceOrderStatus::Completed, ServiceOrderStatus::Cancelled]);
    }
}
