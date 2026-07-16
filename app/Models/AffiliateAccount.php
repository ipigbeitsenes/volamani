<?php

namespace App\Models;

use App\Enums\AffiliateStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property AffiliateStatus $status
 * @property numeric|null $commission_rate
 * @property-read int|null $clicks_count
 * @property int $signups_count
 * @property int $conversions_count
 * @property int $total_earned
 * @property int $total_paid
 * @property Carbon|null $joined_at
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, AffiliateClick> $clicks
 * @property-read Collection<int, AffiliateCommission> $commissions
 * @property-read int|null $commissions_count
 * @property-read Collection<int, Referral> $referrals
 * @property-read int|null $referrals_count
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateAccount onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateAccount whereClicksCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateAccount whereCommissionRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateAccount whereConversionsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateAccount whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateAccount whereJoinedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateAccount whereSignupsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateAccount whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateAccount whereTotalEarned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateAccount whereTotalPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateAccount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateAccount whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateAccount withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AffiliateAccount withoutTrashed()
 *
 * @mixin \Eloquent
 */
class AffiliateAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'status', 'commission_rate',
        'clicks_count', 'signups_count', 'conversions_count',
        'total_earned', 'total_paid', 'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AffiliateStatus::class,
            'commission_rate' => 'decimal:2',
            'clicks_count' => 'integer',
            'signups_count' => 'integer',
            'conversions_count' => 'integer',
            'total_earned' => 'integer',
            'total_paid' => 'integer',
            'joined_at' => 'datetime',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(AffiliateClick::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class)->latest();
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class)->latest();
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    /** The shareable code is the owner's canonical referral code. */
    public function code(): string
    {
        return $this->user->referral_code;
    }

    public function shareUrl(): string
    {
        return route('referral.track', $this->code());
    }

    public function isActive(): bool
    {
        return $this->status === AffiliateStatus::Active;
    }

    /** Effective commission % — account override, else platform default. */
    public function effectiveRate(): float
    {
        return (float) ($this->commission_rate ?? settings('affiliate_commission', 5));
    }

    /** Lifetime earnings still awaiting payout (earned but not yet credited). */
    public function pendingEarnings(): int
    {
        return max(0, $this->total_earned - $this->total_paid);
    }

    public function conversionRate(): float
    {
        return $this->clicks_count > 0
            ? round($this->conversions_count / $this->clicks_count * 100, 1)
            : 0.0;
    }
}
