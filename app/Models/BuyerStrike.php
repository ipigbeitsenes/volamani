<?php

namespace App\Models;

use App\Enums\BuyerStrikeReason;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * A single abuse strike against a buyer. Mirrors VendorStrike. Active strikes
 * (cleared_at = null) drive the auto-flag / purchase-suspend thresholds.
 *
 * @property int $id
 * @property int $user_id
 * @property BuyerStrikeReason $reason
 * @property string $source
 * @property int|null $source_id
 * @property string|null $note
 * @property int|null $issued_by
 * @property int|null $cleared_by
 * @property Carbon|null $cleared_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read User|null $clearedBy
 * @property-read User|null $issuedBy
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerStrike active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerStrike newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerStrike newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerStrike query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerStrike whereClearedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerStrike whereClearedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerStrike whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerStrike whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerStrike whereIssuedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerStrike whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerStrike whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerStrike whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerStrike whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerStrike whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerStrike whereUserId($value)
 *
 * @mixin \Eloquent
 */
class BuyerStrike extends Model
{
    use Auditable;

    protected $fillable = [
        'user_id', 'reason', 'source', 'source_id', 'note',
        'issued_by', 'cleared_by', 'cleared_at',
    ];

    protected function casts(): array
    {
        return [
            'reason' => BuyerStrikeReason::class,
            'cleared_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function clearedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cleared_by');
    }

    public function isActive(): bool
    {
        return $this->cleared_at === null;
    }

    public function scopeActive($query)
    {
        return $query->whereNull('cleared_at');
    }
}
