<?php

namespace App\Models;

use App\Enums\PackageTier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServicePackage extends Model
{
    protected $fillable = [
        'service_id', 'tier', 'name', 'description',
        'price', 'delivery_days', 'revisions', 'features', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tier'      => PackageTier::class,
            'features'  => 'array',
            'is_active' => 'boolean',
            'price'     => 'integer',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(FreelanceService::class, 'service_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'package_id');
    }

    public function getFormattedPriceAttribute(): string
    {
        return money($this->price);
    }

    public function hasUnlimitedRevisions(): bool
    {
        return $this->revisions >= 255;
    }

    public function revisionsLabel(): string
    {
        return $this->hasUnlimitedRevisions() ? 'Unlimited' : (string) $this->revisions;
    }
}
