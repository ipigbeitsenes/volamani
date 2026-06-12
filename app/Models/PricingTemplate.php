<?php

namespace App\Models;

use App\Enums\PricingCategory;
use App\Enums\PricingType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingTemplate extends Model
{
    protected $fillable = [
        'category', 'name', 'pricing_type', 'base_price', 'hourly_rate',
        'min_hours', 'max_hours', 'description', 'features', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'category'    => PricingCategory::class,
            'pricing_type' => PricingType::class,
            'features'    => 'array',
            'is_active'   => 'boolean',
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
