<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Review extends Model
{
    protected $fillable = [
        'reviewable_type', 'reviewable_id', 'reviewer_id',
        'order_id', 'service_order_id',
        'rating', 'title', 'body', 'is_approved',
        'is_verified_purchase', 'helpful_count', 'response', 'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'is_approved'          => 'boolean',
            'is_verified_purchase' => 'boolean',
            'rating'               => 'integer',
            'helpful_count'        => 'integer',
            'responded_at'         => 'datetime',
        ];
    }

    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ReviewVote::class);
    }

    // ─── Scopes & helpers ───────────────────────────────────────────────────────

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function hasResponse(): bool
    {
        return !empty($this->response);
    }

    public function votedHelpfulBy(?User $user): bool
    {
        return $user && $this->votes()->where('user_id', $user->id)->exists();
    }
}
