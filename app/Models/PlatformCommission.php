<?php

namespace App\Models;

use App\Enums\PlatformCommissionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * The platform's commission on a single order — a queryable ledger of what the
 * marketplace is owed and whether it has been collected. Not to be confused with
 * AffiliateCommission (referral earnings paid OUT to affiliates).
 *
 * @property int $id
 * @property int $order_id
 * @property int|null $vendor_id
 * @property int $amount
 * @property string $currency
 * @property PlatformCommissionStatus $status
 * @property string|null $method
 * @property string|null $reason
 * @property Carbon|null $settled_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Order|null $order
 * @property-read Vendor|null $vendor
 *
 * @method static Builder<static>|PlatformCommission newModelQuery()
 * @method static Builder<static>|PlatformCommission newQuery()
 * @method static Builder<static>|PlatformCommission outstanding()
 * @method static Builder<static>|PlatformCommission query()
 * @method static Builder<static>|PlatformCommission whereAmount($value)
 * @method static Builder<static>|PlatformCommission whereCreatedAt($value)
 * @method static Builder<static>|PlatformCommission whereCurrency($value)
 * @method static Builder<static>|PlatformCommission whereId($value)
 * @method static Builder<static>|PlatformCommission whereMethod($value)
 * @method static Builder<static>|PlatformCommission whereOrderId($value)
 * @method static Builder<static>|PlatformCommission whereReason($value)
 * @method static Builder<static>|PlatformCommission whereSettledAt($value)
 * @method static Builder<static>|PlatformCommission whereStatus($value)
 * @method static Builder<static>|PlatformCommission whereUpdatedAt($value)
 * @method static Builder<static>|PlatformCommission whereVendorId($value)
 *
 * @mixin \Eloquent
 */
class PlatformCommission extends Model
{
    protected $fillable = [
        'order_id', 'vendor_id', 'amount', 'currency', 'status', 'method', 'reason', 'settled_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'status' => PlatformCommissionStatus::class,
            'settled_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /** Entries still representing money the platform expects to collect. */
    public function scopeOutstanding(Builder $query): Builder
    {
        return $query->where('status', PlatformCommissionStatus::Owed->value);
    }
}
