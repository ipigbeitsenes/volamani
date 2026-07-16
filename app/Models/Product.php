<?php

namespace App\Models;

use App\Enums\ProductKind;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Services\Reviews\ReviewEligibilityService;
use App\Traits\Auditable;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property int $vendor_id
 * @property ProductKind $kind
 * @property int|null $category_id
 * @property int|null $physical_category_id
 * @property string $name
 * @property string $slug
 * @property string|null $short_description
 * @property string|null $description
 * @property ProductType $type
 * @property int $price
 * @property int|null $compare_price
 * @property string|null $thumbnail
 * @property string|null $preview_url
 * @property bool $is_downloadable
 * @property int|null $download_limit
 * @property int $download_expiry_hours
 * @property ProductStatus $status
 * @property string $availability
 * @property bool $is_featured
 * @property Carbon|null $featured_until
 * @property int $sales_count
 * @property int $views_count
 * @property-read int|null $reviews_count
 * @property float $average_rating
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property Carbon|null $approved_at
 * @property int|null $approved_by
 * @property string|null $rejection_reason
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read User|null $approvedBy
 * @property-read ProductCategory|null $category
 * @property-read Collection<int, ProductDownload> $downloads
 * @property-read int|null $downloads_count
 * @property-read Collection<int, ProductFile> $files
 * @property-read int|null $files_count
 * @property-read Collection<int, ProductGallery> $gallery
 * @property-read int|null $gallery_count
 * @property-read string $thumbnail_url
 * @property-read Collection<int, OrderItem> $orderItems
 * @property-read int|null $order_items_count
 * @property-read PhysicalCategory|null $physicalCategory
 * @property-read ProductPhysicalDetail|null $physicalDetail
 * @property-read Collection<int, Review> $reviews
 * @property-read Collection<int, PhysicalCategory> $secondaryPhysicalCategories
 * @property-read int|null $secondary_physical_categories_count
 * @property-read Collection<int, ProductTag> $tags
 * @property-read int|null $tags_count
 * @property-read Collection<int, ProductVariant> $variants
 * @property-read int|null $variants_count
 * @property-read Vendor|null $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product digital()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product featured()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product ofType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product physical()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product search(string $term)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereAvailability($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereAverageRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereComparePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDownloadExpiryHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDownloadLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereFeaturedUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsDownloadable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereKind($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePhysicalCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePreviewUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereRejectionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereReviewsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSalesCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSeoDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSeoTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereShortDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereThumbnail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereViewsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Product extends Model
{
    use Auditable, HasSlug, SoftDeletes;

    protected $fillable = [
        'vendor_id', 'kind', 'category_id', 'physical_category_id', 'name', 'slug', 'short_description',
        'description', 'type', 'price', 'compare_price', 'thumbnail',
        'preview_url', 'is_downloadable', 'download_limit', 'download_expiry_hours',
        'status', 'availability', 'is_featured', 'featured_until', 'seo_title', 'seo_description',
        'approved_at', 'approved_by', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'kind' => ProductKind::class,
            'status' => ProductStatus::class,
            'type' => ProductType::class,
            'is_featured' => 'boolean',
            'is_downloadable' => 'boolean',
            'featured_until' => 'datetime',
            'approved_at' => 'datetime',
            'price' => 'integer',
            'compare_price' => 'integer',
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

    public function physicalCategory(): BelongsTo
    {
        return $this->belongsTo(PhysicalCategory::class, 'physical_category_id');
    }

    /** Secondary physical categories (the primary is physical_category_id). */
    public function secondaryPhysicalCategories(): BelongsToMany
    {
        return $this->belongsToMany(PhysicalCategory::class, 'physical_category_product');
    }

    public function physicalDetail(): HasOne
    {
        return $this->hasOne(ProductPhysicalDetail::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
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

    public function scopeDigital($query)
    {
        return $query->where('kind', ProductKind::Digital->value);
    }

    public function scopePhysical($query)
    {
        return $query->where('kind', ProductKind::Physical->value);
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

    /** A pre-sold product with no downloadable file yet — bought via deposit. */
    public function isComingSoon(): bool
    {
        return $this->availability === 'coming_soon';
    }

    /** Up-front deposit (kobo) required to reserve a coming-soon product. */
    public function depositAmount(): int
    {
        $percent = (int) settings('preorder_deposit_percent', 50);

        return (int) round($this->price * min(100, max(0, $percent)) / 100);
    }

    /** Balance (kobo) due when a reserved product is delivered. */
    public function balanceAmount(): int
    {
        return (int) $this->price - $this->depositAmount();
    }

    /** A currently-running paid promotion. */
    public function isPromoted(): bool
    {
        return $this->is_featured && $this->featured_until !== null && $this->featured_until->isFuture();
    }

    public function isPhysical(): bool
    {
        return $this->kind === ProductKind::Physical;
    }

    public function isDigital(): bool
    {
        return $this->kind !== ProductKind::Physical;
    }

    public function hasVariants(): bool
    {
        return $this->variants->where('is_active', true)->isNotEmpty();
    }

    /**
     * Units in stock for a physical product (sum of active variants, or the
     * detail row's quantity). Returns null for digital / untracked inventory.
     */
    public function stockQuantity(): ?int
    {
        if (! $this->isPhysical()) {
            return null;
        }

        if ($this->hasVariants()) {
            return (int) $this->variants->where('is_active', true)->sum('stock_quantity');
        }

        $detail = $this->physicalDetail;
        if (! $detail || ! $detail->track_inventory) {
            return null;
        }

        return (int) $detail->stock_quantity;
    }

    public function inStock(): bool
    {
        if (! $this->isPhysical()) {
            return true;
        }

        $detail = $this->physicalDetail;
        if ($detail && $detail->allow_backorder) {
            return true;
        }

        if ($this->hasVariants()) {
            return $this->variants->where('is_active', true)->where('stock_quantity', '>', 0)->isNotEmpty();
        }

        if (! $detail || ! $detail->track_inventory) {
            return true;
        }

        return $detail->stock_quantity > 0;
    }

    /** Whether this physical product can fulfil the requested quantity (variant or base stock). */
    public function canFulfilQuantity(int $qty, ?ProductVariant $variant = null): bool
    {
        if (! $this->isPhysical()) {
            return true;
        }

        $detail = $this->physicalDetail;
        if ($detail && $detail->allow_backorder) {
            return true;
        }

        if ($variant) {
            return $variant->stock_quantity >= $qty;
        }

        if (! $detail || ! $detail->track_inventory) {
            return true;
        }

        return $detail->stock_quantity >= $qty;
    }

    /** Lowest sellable price in kobo (cheapest variant override, else base price). */
    public function lowestPrice(): int
    {
        if ($this->hasVariants()) {
            return (int) $this->variants->where('is_active', true)
                ->map(fn ($v) => $v->effectivePrice())
                ->min();
        }

        return (int) $this->price;
    }

    /** The display category regardless of kind. */
    public function displayCategory(): ?string
    {
        return $this->isPhysical()
            ? $this->physicalCategory?->name
            : $this->category?->name;
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

        return OrderItem::whereHas('order', fn ($q) => $q->where('buyer_id', $user->id)
            ->where('payment_status', 'success')
        )
            ->where('product_id', $this->id)
            ->exists();
    }

    public function canBeReviewedBy(?User $user): bool
    {
        return $user && app(ReviewEligibilityService::class)->canReview($user, $this);
    }

    public function getThumbnailUrlAttribute(): string
    {
        return media_url($this->thumbnail, '/images/placeholder.svg');
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }
}
