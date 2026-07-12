<?php

namespace App\Models;

use App\Enums\ReferralStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
