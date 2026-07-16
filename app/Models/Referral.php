<?php

namespace App\Models;

use App\Enums\ReferralStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $affiliate_account_id
 * @property int $referrer_id
 * @property int $referred_user_id
 * @property ReferralStatus $status
 * @property int $signup_reward
 * @property Carbon|null $qualified_at
 * @property Carbon|null $rewarded_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read AffiliateAccount|null $account
 * @property-read User|null $referredUser
 * @property-read User|null $referrer
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral whereAffiliateAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral whereQualifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral whereReferredUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral whereReferrerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral whereRewardedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral whereSignupReward($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Referral whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Referral extends Model
{
    protected $fillable = [
        'affiliate_account_id', 'referrer_id', 'referred_user_id',
        'status', 'signup_reward', 'qualified_at', 'rewarded_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReferralStatus::class,
            'signup_reward' => 'integer',
            'qualified_at' => 'datetime',
            'rewarded_at' => 'datetime',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function account(): BelongsTo
    {
        return $this->belongsTo(AffiliateAccount::class, 'affiliate_account_id');
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    // ─── State helpers ──────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === ReferralStatus::Pending;
    }

    public function isRewarded(): bool
    {
        return $this->status === ReferralStatus::Rewarded;
    }
}
