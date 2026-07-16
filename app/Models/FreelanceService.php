<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Services\Reviews\ReviewEligibilityService;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $vendor_id
 * @property int|null $category_id
 * @property string $title
 * @property string $slug
 * @property string|null $short_description
 * @property string $description
 * @property string|null $thumbnail
 * @property ProductStatus $status
 * @property bool $is_featured
 * @property int $views_count
 * @property-read int|null $orders_count
 * @property-read int|null $reviews_count
 * @property numeric $average_rating
 * @property Carbon|null $approved_at
 * @property int|null $approved_by
 * @property string|null $rejection_reason
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $approvedBy
 * @property-read ProductCategory|null $category
 * @property-read Collection<int, ServiceFaq> $faqs
 * @property-read int|null $faqs_count
 * @property-read string $thumbnail_url
 * @property-read Collection<int, ServiceOrder> $orders
 * @property-read Collection<int, ServicePackage> $packages
 * @property-read int|null $packages_count
 * @property-read Collection<int, Review> $reviews
 * @property-read Vendor|null $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService featured()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService search(string $term)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService whereAverageRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService whereOrdersCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService whereRejectionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService whereReviewsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService whereSeoDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService whereSeoTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService whereShortDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService whereThumbnail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService whereViewsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreelanceService withoutTrashed()
 *
 * @mixin \Eloquent
 */
class FreelanceService extends Model
{
    use HasSlug, SoftDeletes;

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
            'status' => ProductStatus::class,
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

    public function canBeReviewedBy(?User $user): bool
    {
        return $user && app(ReviewEligibilityService::class)->canReview($user, $this);
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
        return media_url($this->thumbnail, '/images/placeholder.svg');
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }
}
