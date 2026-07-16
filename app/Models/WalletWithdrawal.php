<?php

namespace App\Models;

use App\Enums\WithdrawalStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property string $reference
 * @property int $wallet_id
 * @property int $user_id
 * @property int $amount
 * @property int $fee
 * @property int $net_amount
 * @property string $bank_name
 * @property string $account_name
 * @property string $account_number
 * @property string|null $bank_code
 * @property WithdrawalStatus $status
 * @property string|null $admin_notes
 * @property int|null $processed_by
 * @property Carbon|null $processed_at
 * @property string|null $paystack_transfer_code
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read User|null $processedBy
 * @property-read User|null $user
 * @property-read Wallet $wallet
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletWithdrawal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletWithdrawal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletWithdrawal onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletWithdrawal query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletWithdrawal whereAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletWithdrawal whereAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletWithdrawal whereAdminNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletWithdrawal whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletWithdrawal whereBankCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletWithdrawal whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletWithdrawal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletWithdrawal whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletWithdrawal whereFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletWithdrawal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletWithdrawal whereNetAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletWithdrawal wherePaystackTransferCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletWithdrawal whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletWithdrawal whereProcessedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletWithdrawal whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletWithdrawal whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletWithdrawal whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletWithdrawal whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletWithdrawal whereWalletId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletWithdrawal withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WalletWithdrawal withoutTrashed()
 *
 * @mixin \Eloquent
 */
class WalletWithdrawal extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'reference', 'wallet_id', 'user_id', 'amount', 'fee', 'net_amount',
        'bank_name', 'account_name', 'account_number', 'bank_code',
        'status', 'admin_notes', 'processed_by', 'processed_at', 'paystack_transfer_code',
    ];

    protected function casts(): array
    {
        return [
            'status' => WithdrawalStatus::class,
            'processed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->reference)) {
                $model->reference = generate_reference('WDR');
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

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function isPending(): bool
    {
        return $this->status === WithdrawalStatus::Pending;
    }

    public function canBeProcessed(): bool
    {
        return in_array($this->status, [WithdrawalStatus::Pending, WithdrawalStatus::Processing]);
    }
}
