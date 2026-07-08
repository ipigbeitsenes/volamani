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
        'requires_shipping', 'total_amount', 'platform_fee', 'vendor_earnings', 'shipping_fee',
        'ship_to_name', 'ship_to_phone', 'ship_to_address', 'ship_to_city', 'ship_to_state',
        'tracking_number', 'courier',
        'payment_reference', 'payment_method', 'currency',
        'notes', 'cancellation_reason', 'cancelled_by',
        'paid_at', 'shipped_at', 'delivered_at', 'completed_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status'            => OrderStatus::class,
            'payment_status'    => PaymentStatus::class,
            'requires_shipping' => 'boolean',
            'shipping_fee'      => 'integer',
            'paid_at'           => 'datetime',
            'shipped_at'        => 'datetime',
            'delivered_at'      => 'datetime',
            'completed_at'      => 'datetime',
            'cancelled_at'      => 'datetime',
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

    public function returnRequests(): HasMany
    {
        return $this->hasMany(ReturnRequest::class)->latest();
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

    /**
     * Whether the SELLER may cancel this order (e.g. can't deliver / undeliverable
     * address / technical issue). Allowed while paid and still in flight, but NOT
     * once completed, already cancelled/refunded, or under an active dispute
     * (a dispute must be resolved by support, not unilaterally cancelled).
     */
    public function canVendorCancel(): bool
    {
        return $this->isPaid()
            && ! in_array($this->status, [
                OrderStatus::Completed,
                OrderStatus::Cancelled,
                OrderStatus::Refunded,
                OrderStatus::Disputed,
            ], true);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function canBeDisputed(): bool
    {
        return in_array($this->status, [
            OrderStatus::Paid,
            OrderStatus::Processing,
            OrderStatus::InProgress,
            OrderStatus::Shipped,
            OrderStatus::Delivered,
        ]);
    }

    // ─── Physical fulfillment ───────────────────────────────────────────────────

    public function isPhysical(): bool
    {
        return (bool) $this->requires_shipping;
    }

    /** Vendor can mark a physical order shipped once it's paid and not yet shipped/delivered. */
    public function canShip(): bool
    {
        return $this->isPhysical()
            && $this->isPaid()
            && in_array($this->status, [OrderStatus::Paid, OrderStatus::Processing], true);
    }

    /** Vendor can mark delivered after shipping (or directly after payment for local handoff). */
    public function canMarkDelivered(): bool
    {
        return $this->isPhysical()
            && $this->isPaid()
            && in_array($this->status, [OrderStatus::Paid, OrderStatus::Processing, OrderStatus::Shipped], true);
    }

    /** Buyer can confirm receipt (release escrow) once paid and not already completed. */
    public function canConfirmReceipt(): bool
    {
        return $this->isPaid()
            && ! $this->isCompleted()
            && ! in_array($this->status, [OrderStatus::Cancelled, OrderStatus::Refunded, OrderStatus::Disputed], true);
    }

    /** The in-flight return for this order, if any. */
    public function activeReturn(): ?ReturnRequest
    {
        return $this->returnRequests->first(fn (ReturnRequest $r) => $r->isActive())
            ?? $this->returnRequests()->active()->first();
    }

    public function hasActiveReturn(): bool
    {
        return $this->activeReturn() !== null;
    }

    public function returnWindowClosesAt(): ?\Illuminate\Support\Carbon
    {
        return $this->delivered_at?->copy()->addDays((int) config('business_days.return_window_days', 7));
    }

    /**
     * Whether the buyer may open a return: a delivered physical order, still
     * within the return window, with no active return and not already
     * completed/refunded. (Escrow must still hold the funds — verified at the
     * point of action.)
     */
    public function canRequestReturn(): bool
    {
        return $this->isPhysical()
            && $this->isPaid()
            && $this->delivered_at !== null
            && ! in_array($this->status, [OrderStatus::Completed, OrderStatus::Refunded, OrderStatus::Cancelled], true)
            && ($this->returnWindowClosesAt()?->isFuture() ?? false)
            && ! $this->hasActiveReturn();
    }

    public function shippingAddressLines(): array
    {
        return array_values(array_filter([
            $this->ship_to_name,
            $this->ship_to_phone,
            $this->ship_to_address,
            trim(($this->ship_to_city ?? '') . ($this->ship_to_state ? ', ' . $this->ship_to_state : '')),
        ]));
    }
}
