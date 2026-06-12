<?php

namespace App\Actions\Wallet;

use App\Enums\TransactionType;
use App\Enums\WithdrawalStatus;
use App\Models\User;
use App\Models\WalletWithdrawal;
use Illuminate\Support\Facades\DB;

class ProcessWithdrawalAction
{
    public function __construct(private CreditWalletAction $credit) {}

    public function approve(WalletWithdrawal $withdrawal, User $admin): WalletWithdrawal
    {
        abort_unless($withdrawal->canBeProcessed(), 422, 'Withdrawal cannot be approved at this stage.');

        $withdrawal->update([
            'status'       => WithdrawalStatus::Paid,
            'processed_by' => $admin->id,
            'processed_at' => now(),
        ]);

        return $withdrawal->fresh();
    }

    public function reject(WalletWithdrawal $withdrawal, User $admin, string $reason): WalletWithdrawal
    {
        abort_unless($withdrawal->canBeProcessed(), 422, 'Withdrawal cannot be rejected at this stage.');

        return DB::transaction(function () use ($withdrawal, $admin, $reason) {
            $withdrawal->update([
                'status'       => WithdrawalStatus::Rejected,
                'admin_notes'  => $reason,
                'processed_by' => $admin->id,
                'processed_at' => now(),
            ]);

            // Refund the deducted amount back to wallet
            $this->credit->execute(
                $withdrawal->wallet,
                $withdrawal->amount,
                TransactionType::Refund,
                "Withdrawal rejected: #{$withdrawal->reference}. {$reason}",
                $withdrawal
            );

            return $withdrawal->fresh();
        });
    }
}
