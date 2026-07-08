<?php

namespace App\Models;

use App\Enums\ChargebackStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chargeback extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'reference', 'payment_id', 'escrow_id', 'buyer_id', 'vendor_id',
        'gateway_reference', 'amount', 'clawed_back_amount', 'unrecovered_amount',
        'reason', 'status', 'evidence',
        'resolved_by', 'resolution_note', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'status'             => ChargebackStatus::class,
            'evidence'           => 'array',
            'amount'             => 'integer',
            'clawed_back_amount' => 'integer',
            'unrecovered_amount' => 'integer',
            'resolved_at'        => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Chargeback $c) {
            if (empty($c->reference)) {
                $c->reference = generate_reference('CBK');
            }
        });
    }

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function escrow(): BelongsTo
    {
        return $this->belongsTo(Escrow::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // ─── State helpers ──────────────────────────────────────────────────────────

    public function isOpen(): bool
    {
        return $this->status->isOpen();
    }

    public function canContest(): bool
    {
        return $this->status === ChargebackStatus::Open;
    }

    public function canResolve(): bool
    {
        return $this->status->isOpen();
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', [ChargebackStatus::Open->value, ChargebackStatus::Contested->value]);
    }

    public function evidenceNote(): ?string
    {
        return $this->evidence['note'] ?? null;
    }

    public function evidenceFiles(): array
    {
        return $this->evidence['files'] ?? [];
    }
}
