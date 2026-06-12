<?php

namespace App\Models;

use App\Enums\SecurityEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityLog extends Model
{
    /** Append-only: only created_at is tracked. */
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'event', 'ip_address', 'user_agent', 'description', 'metadata', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'event'      => SecurityEvent::class,
            'metadata'   => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $log) {
            $log->created_at ??= now();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
