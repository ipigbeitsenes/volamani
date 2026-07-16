<?php

namespace App\Models;

use App\Enums\PackageTier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $service_id
 * @property PackageTier $tier
 * @property string $name
 * @property string $description
 * @property int $price
 * @property int $delivery_days
 * @property int $revisions
 * @property array<array-key, mixed>|null $features
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $formatted_price
 * @property-read Collection<int, ServiceOrder> $orders
 * @property-read int|null $orders_count
 * @property-read FreelanceService|null $service
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePackage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePackage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePackage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePackage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePackage whereDeliveryDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePackage whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePackage whereFeatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePackage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePackage whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePackage whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePackage wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePackage whereRevisions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePackage whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePackage whereTier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePackage whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ServicePackage extends Model
{
    protected $fillable = [
        'service_id', 'tier', 'name', 'description',
        'price', 'delivery_days', 'revisions', 'features', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tier' => PackageTier::class,
            'features' => 'array',
            'is_active' => 'boolean',
            'price' => 'integer',
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
