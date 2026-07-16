<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $category
 * @property string $name
 * @property string|null $description
 * @property int $price
 * @property bool $is_percentage
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingAddOn newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingAddOn newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingAddOn query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingAddOn whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingAddOn whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingAddOn whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingAddOn whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingAddOn whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingAddOn whereIsPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingAddOn whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingAddOn wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingAddOn whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingAddOn whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PricingAddOn extends Model
{
    protected $fillable = [
        'category', 'name', 'description', 'price', 'is_percentage', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_percentage' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function displayPrice(): string
    {
        return $this->is_percentage
            ? ($this->price / 100).'%'
            : money($this->price);
    }

    public function calculateFor(int $baseKobo): int
    {
        if ($this->is_percentage) {
            return (int) round($baseKobo * ($this->price / 10000));
        }

        return $this->price;
    }
}
