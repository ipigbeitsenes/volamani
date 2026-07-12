<?php

namespace App\Models;

use App\Enums\ConsultationPackageType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
