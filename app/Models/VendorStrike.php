<?php

namespace App\Models;

use App\Enums\StrikeReason;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property int $vendor_id
 * @property StrikeReason $reason
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
 * @property-read Vendor|null $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorStrike active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorStrike newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorStrike newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorStrike query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorStrike whereClearedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorStrike whereClearedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorStrike whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorStrike whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorStrike whereIssuedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorStrike whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorStrike whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorStrike whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorStrike whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorStrike whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorStrike whereVendorId($value)
 *
 * @mixin \Eloquent
 */
class VendorStrike extends Model
{
    use Auditable;

    protected $fillable = [
        'vendor_id', 'reason', 'source', 'source_id', 'note',
        'issued_by', 'cleared_by', 'cleared_at',
    ];

    protected function casts(): array
    {
        return [
            'reason' => StrikeReason::class,
            'cleared_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
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
