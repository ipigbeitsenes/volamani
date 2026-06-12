<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Traits\Auditable;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes, HasSlug, Auditable;

    protected $fillable = [
        'vendor_id', 'category_id', 'name', 'slug', 'short_description',
        'description', 'type', 'price', 'compare_price', 'thumbnail',
        'preview_url', 'is_downloadable', 'download_limit', 'download_expiry_hours',
        'status', 'is_featured', 'seo_title', 'seo_description',
        'approved_at', 'approved_by', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status'         => ProductStatus::class,
            'type'           => ProductType::class,
            'is_featured'    => 'boolean',
            'is_downloadable'=> 'boolean',
            'approved_at'    => 'datetime',
            'price'          => 'integer',
            'compare_price'  => 'integer',
            'average_rating' => 'float',
        ];
    }

    public function getSlugSource(): string
    {
        return $this->name;
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

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ProductTag::class, 'product_tag', 'product_id', 'product_tag_id');
    }

    public function gallery(): HasMany
    {
        return $this->hasMany(ProductGallery::class)->orderBy('sort_order');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProductFile::class)->orderBy('sort_order');
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(ProductDownload::class);
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable')
            ->where('is_approved', true);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
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

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('short_description', 'like', "%{$term}%");
        });
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === ProductStatus::Active;
    }

    public function hasDiscount(): bool
    {
        return $this->compare_price && $this->compare_price > $this->price;
    }

    public function discountPercent(): int
    {
        if (! $this->hasDiscount()) {
            return 0;
        }
        return (int) round((($this->compare_price - $this->price) / $this->compare_price) * 100);
    }

    public function hasPurchased(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return OrderItem::whereHas('order', fn ($q) =>
                $q->where('buyer_id', $user->id)
                  ->where('payment_status', 'success')
            )
            ->where('product_id', $this->id)
            ->exists();
    }

    public function canBeReviewedBy(?User $user): bool
    {
        return $user && app(\App\Services\Reviews\ReviewEligibilityService::class)->canReview($user, $this);
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
