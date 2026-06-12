<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FreelanceService extends Model
{
    use SoftDeletes, HasSlug;

    protected $table = 'freelance_services';

    protected $fillable = [
        'vendor_id', 'category_id', 'title', 'slug', 'short_description',
        'description', 'thumbnail', 'status', 'is_featured',
        'views_count', 'orders_count', 'reviews_count', 'average_rating',
        'approved_at', 'approved_by', 'rejection_reason',
        'seo_title', 'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'status'      => ProductStatus::class,
            'is_featured' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function getSlugSource(): string
    {
        return $this->title;
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(ServicePackage::class, 'service_id')
            ->where('is_active', true)
            ->orderByRaw("FIELD(tier, 'basic', 'standard', 'premium')");
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(ServiceFaq::class, 'service_id')->orderBy('sort_order');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'service_id');
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable')
            ->where('is_approved', true);
    }

    public function canBeReviewedBy(?\App\Models\User $user): bool
    {
        return $user && app(\App\Services\Reviews\ReviewEligibilityService::class)->canReview($user, $this);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', ProductStatus::Active);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('short_description', 'like', "%{$term}%");
        });
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === ProductStatus::Active;
    }

    public function lowestPrice(): int
    {
        return $this->packages()->min('price') ?? 0;
    }

    public function getThumbnailUrlAttribute(): string
    {
        return $this->thumbnail
            ? asset('storage/' . $this->thumbnail)
            : asset('images/placeholder.svg');
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }
}
