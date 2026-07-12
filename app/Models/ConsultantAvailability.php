<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
