<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $profile_id
 * @property int $day_of_week
 * @property string $start_time
 * @property string $end_time
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $day_name
 * @property-read string $time_range
 * @property-read ConsultantProfile $profile
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantAvailability newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantAvailability newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantAvailability query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantAvailability whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantAvailability whereDayOfWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantAvailability whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantAvailability whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantAvailability whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantAvailability whereProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantAvailability whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsultantAvailability whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ConsultantAvailability extends Model
{
    protected $table = 'consultant_availability';

    protected $fillable = [
        'profile_id', 'day_of_week', 'start_time', 'end_time', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(ConsultantProfile::class, 'profile_id');
    }

    public function getDayNameAttribute(): string
    {
        return ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'][$this->day_of_week] ?? '—';
    }

    public function getTimeRangeAttribute(): string
    {
        return substr($this->start_time, 0, 5).' – '.substr($this->end_time, 0, 5);
    }
}
