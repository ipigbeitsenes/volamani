<?php

namespace App\Models;

use App\Enums\Status;
use App\Enums\StoreFocus;
use App\Enums\StoreType;
use App\Enums\TrustTier;
use App\Traits\Auditable;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property int $user_id
 * @property string $business_name
 * @property string $slug
 * @property string|null $tagline
 * @property string|null $description
 * @property string|null $logo
 * @property string|null $banner
 * @property string|null $whatsapp
 * @property string|null $website
 * @property array<array-key, mixed>|null $social_links
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string|null $category
 * @property StoreType $store_type
 * @property StoreFocus $store_focus
 * @property string|null $currency
 * @property Status $status
 * @property bool $is_featured
 * @property int $views_count
 * @property Carbon|null $approved_at
 * @property int|null $approved_by
 * @property string|null $rejection_reason
 * @property Carbon|null $verified_at
 * @property float $average_rating
 * @property int $reviews_count
 * @property-read int|null $followers_count
 * @property int $trust_score
 * @property Collection<int, VendorStrike> $strikes
 * @property Carbon|null $strikes_updated_at
 * @property bool $suspended_for_strikes
 * @property int|null $commission_rate
 * @property string $plan
 * @property int $shipping_fee
 * @property int|null $free_shipping_threshold
 * @property string|null $ships_to
 * @property array<array-key, mixed>|null $no_delivery_states
 * @property array<array-key, mixed>|null $no_delivery_cities
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read User|null $approvedBy
 * @property-read Collection<int, BusinessMatch> $businessMatches
 * @property-read int|null $business_matches_count
 * @property-read Collection<int, CategoryRequest> $categoryRequests
 * @property-read int|null $category_requests_count
 * @property-read Collection<int, Client> $clients
 * @property-read int|null $clients_count
 * @property-read ConsultantProfile|null $consultantProfile
 * @property-read Collection<int, Document> $documents
 * @property-read int|null $documents_count
 * @property-read Collection<int, User> $followers
 * @property-read Collection<int, Follow> $follows
 * @property-read int|null $follows_count
 * @property-read string $banner_url
 * @property-read string $logo_url
 * @property-read string $storefront_url
 * @property-read MatchingProfile|null $matchingProfile
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 * @property-read Collection<int, FreelanceService> $services
 * @property-read int|null $services_count
 * @property-read int|null $strikes_count
 * @property-read Collection<int, Subscription> $subscriptions
 * @property-read int|null $subscriptions_count
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereAverageRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereBanner($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereBusinessName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereCommissionRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereFollowersCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereFreeShippingThreshold($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereNoDeliveryCities($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereNoDeliveryStates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor wherePlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereRejectionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereReviewsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereShippingFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereShipsTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereSocialLinks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereStoreFocus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereStoreType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereStrikes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereStrikesUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereSuspendedForStrikes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereTagline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereTrustScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereViewsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereWebsite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereWhatsapp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Vendor extends Model
{
    use Auditable, HasSlug, SoftDeletes;

    protected $fillable = [
        'user_id',
        'business_name',
        'slug',
        'tagline',
        'description',
        'logo',
        'banner',
        'whatsapp',
        'website',
        'social_links',
        'address',
        'city',
        'state',
        'category',
        'store_type',
        'store_focus',
        'currency',
        'status',
        'is_featured',
        'views_count',
        'approved_at',
        'approved_by',
        'rejection_reason',
        'verified_at',
        'commission_rate',
        'plan',
        'shipping_fee',
        'free_shipping_threshold',
        'ships_to',
        'no_delivery_states',
        'no_delivery_cities',
        'average_rating',
        'reviews_count',
        'trust_score',
        'strikes',
        'strikes_updated_at',
        'suspended_for_strikes',
        'followers_count',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'is_featured' => 'boolean',
            'approved_at' => 'datetime',
            'verified_at' => 'datetime',
            'status' => Status::class,
            'store_type' => StoreType::class,
            'store_focus' => StoreFocus::class,
            'shipping_fee' => 'integer',
            'free_shipping_threshold' => 'integer',
            'no_delivery_states' => 'array',
            'no_delivery_cities' => 'array',
            'average_rating' => 'float',
            'trust_score' => 'integer',
            'strikes' => 'integer',
            'strikes_updated_at' => 'datetime',
            'suspended_for_strikes' => 'boolean',
            'followers_count' => 'integer',
        ];
    }

    // ─── HasSlug requirement ──────────────────────────────────────────────────

    public function getSlugSource(): string
    {
        return $this->business_name;
    }

    public function getSlugSourceColumn(): string
    {
        return 'business_name';
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function strikes(): HasMany
    {
        return $this->hasMany(VendorStrike::class)->latest();
    }

    public function activeStrikeCount(): int
    {
        return $this->strikes()->active()->count();
    }

    /** Live products + services (anything not rejected/archived) — for tier listing caps. */
    public function activeListingCount(): int
    {
        $exclude = ['rejected', 'archived'];

        return $this->products()->whereNotIn('status', $exclude)->count()
            + $this->services()->whereNotIn('status', $exclude)->count();
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(FreelanceService::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class)->latest();
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class)->latest();
    }

    public function matchingProfile(): HasOne
    {
        return $this->hasOne(MatchingProfile::class);
    }

    public function consultantProfile(): HasOne
    {
        return $this->hasOne(ConsultantProfile::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function businessMatches(): HasMany
    {
        return $this->hasMany(BusinessMatch::class);
    }

    public function follows(): HasMany
    {
        return $this->hasMany(Follow::class);
    }

    public function categoryRequests(): HasMany
    {
        return $this->hasMany(CategoryRequest::class)->latest();
    }

    /** Users who follow this store — recipients for new-listing announcements. */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'vendor_id', 'follower_id')->withTimestamps();
    }

    /**
     * Approved reviews across everything this vendor owns (products, services,
     * consultant profiles). Returns a query builder — the reviews table is
     * polymorphic, so there is no direct vendor_id foreign key.
     */
    public function reviews()
    {
        $map = [
            Product::class => $this->products()->pluck('id')->all(),
            FreelanceService::class => $this->services()->pluck('id')->all(),
            ConsultantProfile::class => ConsultantProfile::where('vendor_id', $this->id)->pluck('id')->all(),
        ];

        return Review::query()
            ->where('is_approved', true)
            ->where(function ($q) use ($map) {
                $q->whereRaw('1 = 0');
                foreach ($map as $type => $ids) {
                    if (! empty($ids)) {
                        $q->orWhere(fn ($qq) => $qq->where('reviewable_type', $type)->whereIn('reviewable_id', $ids));
                    }
                }
            });
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /** The currency this vendor prices their listings in (falls back to base). */
    public function currencyCode(): string
    {
        return $this->currency ?: currency()->base();
    }

    public function isActive(): bool
    {
        return $this->status === Status::Active;
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function sellsPhysical(): bool
    {
        return (bool) $this->store_focus?->sellsPhysical();
    }

    public function sellsDigital(): bool
    {
        return (bool) $this->store_focus?->sellsDigital();
    }

    public function sellsServices(): bool
    {
        return (bool) $this->store_focus?->sellsServices();
    }

    /**
     * Whether this vendor delivers to the given state / city. A vendor can
     * exclude states and/or cities they cannot ship to. Matching is
     * case-insensitive and trims whitespace; empty exclusion lists = ships
     * everywhere.
     */
    public function deliversTo(?string $state, ?string $city = null): bool
    {
        $norm = fn (?string $v) => mb_strtolower(trim((string) $v));

        $blockedStates = array_map($norm, $this->no_delivery_states ?? []);
        if ($state && in_array($norm($state), $blockedStates, true)) {
            return false;
        }

        $blockedCities = array_map($norm, $this->no_delivery_cities ?? []);
        if ($city && in_array($norm($city), $blockedCities, true)) {
            return false;
        }

        return true;
    }

    /** Flat shipping fee (kobo) for an order of the given item subtotal — 0 if the free-shipping threshold is met. */
    public function shippingFeeFor(int $subtotalKobo): int
    {
        if ($this->free_shipping_threshold !== null && $subtotalKobo >= $this->free_shipping_threshold) {
            return 0;
        }

        return (int) ($this->shipping_fee ?? 0);
    }

    public function getLogoUrlAttribute(): string
    {
        return media_url($this->logo)
            ?? 'https://ui-avatars.com/api/?name='.urlencode($this->business_name).'&size=80&background=1a56db&color=fff';
    }

    public function getBannerUrlAttribute(): string
    {
        return media_url($this->banner, '');
    }

    public function getStorefrontUrlAttribute(): string
    {
        return route('storefront.show', $this->user->username ?? $this->slug);
    }

    /** The vendor's currently active subscription, if any. */
    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->whereIn('status', ['trialing', 'active', 'past_due', 'cancelled'])
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->with('plan')
            ->first();
    }

    public function currentPlan(): ?SubscriptionPlan
    {
        return $this->activeSubscription()?->plan;
    }

    public function getEffectiveCommissionRate(): int
    {
        // An active paid plan can override the per-vendor / platform commission.
        $planRate = $this->currentPlan()?->commission_rate;
        if ($planRate !== null) {
            return (int) round((float) $planRate);
        }

        return $this->commission_rate ?? (int) settings('platform_commission', 10);
    }

    public function productLimit(): ?int
    {
        return $this->currentPlan()?->max_products;
    }

    public function canAddProduct(): bool
    {
        $limit = $this->productLimit();

        return $limit === null || $this->products()->count() < $limit;
    }

    public function serviceLimit(): ?int
    {
        return $this->currentPlan()?->max_services;
    }

    public function canAddService(): bool
    {
        $limit = $this->serviceLimit();

        return $limit === null || $this->services()->count() < $limit;
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function averageRating(): float
    {
        return (float) $this->average_rating;
    }

    public function totalReviews(): int
    {
        return (int) $this->reviews_count;
    }

    public function trustTier(): TrustTier
    {
        return TrustTier::fromScore((int) $this->trust_score);
    }
}
