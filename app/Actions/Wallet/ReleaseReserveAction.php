<?php

namespace App\Actions\Wallet;

use App\Enums\TransactionType;
use App\Models\WalletReserve;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;

class ReleaseReserveAction
{
    public function __construct(private WalletService $walletService) {}

    /**
     * Pay a matured chargeback reserve out to the vendor's spendable balance.
     * Moves the amount from reserve_balance and records a ReserveRelease ledger
     * credit so wallet reconciliation stays exact.
     */
    public function execute(WalletReserve $reserve): WalletReserve
    {
        abort_unless($reserve->isHeld(), 422, 'This reserve is not held and cannot be released.');

        return DB::transaction(function () use ($reserve) {
            $locked = WalletReserve::where('id', $reserve->id)->lockForUpdate()->first();

            if (! $locked->isHeld()) {
                return $locked;
            }

            $this->walletService->decrementReserve($locked->wallet, $locked->amount);
            $this->walletService->credit(
                $locked->wallet,
                $locked->amount,
                TransactionType::ReserveRelease,
                "Chargeback reserve released ({$locked->reference})",
                $locked,
                ['reserve_reference' => $locked->reference]
            );

            $locked->update([
                'status' => 'released',
                'released_at' => now(),
            ]);

            return $locked->fresh();
        });
    }
}
