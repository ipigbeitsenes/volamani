<?php

namespace App\Models;

use App\Enums\PricingCategory;
use App\Enums\PricingType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property PricingCategory $category
 * @property string $name
 * @property PricingType $pricing_type
 * @property int $base_price
 * @property int $hourly_rate
 * @property int $min_hours
 * @property int $max_hours
 * @property string|null $description
 * @property array<array-key, mixed>|null $features
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, PricingEstimate> $estimates
 * @property-read int|null $estimates_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate whereBasePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate whereFeatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate whereHourlyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate whereMaxHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate whereMinHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate wherePricingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PricingTemplate extends Model
{
    protected $fillable = [
        'category', 'name', 'pricing_type', 'base_price', 'hourly_rate',
        'min_hours', 'max_hours', 'description', 'features', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'category' => PricingCategory::class,
            'pricing_type' => PricingType::class,
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function estimates(): HasMany
    {
        return $this->hasMany(PricingEstimate::class, 'template_id');
    }

    public function priceRange(): string
    {
        if ($this->pricing_type === PricingType::Hourly && $this->min_hours && $this->max_hours) {
            $min = money($this->hourly_rate * $this->min_hours);
            $max = money($this->hourly_rate * $this->max_hours);

            return "{$min} – {$max}";
        }

        return money($this->base_price);
    }
}
