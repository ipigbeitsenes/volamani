<?php

namespace App\Actions\Wallet;

use App\Models\User;
use App\Models\WalletFunding;
use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\DB;

class FundWalletAction
{
    /**
     * PaymentService is resolved lazily (not constructor-injected) to break a
     * circular container dependency: WalletService → FundWalletAction →
     * PaymentService → VerifyPaymentAction → WalletService. Eager injection here
     * makes the container recurse forever (OOM) whenever WalletService is built.
     */
    public function execute(User $user, int $amountKobo): array
    {
        return DB::transaction(function () use ($user, $amountKobo) {
            $wallet  = $user->wallet;
            abort_if(!$wallet, 500, 'User wallet not found.');

            $funding = WalletFunding::create([
                'wallet_id' => $wallet->id,
                'user_id'   => $user->id,
                'amount'    => $amountKobo,
                'status'    => 'pending',
            ]);

            $result = app(PaymentService::class)->initiatePaystackPayment(
                $user,
                $amountKobo,
                $funding,
                ['description' => 'Wallet top-up']
            );

            $funding->update(['payment_id' => $result['payment']->id]);

            return ['funding' => $funding, 'authorization_url' => $result['authorization_url']];
        });
    }
}
