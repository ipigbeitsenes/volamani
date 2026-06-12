<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WalletLedger extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'wallet_id', 'reference', 'type', 'amount', 'balance_after',
        'description', 'metadata', 'ledgerable_type', 'ledgerable_id', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'type'       => TransactionType::class,
            'metadata'   => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Enforce immutability
        static::updating(fn () => throw new \LogicException('Wallet ledger entries are immutable and cannot be updated.'));
        static::deleting(fn () => throw new \LogicException('Wallet ledger entries cannot be deleted.'));

        static::creating(function (self $model) {
            if (empty($model->reference)) {
                $model->reference = generate_reference('LDG');
            }
            if (empty($model->created_at)) {
                $model->created_at = now();
            }
        });
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function ledgerable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isCredit(): bool
    {
        return $this->type->isCredit();
    }
}
