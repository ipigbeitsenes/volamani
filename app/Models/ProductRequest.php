<?php

namespace App\Models;

use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'buyer_id', 'category_id', 'title', 'description',
        'budget_min', 'budget_max', 'attachments', 'deadline_at',
        'status', 'quotations_count', 'accepted_quotation_id',
        'closed_at', 'is_public', 'location',
    ];

    protected function casts(): array
    {
        return [
            'status'      => RequestStatus::class,
            'attachments' => 'array',
            'is_public'   => 'boolean',
            'deadline_at' => 'datetime',
            'closed_at'   => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(ProductRequestQuotation::class, 'request_id');
    }

    public function acceptedQuotation(): BelongsTo
    {
        return $this->belongsTo(ProductRequestQuotation::class, 'accepted_quotation_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeOpen($query)
    {
        return $query->where('status', RequestStatus::Open)->where('is_public', true);
    }

    public function scopeForBuyer($query, int $userId)
    {
        return $query->where('buyer_id', $userId);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isOpen(): bool
    {
        return $this->status === RequestStatus::Open;
    }

    public function isExpired(): bool
    {
        return $this->deadline_at && $this->deadline_at->isPast() && $this->isOpen();
    }

    public function budgetRange(): string
    {
        if ($this->budget_min && $this->budget_max) {
            return money($this->budget_min) . ' – ' . money($this->budget_max);
        }
        if ($this->budget_max) {
            return 'Up to ' . money($this->budget_max);
        }
        if ($this->budget_min) {
            return 'From ' . money($this->budget_min);
        }
        return 'Flexible';
    }

    public function hasQuotedBy(Vendor $vendor): bool
    {
        return $this->quotations()->where('vendor_id', $vendor->id)->exists();
    }

    public function getQuotationBy(Vendor $vendor): ?ProductRequestQuotation
    {
        return $this->quotations()->where('vendor_id', $vendor->id)->first();
    }
}
