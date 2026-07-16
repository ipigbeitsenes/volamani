<?php

namespace App\Models;

use App\Enums\ReturnReason;
use App\Enums\ReturnStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property string $reference
 * @property int $order_id
 * @property int $buyer_id
 * @property int $vendor_id
 * @property ReturnReason $reason
 * @property string|null $description
 * @property array<array-key, mixed>|null $photos
 * @property ReturnStatus $status
 * @property string|null $return_tracking
 * @property string|null $decision_note
 * @property int|null $decided_by
 * @property int|null $refunded_amount
 * @property Carbon|null $requested_at
 * @property Carbon|null $approved_at
 * @property Carbon|null $rejected_at
 * @property Carbon|null $shipped_back_at
 * @property Carbon|null $refunded_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read User|null $buyer
 * @property-read User|null $decidedBy
 * @property-read Order|null $order
 * @property-read Vendor|null $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnRequest active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnRequest whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnRequest whereBuyerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnRequest whereDecidedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnRequest whereDecisionNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnRequest whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnRequest whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnRequest wherePhotos($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnRequest whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnRequest whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnRequest whereRefundedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnRequest whereRefundedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnRequest whereRejectedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnRequest whereRequestedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnRequest whereReturnTracking($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnRequest whereShippedBackAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnRequest whereVendorId($value)
 *
 * @mixin \Eloquent
 */
class ReturnRequest extends Model
{
    use Auditable;

    protected $fillable = [
        'reference', 'order_id', 'buyer_id', 'vendor_id',
        'reason', 'description', 'photos', 'status',
        'return_tracking', 'decision_note', 'decided_by', 'refunded_amount',
        'requested_at', 'approved_at', 'rejected_at', 'shipped_back_at', 'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'reason' => ReturnReason::class,
            'status' => ReturnStatus::class,
            'photos' => 'array',
            'refunded_amount' => 'integer',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'shipped_back_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ReturnRequest $r) {
            if (empty($r->reference)) {
                $r->reference = generate_reference('RET');
            }
        });
    }

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    // ─── State helpers ──────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function canApprove(): bool
    {
        return $this->status === ReturnStatus::Requested;
    }

    public function canReject(): bool
    {
        return $this->status === ReturnStatus::Requested;
    }

    /** Buyer can record return shipping once the seller has approved. */
    public function canMarkShipped(): bool
    {
        return $this->status === ReturnStatus::Approved;
    }

    /** Seller/admin confirm receipt of the returned item → triggers refund. */
    public function canConfirmReceived(): bool
    {
        return in_array($this->status, [ReturnStatus::Approved, ReturnStatus::ShippedBack], true);
    }

    public function canCancel(): bool
    {
        return in_array($this->status, [ReturnStatus::Requested, ReturnStatus::Approved], true);
    }

    public function photoUrls(): array
    {
        return collect($this->photos ?? [])->map(fn ($p) => media_url($p))->all();
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['requested', 'approved', 'shipped_back']);
    }
}
