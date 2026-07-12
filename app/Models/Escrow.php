<?php

namespace App\Models;

use App\Enums\EscrowStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

class Escrow extends Model
{
    protected $fillable = [
        'reference', 'escrowable_type', 'escrowable_id',
        'buyer_id', 'vendor_id', 'wallet_id', 'payment_id',
        'total_amount', 'platform_fee', 'vendor_earnings',
        'released_amount', 'refunded_amount',
        'status', 'notes', 'auto_release_at',
        'held_at', 'released_at', 'refunded_at', 'disputed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => EscrowStatus::class,
            'total_amount' => 'integer',
            'platform_fee' => 'integer',
            'vendor_earnings' => 'integer',
            'released_amount' => 'integer',
            'refunded_amount' => 'integer',
            'auto_release_at' => 'datetime',
            'held_at' => 'datetime',
            'released_at' => 'datetime',
            'refunded_at' => 'datetime',
            'disputed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Escrow $escrow) {
            if (empty($escrow->reference)) {
                $escrow->reference = generate_reference('ESC');
            }
        });
    }

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function escrowable(): MorphTo
    {
        return $this->morphTo();
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(EscrowTransaction::class)->latest('created_at');
    }

    // ─── State helpers ──────────────────────────────────────────────────────────

    public function isHolding(): bool
    {
        return $this->status === EscrowStatus::Holding;
    }

    public function isDisputed(): bool
    {
        return $this->status === EscrowStatus::Disputed;
    }

    public function isSettled(): bool
    {
        return in_array($this->status, [EscrowStatus::Released, EscrowStatus::Refunded]);
    }

    /** Vendor earnings still sitting in escrow (vendor basis), not yet released or refunded. */
    public function heldAmount(): int
    {
        if (in_array($this->status, [EscrowStatus::Released, EscrowStatus::Refunded])) {
            return 0;
        }

        return max(0, $this->vendor_earnings - $this->released_amount);
    }

    /** Amount still releasable to the vendor. */
    public function releasableAmount(): int
    {
        return $this->heldAmount();
    }

    /**
     * Buyer's still-refundable money (total basis, fee-inclusive). Mirrors the
     * proportion of vendor earnings still held — money already released to the
     * vendor (and its fee share) can no longer be clawed back.
     */
    public function refundableAmount(): int
    {
        if (in_array($this->status, [EscrowStatus::Released, EscrowStatus::Refunded])) {
            return 0;
        }

        if ($this->vendor_earnings <= 0) {
            return max(0, $this->total_amount - $this->refunded_amount);
        }

        return (int) round($this->heldAmount() / $this->vendor_earnings * $this->total_amount);
    }

    public function canRelease(): bool
    {
        return in_array($this->status, [EscrowStatus::Holding, EscrowStatus::PartiallyReleased, EscrowStatus::Disputed])
            && $this->releasableAmount() > 0;
    }

    public function canRefund(): bool
    {
        return in_array($this->status, [EscrowStatus::Holding, EscrowStatus::PartiallyReleased, EscrowStatus::Disputed])
            && $this->refundableAmount() > 0;
    }

    public function canDispute(): bool
    {
        return in_array($this->status, [EscrowStatus::Holding, EscrowStatus::PartiallyReleased]);
    }

    /** Digital product purchases follow the 24h-ticket / business-day-release rules. */
    public function isProductEscrow(): bool
    {
        return $this->escrowable_type === Order::class;
    }

    /**
     * When the buyer's window to open a support ticket closes. Only applies to
     * digital product purchases (measured from when the funds were held); null
     * for service/consultation escrows, which keep open-ended disputes.
     */
    public function ticketWindowClosesAt(): ?Carbon
    {
        if (! $this->isProductEscrow() || ! $this->held_at) {
            return null;
        }

        return $this->held_at->copy()
            ->addHours((int) config('business_days.ticket_window_hours', 24));
    }

    /**
     * Whether the buyer may still open a support ticket for this purchase. For
     * product escrows the 24h post-purchase window must still be open; other
     * escrow types fall back to the standard dispute eligibility.
     */
    public function canRaiseTicket(): bool
    {
        if (! $this->canDispute()) {
            return false;
        }

        if ($this->isProductEscrow()) {
            return $this->ticketWindowClosesAt()?->isFuture() ?? false;
        }

        return true;
    }

    public function isAutoReleaseDue(): bool
    {
        return $this->isHolding()
            && $this->auto_release_at !== null
            && $this->auto_release_at->isPast();
    }
}
