<?php

namespace App\Models;

use App\Enums\ConsultationPackageType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $profile_id
 * @property string $name
 * @property string $description
 * @property ConsultationPackageType $type
 * @property int $duration_minutes
 * @property int $price
 * @property int|null $max_sessions_per_month
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $formatted_price
 * @property-read ConsultantProfile $profile
 * @property-read Collection<int, ConsultationSession> $sessions
 * @property-read int|null $sessions_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationPackage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationPackage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationPackage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationPackage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationPackage whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationPackage whereDurationMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationPackage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationPackage whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationPackage whereMaxSessionsPerMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationPackage whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationPackage wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationPackage whereProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationPackage whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationPackage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultationPackage whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ConsultationPackage extends Model
{
    protected $fillable = [
        'profile_id', 'name', 'description', 'type',
        'duration_minutes', 'price', 'max_sessions_per_month',
        'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => ConsultationPackageType::class,
            'is_active' => 'boolean',
            'price' => 'integer',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(ConsultantProfile::class, 'profile_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ConsultationSession::class, 'package_id');
    }

    public function durationLabel(): string
    {
        if ($this->duration_minutes < 60) {
            return $this->duration_minutes.' min';
        }
        $hours = intdiv($this->duration_minutes, 60);
        $mins = $this->duration_minutes % 60;

        return $mins > 0 ? "{$hours}h {$mins}min" : "{$hours} hour".($hours > 1 ? 's' : '');
    }

    public function getFormattedPriceAttribute(): string
    {
        return money($this->price);
    }
}
