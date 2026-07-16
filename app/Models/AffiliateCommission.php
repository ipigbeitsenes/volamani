<?php

namespace App\Models;

use App\Enums\CommissionStatus;
use App\Enums\CommissionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $reference
 * @property int $affiliate_account_id
 * @property int|null $referral_id
 * @property string|null $earnable_type
 * @property int|null $earnable_id
 * @property int|null $buyer_id
 * @property CommissionType $type
 * @property int $amount
 * @property numeric|null $rate_applied
 * @property CommissionStatus $status
 * @property int|null $wallet_ledger_id
 * @property string|null $note
 * @property Carbon|null $approved_at
 * @property Carbon|null $paid_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read AffiliateAccount|null $account
 * @property-read User|null $buyer
 * @property-read Model|\Eloquent|null $earnable
 * @property-read Referral|null $referral
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateCommission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateCommission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateCommission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateCommission whereAffiliateAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateCommission whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateCommission whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateCommission whereBuyerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateCommission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateCommission whereEarnableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateCommission whereEarnableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateCommission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateCommission whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateCommission wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateCommission whereRateApplied($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateCommission whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateCommission whereReferralId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateCommission whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateCommission whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateCommission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateCommission whereWalletLedgerId($value)
 *
 * @mixin \Eloquent
 */
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
            'type' => CommissionType::class,
            'status' => CommissionStatus::class,
            'amount' => 'integer',
            'rate_applied' => 'decimal:2',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
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
