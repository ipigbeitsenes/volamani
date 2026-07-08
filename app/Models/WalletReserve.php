<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'amount'         => 'integer',
            'release_at'     => 'datetime',
            'released_at'    => 'datetime',
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
