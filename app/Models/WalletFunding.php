<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $reference
 * @property int $wallet_id
 * @property int $user_id
 * @property int|null $payment_id
 * @property int $amount
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Payment|null $payment
 * @property-read User|null $user
 * @property-read Wallet $wallet
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletFunding newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletFunding newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletFunding query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletFunding whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletFunding whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletFunding whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletFunding wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletFunding whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletFunding whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletFunding whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletFunding whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletFunding whereWalletId($value)
 *
 * @mixin \Eloquent
 */
class WalletFunding extends Model
{
    protected $fillable = [
        'reference', 'wallet_id', 'user_id', 'payment_id', 'amount', 'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->reference)) {
                $model->reference = generate_reference('FND');
            }
        });
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
