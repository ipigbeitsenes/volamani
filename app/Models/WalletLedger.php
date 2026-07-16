<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $wallet_id
 * @property string $reference
 * @property TransactionType $type
 * @property int $amount
 * @property int $balance_after
 * @property string $description
 * @property array<array-key, mixed>|null $metadata
 * @property string|null $ledgerable_type
 * @property int|null $ledgerable_id
 * @property Carbon $created_at
 * @property-read Model|\Eloquent|null $ledgerable
 * @property-read Wallet $wallet
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletLedger newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletLedger newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletLedger query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletLedger whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletLedger whereBalanceAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletLedger whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletLedger whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletLedger whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletLedger whereLedgerableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletLedger whereLedgerableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletLedger whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletLedger whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletLedger whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletLedger whereWalletId($value)
 *
 * @mixin \Eloquent
 */
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
            'type' => TransactionType::class,
            'metadata' => 'array',
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
