<?php

namespace App\Models;

use App\Enums\QuotationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $request_id
 * @property int $vendor_id
 * @property int $price
 * @property int $delivery_days
 * @property string $message
 * @property array<array-key, mixed>|null $attachments
 * @property QuotationStatus $status
 * @property Carbon|null $viewed_at
 * @property Carbon|null $accepted_at
 * @property Carbon|null $rejected_at
 * @property Carbon|null $withdrawn_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ProductRequest|null $request
 * @property-read Vendor|null $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequestQuotation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequestQuotation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequestQuotation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequestQuotation whereAcceptedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequestQuotation whereAttachments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequestQuotation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequestQuotation whereDeliveryDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequestQuotation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequestQuotation whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequestQuotation wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequestQuotation whereRejectedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequestQuotation whereRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequestQuotation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequestQuotation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequestQuotation whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequestQuotation whereViewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductRequestQuotation whereWithdrawnAt($value)
 *
 * @mixin \Eloquent
 */
class ProductRequestQuotation extends Model
{
    protected $fillable = [
        'request_id', 'vendor_id', 'price', 'delivery_days',
        'message', 'attachments', 'status',
        'viewed_at', 'accepted_at', 'rejected_at', 'withdrawn_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => QuotationStatus::class,
            'attachments' => 'array',
            'price' => 'integer',
            'viewed_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
            'withdrawn_at' => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function request(): BelongsTo
    {
        return $this->belongsTo(ProductRequest::class, 'request_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === QuotationStatus::Pending;
    }

    public function isAccepted(): bool
    {
        return $this->status === QuotationStatus::Accepted;
    }

    public function canBeWithdrawn(): bool
    {
        return $this->status === QuotationStatus::Pending;
    }

    public function markViewed(): void
    {
        if (! $this->viewed_at) {
            $this->update(['viewed_at' => now()]);
        }
    }
}
