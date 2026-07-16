<?php

namespace App\Models;

use App\Enums\SecurityEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property SecurityEvent $event
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $description
 * @property array<array-key, mixed>|null $metadata
 * @property Carbon|null $created_at
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityLog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityLog whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityLog whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityLog whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecurityLog whereUserId($value)
 *
 * @mixin \Eloquent
 */
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
            'event' => SecurityEvent::class,
            'metadata' => 'array',
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
