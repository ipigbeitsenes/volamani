<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\Auditable;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use SoftDeletes, HasSlug, Auditable;

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
        'average_rating',
        'reviews_count',
        'trust_score',
        'followers_count',
    ];

    protected function casts(): array
    {
        return [
            'social_links'   => 'array',
            'is_featured'    => 'boolean',
            'approved_at'    => 'datetime',
            'verified_at'    => 'datetime',
            'status'         => Status::class,
            'store_type'     => \App\Enums\StoreType::class,
            'store_focus'    => \App\Enums\StoreFocus::class,
            'shipping_fee'   => 'integer',
            'free_shipping_threshold' => 'integer',
            'average_rating' => 'float',
            'trust_score'    => 'integer',
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

    public function matchingProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MatchingProfile::class);
    }

    public function consultantProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
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
    public function followers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
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
            Product::class           => $this->products()->pluck('id')->all(),
            FreelanceService::class  => $this->services()->pluck('id')->all(),
            ConsultantProfile::class => ConsultantProfile::where('vendor_id', $this->id)->pluck('id')->all(),
        ];

        return Review::query()
            ->where('is_approved', true)
            ->where(function ($q) use ($map) {
                $q->whereRaw('1 = 0');
                foreach ($map as $type => $ids) {
                    if (!empty($ids)) {
                        $q->orWhere(fn ($qq) => $qq->where('reviewable_type', $type)->whereIn('reviewable_id', $ids));
                    }
                }
            });
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

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
            ?? 'https://ui-avatars.com/api/?name=' . urlencode($this->business_name) . '&size=80&background=1a56db&color=fff';
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

    public function trustTier(): \App\Enums\TrustTier
    {
        return \App\Enums\TrustTier::fromScore((int) $this->trust_score);
    }
}
