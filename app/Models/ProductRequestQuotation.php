<?php

namespace App\Models;

use App\Enums\QuotationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'status'       => QuotationStatus::class,
            'attachments'  => 'array',
            'price'        => 'integer',
            'viewed_at'    => 'datetime',
            'accepted_at'  => 'datetime',
            'rejected_at'  => 'datetime',
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
        if (!$this->viewed_at) {
            $this->update(['viewed_at' => now()]);
        }
    }
}
