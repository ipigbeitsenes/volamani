<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
