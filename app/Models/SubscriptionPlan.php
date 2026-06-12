<?php

namespace App\Models;

use App\Enums\BillingInterval;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use SoftDeletes, HasSlug;

    protected $fillable = [
        'name', 'slug', 'tagline', 'description', 'price', 'billing_interval',
        'commission_rate', 'trial_days', 'max_products', 'max_services',
        'featured_listing', 'perks', 'is_active', 'is_popular', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'billing_interval' => BillingInterval::class,
            'price'            => 'integer',
            'commission_rate'  => 'decimal:2',
            'trial_days'       => 'integer',
            'max_products'     => 'integer',
            'max_services'     => 'integer',
            'featured_listing' => 'boolean',
            'perks'            => 'array',
            'is_active'        => 'boolean',
            'is_popular'       => 'boolean',
            'sort_order'       => 'integer',
        ];
    }

    // ─── HasSlug requirement ──────────────────────────────────────────────────

    public function getSlugSource(): string
    {
        return $this->name;
    }

    public function getSlugSourceColumn(): string
    {
        return 'name';
    }

    public function subscriptions(): HasMany
    {
        // subscriptions table uses plan_id (not the conventional subscription_plan_id)
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    public function isFree(): bool
    {
        return $this->price <= 0;
    }

    public function hasTrial(): bool
    {
        return $this->trial_days > 0;
    }

    public function priceLabel(): string
    {
        if ($this->isFree()) {
            return 'Free';
        }

        return money($this->price) . $this->billing_interval->shortLabel();
    }

    public function productLimitLabel(): string
    {
        return $this->max_products === null ? 'Unlimited' : (string) $this->max_products;
    }

    public function serviceLimitLabel(): string
    {
        return $this->max_services === null ? 'Unlimited' : (string) $this->max_services;
    }
}
