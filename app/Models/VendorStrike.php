<?php

namespace App\Models;

use App\Enums\StrikeReason;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'reason'     => StrikeReason::class,
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
