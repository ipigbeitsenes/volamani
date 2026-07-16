<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property string $reference
 * @property int $wallet_id
 * @property int $vendor_id
 * @property int|null $escrow_id
 * @property int $amount
 * @property string $status
 * @property Carbon $release_at
 * @property Carbon|null $released_at
 * @property Carbon|null $clawed_back_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Escrow|null $escrow
 * @property-read Vendor|null $vendor
 * @property-read Wallet $wallet
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletReserve dueForRelease()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletReserve held()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletReserve newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletReserve newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletReserve query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletReserve whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletReserve whereClawedBackAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletReserve whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletReserve whereEscrowId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletReserve whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletReserve whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletReserve whereReleaseAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletReserve whereReleasedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletReserve whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletReserve whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletReserve whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletReserve whereWalletId($value)
 *
 * @mixin \Eloquent
 */
class WalletReserve extends Model
{
    use Auditable;

    protected $fillable = [
        'reference', 'wallet_id', 'vendor_id', 'escrow_id',
        'amount', 'status', 'release_at', 'released_at', 'clawed_back_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'release_at' => 'datetime',
            'released_at' => 'datetime',
            'clawed_back_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (WalletReserve $r) {
            if (empty($r->reference)) {
                $r->reference = generate_reference('RSV');
            }
        });
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function escrow(): BelongsTo
    {
        return $this->belongsTo(Escrow::class);
    }

    public function isHeld(): bool
    {
        return $this->status === 'held';
    }

    public function scopeHeld($query)
    {
        return $query->where('status', 'held');
    }

    /** Held reserves whose window has elapsed and are due for payout. */
    public function scopeDueForRelease($query)
    {
        return $query->where('status', 'held')->where('release_at', '<=', now());
    }
}
