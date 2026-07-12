<?php

namespace App\Actions\Wallet;

use App\Enums\TransactionType;
use App\Models\Wallet;
use App\Models\WalletLedger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DebitWalletAction
{
    public function execute(
        Wallet $wallet,
        int $amountKobo,
        TransactionType $type,
        string $description,
        ?Model $ledgerable = null,
        array $metadata = []
    ): WalletLedger {
        return DB::transaction(function () use ($wallet, $amountKobo, $type, $description, $ledgerable, $metadata) {
            $locked = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            abort_if($locked->is_frozen, 422, 'Your wallet is currently frozen. Please contact support.');
            abort_if($locked->availableBalance() < $amountKobo, 422,
                'Insufficient wallet balance. Available: '.money($locked->availableBalance())
            );

            $newBalance = $locked->balance - $amountKobo;
            $locked->update(['balance' => $newBalance]);

            return WalletLedger::create([
                'wallet_id' => $locked->id,
                'type' => $type,
                'amount' => $amountKobo,
                'balance_after' => $newBalance,
                'description' => $description,
                'metadata' => $metadata ?: null,
                'ledgerable_type' => $ledgerable ? get_class($ledgerable) : null,
                'ledgerable_id' => $ledgerable?->getKey(),
            ]);
        });
    }
}
