<?php

namespace App\Models;

use App\Enums\DisputeReason;
use App\Enums\DisputeResolution;
use App\Enums\DisputeStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dispute extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'reference', 'escrow_id', 'buyer_id', 'vendor_id', 'raised_by',
        'reason', 'description', 'status',
        'resolution', 'resolution_amount', 'resolution_note', 'resolved_by',
        'resolved_at', 'escalated_at', 'response_due_at', 'sla_breached',
    ];

    protected function casts(): array
    {
        return [
            'reason'            => DisputeReason::class,
            'status'            => DisputeStatus::class,
            'resolution'        => DisputeResolution::class,
            'resolution_amount' => 'integer',
            'resolved_at'       => 'datetime',
            'escalated_at'      => 'datetime',
            'response_due_at'   => 'datetime',
            'sla_breached'      => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Dispute $dispute) {
            if (empty($dispute->reference)) {
                $dispute->reference = generate_reference('DSP');
            }
        });
    }

    // ─── Relationships ──────────────────────────────────────────────────────────

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

    public function raisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raised_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(DisputeMessage::class)->orderBy('created_at');
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    public function isOpen(): bool
    {
        return in_array($this->status, [
            DisputeStatus::Open,
            DisputeStatus::UnderReview,
            DisputeStatus::AwaitingResponse,
            DisputeStatus::Escalated,
        ]);
    }

    public function isResolved(): bool
    {
        return in_array($this->status, [DisputeStatus::Resolved, DisputeStatus::Closed]);
    }

    public function canBeResolved(): bool
    {
        return $this->isOpen();
    }

    public function canBeEscalated(): bool
    {
        return in_array($this->status, [DisputeStatus::Open, DisputeStatus::UnderReview, DisputeStatus::AwaitingResponse]);
    }

    /** Is the given user a party to (or staff on) this dispute? */
    public function involves(User $user): bool
    {
        return $this->buyer_id === $user->id
            || $this->raised_by === $user->id
            || ($this->vendor && $this->vendor->user_id === $user->id);
    }

    // ─── SLA helpers ──────────────────────────────────────────────────────────

    /** An open dispute whose awaited-response deadline has passed. */
    public function isSlaOverdue(): bool
    {
        return $this->isOpen()
            && $this->response_due_at !== null
            && $this->response_due_at->isPast();
    }

    public function slaCountdownLabel(): ?string
    {
        if (! $this->response_due_at || ! $this->isOpen()) {
            return null;
        }

        return $this->response_due_at->isPast()
            ? 'overdue by ' . $this->response_due_at->diffForHumans(null, true)
            : 'due ' . $this->response_due_at->diffForHumans();
    }
}
