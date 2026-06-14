<?php

namespace App\Models;

use App\Enums\ReturnReason;
use App\Enums\ReturnStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'reason'          => ReturnReason::class,
            'status'          => ReturnStatus::class,
            'photos'          => 'array',
            'refunded_amount' => 'integer',
            'requested_at'    => 'datetime',
            'approved_at'     => 'datetime',
            'rejected_at'     => 'datetime',
            'shipped_back_at' => 'datetime',
            'refunded_at'     => 'datetime',
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
