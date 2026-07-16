<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTag query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTag whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTag whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTag whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ProductTag extends Model
{
    use HasSlug;

    protected $fillable = ['name', 'slug'];

    public function getSlugSource(): string
    {
        return $this->name;
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_tag', 'product_tag_id', 'product_id');
    }
}
