<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $balance
 * @property int $escrow_balance
 * @property int $reserve_balance
 * @property int $pending_withdrawal
 * @property string $currency
 * @property bool $is_frozen
 * @property Carbon|null $last_reconciled_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, WalletFunding> $fundings
 * @property-read int|null $fundings_count
 * @property-read string $formatted_available
 * @property-read string $formatted_balance
 * @property-read string $formatted_escrow
 * @property-read string $formatted_reserve
 * @property-read Collection<int, WalletLedger> $ledgers
 * @property-read int|null $ledgers_count
 * @property-read User|null $user
 * @property-read Collection<int, WalletWithdrawal> $withdrawals
 * @property-read int|null $withdrawals_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet whereEscrowBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet whereIsFrozen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet whereLastReconciledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet wherePendingWithdrawal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet whereReserveBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wallet whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Wallet extends Model
{
    protected $fillable = [
        'user_id', 'balance', 'escrow_balance', 'reserve_balance', 'pending_withdrawal',
        'currency', 'is_frozen', 'last_reconciled_at',
    ];

    protected function casts(): array
    {
        return [
            'is_frozen' => 'boolean',
            'last_reconciled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(WalletLedger::class)->latest('created_at');
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(WalletWithdrawal::class);
    }

    public function fundings(): HasMany
    {
        return $this->hasMany(WalletFunding::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function availableBalance(): int
    {
        return max(0, $this->balance - $this->pending_withdrawal);
    }

    public function canWithdraw(int $amountKobo): bool
    {
        return ! $this->is_frozen && $this->availableBalance() >= $amountKobo;
    }

    public function getFormattedBalanceAttribute(): string
    {
        return money($this->balance);
    }

    public function getFormattedEscrowAttribute(): string
    {
        return money($this->escrow_balance);
    }

    public function getFormattedReserveAttribute(): string
    {
        return money($this->reserve_balance ?? 0);
    }

    public function getFormattedAvailableAttribute(): string
    {
        return money($this->availableBalance());
    }

    /**
     * Reconcile: recalculate balance from ledger.
     * Use for auditing — not on every request.
     */
    public function reconcile(): int
    {
        $sum = WalletLedger::where('wallet_id', $this->id)
            ->selectRaw("SUM(CASE WHEN type IN ('credit','escrow_release','refund','bonus','affiliate_earning','wallet_funding','reserve_release') THEN amount ELSE 0 END) - SUM(CASE WHEN type IN ('debit','escrow_hold','commission','withdrawal','chargeback') THEN amount ELSE 0 END) as net")
            ->value('net') ?? 0;

        $this->update(['balance' => max(0, (int) $sum), 'last_reconciled_at' => now()]);

        return (int) $sum;
    }
}
