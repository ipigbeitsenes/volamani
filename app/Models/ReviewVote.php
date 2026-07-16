<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $review_id
 * @property int $user_id
 * @property Carbon $created_at
 * @property-read Review $review
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewVote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewVote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewVote query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewVote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewVote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewVote whereReviewId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReviewVote whereUserId($value)
 *
 * @mixin \Eloquent
 */
class ReviewVote extends Model
{
    public $timestamps = false;

    protected $fillable = ['review_id', 'user_id', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
