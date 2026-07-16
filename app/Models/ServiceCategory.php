<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $parent_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $image
 * @property string|null $icon
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, ServiceCategory> $children
 * @property-read int|null $children_count
 * @property-read string $image_url
 * @property-read ServiceCategory|null $parent
 * @property-read Collection<int, FreelanceService> $services
 * @property-read int|null $services_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategory active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategory roots()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategory whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategory whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategory whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategory whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategory whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategory whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategory whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategory whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ServiceCategory extends Model
{
    use HasSlug;

    protected $fillable = [
        'parent_id', 'name', 'slug', 'description',
        'image', 'icon', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function getSlugSource(): string
    {
        return $this->name;
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ServiceCategory::class, 'parent_id');
    }

    /** Freelance services that list this as a SECONDARY category. */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(FreelanceService::class, 'service_category_freelance_service');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function getImageUrlAttribute(): string
    {
        return media_url($this->image, '');
    }
}
