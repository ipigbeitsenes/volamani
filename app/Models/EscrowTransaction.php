<?php

namespace App\Models;

use App\Enums\EscrowTransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $escrow_id
 * @property string $reference
 * @property EscrowTransactionType $type
 * @property int $amount
 * @property int $balance_after
 * @property string $description
 * @property int|null $actor_id
 * @property array<array-key, mixed>|null $metadata
 * @property Carbon $created_at
 * @property-read User|null $actor
 * @property-read Escrow $escrow
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EscrowTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EscrowTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EscrowTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EscrowTransaction whereActorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EscrowTransaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EscrowTransaction whereBalanceAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EscrowTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EscrowTransaction whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EscrowTransaction whereEscrowId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EscrowTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EscrowTransaction whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EscrowTransaction whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EscrowTransaction whereType($value)
 *
 * @mixin \Eloquent
 */
class EscrowTransaction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'escrow_id', 'reference', 'type', 'amount', 'balance_after',
        'description', 'actor_id', 'metadata', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => EscrowTransactionType::class,
            'amount' => 'integer',
            'balance_after' => 'integer',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Immutable audit trail — same guarantee as the wallet ledger.
        static::updating(fn () => throw new \LogicException('Escrow transactions are immutable and cannot be updated.'));
        static::deleting(fn () => throw new \LogicException('Escrow transactions cannot be deleted.'));

        static::creating(function (self $model) {
            if (empty($model->reference)) {
                $model->reference = generate_reference('ETX');
            }
            if (empty($model->created_at)) {
                $model->created_at = now();
            }
        });
    }

    public function escrow(): BelongsTo
    {
        return $this->belongsTo(Escrow::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
