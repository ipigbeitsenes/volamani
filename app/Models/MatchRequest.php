<?php

namespace App\Models;

use App\Enums\MatchRequestStatus;
use App\Enums\MatchTargetType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MatchRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference', 'user_id', 'looking_for', 'title', 'description', 'category',
        'budget_min', 'budget_max', 'preferred_location', 'remote_ok',
        'skills', 'timeline', 'status', 'matches_count', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'looking_for' => MatchTargetType::class,
            'status' => MatchRequestStatus::class,
            'budget_min' => 'integer',
            'budget_max' => 'integer',
            'remote_ok' => 'boolean',
            'skills' => 'array',
            'matches_count' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (MatchRequest $request) {
            if (empty($request->reference)) {
                $request->reference = generate_reference('MTR');
            }
        });
    }

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(BusinessMatch::class)->orderByDesc('score');
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    public function isOpen(): bool
    {
        return $this->status->isOpen();
    }

    public function budgetLabel(): string
    {
        return match (true) {
            $this->budget_min && $this->budget_max => money($this->budget_min).' – '.money($this->budget_max),
            (bool) $this->budget_max => 'Up to '.money($this->budget_max),
            (bool) $this->budget_min => 'From '.money($this->budget_min),
            default => 'Flexible',
        };
    }
}
