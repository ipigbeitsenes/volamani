<?php

namespace App\Models;

use App\Enums\AffiliateStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
            'status'            => AffiliateStatus::class,
            'commission_rate'   => 'decimal:2',
            'clicks_count'      => 'integer',
            'signups_count'     => 'integer',
            'conversions_count' => 'integer',
            'total_earned'      => 'integer',
            'total_paid'        => 'integer',
            'joined_at'         => 'datetime',
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
