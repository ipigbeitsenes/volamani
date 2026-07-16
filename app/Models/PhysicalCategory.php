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
 * @property-read Collection<int, PhysicalCategory> $children
 * @property-read int|null $children_count
 * @property-read string $image_url
 * @property-read PhysicalCategory|null $parent
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PhysicalCategory active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PhysicalCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PhysicalCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PhysicalCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PhysicalCategory roots()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PhysicalCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PhysicalCategory whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PhysicalCategory whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PhysicalCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PhysicalCategory whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PhysicalCategory whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PhysicalCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PhysicalCategory whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PhysicalCategory whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PhysicalCategory whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PhysicalCategory whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PhysicalCategory extends Model
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
        return $this->belongsTo(PhysicalCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(PhysicalCategory::class, 'parent_id');
    }

    /** Products that list this as a SECONDARY category. */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'physical_category_product');
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
