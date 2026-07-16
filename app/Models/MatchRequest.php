<?php

namespace App\Models;

use App\Enums\MatchRequestStatus;
use App\Enums\MatchTargetType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $reference
 * @property int $user_id
 * @property MatchTargetType $looking_for
 * @property string $title
 * @property string $description
 * @property string|null $category
 * @property int|null $budget_min
 * @property int|null $budget_max
 * @property string|null $preferred_location
 * @property bool $remote_ok
 * @property array<array-key, mixed>|null $skills
 * @property string|null $timeline
 * @property MatchRequestStatus $status
 * @property-read int|null $matches_count
 * @property Carbon|null $expires_at
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, BusinessMatch> $matches
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchRequest onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchRequest whereBudgetMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchRequest whereBudgetMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchRequest whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchRequest whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchRequest whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchRequest whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchRequest whereLookingFor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchRequest whereMatchesCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchRequest wherePreferredLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchRequest whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchRequest whereRemoteOk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchRequest whereSkills($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchRequest whereTimeline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchRequest whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchRequest whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchRequest withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MatchRequest withoutTrashed()
 *
 * @mixin \Eloquent
 */
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
