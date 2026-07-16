<?php

namespace App\Models;

use App\Enums\BillingInterval;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $tagline
 * @property string|null $description
 * @property int $price
 * @property BillingInterval $billing_interval
 * @property numeric|null $commission_rate
 * @property int $trial_days
 * @property int|null $max_products
 * @property int|null $max_services
 * @property bool $featured_listing
 * @property array<array-key, mixed>|null $perks
 * @property bool $is_active
 * @property bool $is_popular
 * @property int $sort_order
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Subscription> $subscriptions
 * @property-read int|null $subscriptions_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereBillingInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereCommissionRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereFeaturedListing($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereIsPopular($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereMaxProducts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereMaxServices($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan wherePerks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereTagline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereTrialDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan withoutTrashed()
 *
 * @mixin \Eloquent
 */
class SubscriptionPlan extends Model
{
    use HasSlug, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'tagline', 'description', 'price', 'billing_interval',
        'commission_rate', 'trial_days', 'max_products', 'max_services',
        'featured_listing', 'perks', 'is_active', 'is_popular', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'billing_interval' => BillingInterval::class,
            'price' => 'integer',
            'commission_rate' => 'decimal:2',
            'trial_days' => 'integer',
            'max_products' => 'integer',
            'max_services' => 'integer',
            'featured_listing' => 'boolean',
            'perks' => 'array',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'sort_order' => 'integer',
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

        return money($this->price).$this->billing_interval->shortLabel();
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
