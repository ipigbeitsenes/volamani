<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\ServiceOrderStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $reference
 * @property int $service_id
 * @property int $package_id
 * @property int $buyer_id
 * @property int $vendor_id
 * @property ServiceOrderStatus $status
 * @property PaymentStatus $payment_status
 * @property int $total_amount
 * @property int $platform_fee
 * @property int $vendor_earnings
 * @property string|null $payment_reference
 * @property string|null $payment_method
 * @property string|null $requirements
 * @property int $revisions_allowed
 * @property int $revisions_used
 * @property Carbon|null $due_at
 * @property Carbon|null $paid_at
 * @property Carbon|null $started_at
 * @property Carbon|null $delivered_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $cancelled_at
 * @property string|null $cancellation_reason
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $buyer
 * @property-read Collection<int, ServiceOrderMessage> $messages
 * @property-read int|null $messages_count
 * @property-read ServicePackage $package
 * @property-read FreelanceService|null $service
 * @property-read Vendor|null $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder whereBuyerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder whereCancellationReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder whereDeliveredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder whereDueAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder wherePackageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder wherePaymentReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder wherePlatformFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder whereRequirements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder whereRevisionsAllowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder whereRevisionsUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder whereVendorEarnings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder withoutTrashed()
 *
 * @mixin \Eloquent
 */
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
