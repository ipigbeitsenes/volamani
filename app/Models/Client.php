<?php

namespace App\Models;

use App\Enums\ClientSource;
use App\Enums\ClientStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vendor_id', 'user_id', 'name', 'email', 'phone', 'company', 'address',
        'status', 'source', 'tags', 'about',
        'total_spent', 'orders_count', 'last_interaction_at', 'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'status'              => ClientStatus::class,
            'source'              => ClientSource::class,
            'tags'                => 'array',
            'total_spent'         => 'integer',
            'orders_count'        => 'integer',
            'last_interaction_at' => 'datetime',
            'last_synced_at'      => 'datetime',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(ClientInteraction::class)
            ->orderByDesc('pinned')
            ->orderByDesc('created_at');
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    public function initials(): string
    {
        return Str::of($this->name)->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn ($p) => Str::upper(Str::substr($p, 0, 1)))
            ->implode('');
    }

    public function isRegistered(): bool
    {
        return $this->user_id !== null;
    }
}
