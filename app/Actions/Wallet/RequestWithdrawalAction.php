<?php

namespace App\Actions\Wallet;

use App\Enums\TransactionType;
use App\Enums\WithdrawalStatus;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletWithdrawal;
use Illuminate\Support\Facades\DB;

class RequestWithdrawalAction
{
    public function __construct(private DebitWalletAction $debit) {}

    public function execute(User $user, array $data): WalletWithdrawal
    {
        return DB::transaction(function () use ($user, $data) {
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
            abort_if(!$wallet, 500, 'Wallet not found.');

            $amount     = to_kobo((float) $data['amount']);
            $feePercent = (float) config('payment.withdrawal_fee_percent', 1.5);
            $fee        = (int) round($amount * ($feePercent / 100));
            $netAmount  = $amount - $fee;

            abort_if($netAmount <= 0, 422, 'Withdrawal amount too small after fee deduction.');
            abort_unless($wallet->canWithdraw($amount), 422,
                'Insufficient balance. Available: ' . money($wallet->availableBalance())
            );

            $withdrawal = WalletWithdrawal::create([
                'wallet_id'      => $wallet->id,
                'user_id'        => $user->id,
                'amount'         => $amount,
                'fee'            => $fee,
                'net_amount'     => $netAmount,
                'bank_name'      => $data['bank_name'],
                'account_name'   => $data['account_name'],
                'account_number' => $data['account_number'],
                'bank_code'      => $data['bank_code'] ?? null,
                'status'         => WithdrawalStatus::Pending,
            ]);

            // Lock the funds immediately so they can't be double-spent
            $wallet->increment('pending_withdrawal', $amount);

            $this->debit->execute(
                $wallet,
                $amount,
                TransactionType::Withdrawal,
                "Withdrawal request #{$withdrawal->reference}",
                $withdrawal
            );

            // Reverse the pending_withdrawal lock since debit already removed from balance
            $wallet->decrement('pending_withdrawal', $amount);

            return $withdrawal;
        });
    }
}
