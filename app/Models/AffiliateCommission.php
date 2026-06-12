<?php

namespace App\Models;

use App\Enums\CommissionStatus;
use App\Enums\CommissionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AffiliateCommission extends Model
{
    protected $fillable = [
        'reference', 'affiliate_account_id', 'referral_id',
        'earnable_type', 'earnable_id', 'buyer_id',
        'type', 'amount', 'rate_applied', 'status',
        'wallet_ledger_id', 'note', 'approved_at', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'type'         => CommissionType::class,
            'status'       => CommissionStatus::class,
            'amount'       => 'integer',
            'rate_applied' => 'decimal:2',
            'approved_at'  => 'datetime',
            'paid_at'      => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (AffiliateCommission $commission) {
            if (empty($commission->reference)) {
                $commission->reference = generate_reference('AFC');
            }
        });
    }

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function account(): BelongsTo
    {
        return $this->belongsTo(AffiliateAccount::class, 'affiliate_account_id');
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function earnable(): MorphTo
    {
        return $this->morphTo();
    }

    // ─── State helpers ──────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === CommissionStatus::Pending;
    }

    public function isPaid(): bool
    {
        return $this->status === CommissionStatus::Paid;
    }

    public function canBeApproved(): bool
    {
        return in_array($this->status, [CommissionStatus::Pending, CommissionStatus::Approved]);
    }
}
