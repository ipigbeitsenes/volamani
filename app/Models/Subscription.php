<?php

namespace App\Models;

use App\Enums\BillingInterval;
use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference', 'vendor_id', 'user_id', 'plan_id', 'price', 'billing_interval',
        'status', 'auto_renew', 'trial_ends_at', 'starts_at', 'ends_at',
        'last_payment_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status'           => SubscriptionStatus::class,
            'billing_interval' => BillingInterval::class,
            'price'            => 'integer',
            'auto_renew'       => 'boolean',
            'trial_ends_at'    => 'datetime',
            'starts_at'        => 'datetime',
            'ends_at'          => 'datetime',
            'last_payment_at'  => 'datetime',
            'cancelled_at'     => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Subscription $subscription) {
            if (empty($subscription->reference)) {
                $subscription->reference = generate_reference('SUB');
            }
        });
    }

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class)->latest();
    }

    // ─── State helpers ──────────────────────────────────────────────────────────

    /** Currently entitled to plan benefits. */
    public function isActive(): bool
    {
        return $this->status->grantsAccess()
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }

    public function onTrial(): bool
    {
        return $this->status === SubscriptionStatus::Trialing
            && $this->trial_ends_at !== null
            && $this->trial_ends_at->isFuture();
    }

    public function isCancelled(): bool
    {
        return $this->status === SubscriptionStatus::Cancelled || $this->cancelled_at !== null;
    }

    /** Due to bill: recurring, renewing, and past its current period. */
    public function isDueForRenewal(): bool
    {
        return $this->auto_renew
            && $this->billing_interval->isRecurring()
            && in_array($this->status, [SubscriptionStatus::Active, SubscriptionStatus::Trialing, SubscriptionStatus::PastDue])
            && $this->ends_at !== null
            && ! $this->ends_at->isFuture();
    }

    public function daysRemaining(): ?int
    {
        return $this->ends_at ? max(0, (int) now()->diffInDays($this->ends_at, false)) : null;
    }
}
