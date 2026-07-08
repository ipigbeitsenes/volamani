<?php

namespace App\Models;

use App\Enums\BuyerStrikeReason;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single abuse strike against a buyer. Mirrors VendorStrike. Active strikes
 * (cleared_at = null) drive the auto-flag / purchase-suspend thresholds.
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
            'reason'     => BuyerStrikeReason::class,
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
