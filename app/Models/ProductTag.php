<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
