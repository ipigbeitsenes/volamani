<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property string $reference
 * @property int $buyer_id
 * @property int $vendor_id
 * @property OrderStatus $status
 * @property PaymentStatus $payment_status
 * @property bool $requires_shipping
 * @property int $total_amount
 * @property int $platform_fee
 * @property int $vendor_earnings
 * @property int $shipping_fee
 * @property string|null $ship_to_name
 * @property string|null $ship_to_phone
 * @property string|null $ship_to_address
 * @property string|null $ship_to_city
 * @property string|null $ship_to_state
 * @property string|null $tracking_number
 * @property string|null $courier
 * @property string|null $payment_reference
 * @property string|null $payment_method
 * @property string $currency
 * @property string|null $notes
 * @property string|null $cancellation_reason
 * @property int|null $cancelled_by
 * @property Carbon|null $paid_at
 * @property Carbon|null $shipped_at
 * @property Carbon|null $delivered_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $cancelled_at
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read User|null $buyer
 * @property-read User|null $cancelledBy
 * @property-read Collection<int, OrderItem> $items
 * @property-read int|null $items_count
 * @property-read Collection<int, ProductDownload> $productDownloads
 * @property-read int|null $product_downloads_count
 * @property-read Collection<int, ReturnRequest> $returnRequests
 * @property-read int|null $return_requests_count
 * @property-read Vendor|null $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBuyerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCancellationReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCancelledBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCourier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeliveredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePaymentReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePlatformFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereRequiresShipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShipToAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShipToCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShipToName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShipToPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShipToState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTrackingNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereVendorEarnings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Order extends Model
{
    use Auditable, SoftDeletes;

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
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'requires_shipping' => 'boolean',
            'shipping_fee' => 'integer',
            'paid_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
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

    /** A Pay-on-Delivery order: unpaid up front, no escrow; seller collects on delivery. */
    public function isPod(): bool
    {
        return $this->payment_method === 'pod';
    }

    /** Vendor can mark a physical order shipped once it's paid (or POD) and not yet shipped. */
    public function canShip(): bool
    {
        return $this->isPhysical()
            && ($this->isPaid() || $this->isPod())
            && in_array($this->status, [OrderStatus::Paid, OrderStatus::Processing], true);
    }

    /** Vendor can mark delivered after shipping (or directly after payment for local handoff). */
    public function canMarkDelivered(): bool
    {
        return $this->isPhysical()
            && ($this->isPaid() || $this->isPod())
            && in_array($this->status, [OrderStatus::Paid, OrderStatus::Processing, OrderStatus::Shipped], true);
    }

    /** Buyer can confirm receipt (release escrow, or settle POD) once paid/POD and not completed. */
    public function canConfirmReceipt(): bool
    {
        return ($this->isPaid() || $this->isPod())
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

    public function returnWindowClosesAt(): ?Carbon
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
            trim(($this->ship_to_city ?? '').($this->ship_to_state ? ', '.$this->ship_to_state : '')),
        ]));
    }
}
