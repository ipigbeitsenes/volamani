<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $reviewable_type
 * @property int $reviewable_id
 * @property int $reviewer_id
 * @property int|null $order_id
 * @property int|null $service_order_id
 * @property int $rating
 * @property string|null $title
 * @property string|null $body
 * @property bool $is_approved
 * @property bool $is_verified_purchase
 * @property int $helpful_count
 * @property string|null $response
 * @property Carbon|null $responded_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Order|null $order
 * @property-read Model|\Eloquent $reviewable
 * @property-read User|null $reviewer
 * @property-read ServiceOrder|null $serviceOrder
 * @property-read Collection<int, ReviewVote> $votes
 * @property-read int|null $votes_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereHelpfulCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereIsApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereIsVerifiedPurchase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereRespondedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereReviewableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereReviewableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereReviewerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereServiceOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
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
            'is_approved' => 'boolean',
            'is_verified_purchase' => 'boolean',
            'rating' => 'integer',
            'helpful_count' => 'integer',
            'responded_at' => 'datetime',
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
        return ! empty($this->response);
    }

    public function votedHelpfulBy(?User $user): bool
    {
        return $user && $this->votes()->where('user_id', $user->id)->exists();
    }
}
