<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
