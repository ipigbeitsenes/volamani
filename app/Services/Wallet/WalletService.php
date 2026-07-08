<?php

namespace App\Services\Wallet;

use App\Actions\Wallet\CreditWalletAction;
use App\Actions\Wallet\DebitWalletAction;
use App\Actions\Wallet\FundWalletAction;
use App\Actions\Wallet\ProcessWithdrawalAction;
use App\Actions\Wallet\RequestWithdrawalAction;
use App\Enums\TransactionType;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletFunding;
use App\Models\WalletLedger;
use App\Models\WalletWithdrawal;
use App\Repositories\Wallet\WalletRepository;

class WalletService
{
    public function __construct(
        private CreditWalletAction      $creditAction,
        private DebitWalletAction       $debitAction,
        private FundWalletAction        $fundAction,
        private RequestWithdrawalAction $requestWithdrawalAction,
        private ProcessWithdrawalAction $processWithdrawalAction,
        private WalletRepository        $repo,
    ) {}

    public function getOrCreate(User $user): Wallet
    {
        return $user->wallet ?? Wallet::create([
            'user_id'        => $user->id,
            'balance'        => 0,
            'escrow_balance' => 0,
        ]);
    }

    public function credit(
        Wallet $wallet,
        int    $amountKobo,
        TransactionType $type,
        string $description,
        $ledgerable = null,
        array  $metadata = []
    ): WalletLedger {
        return $this->creditAction->execute($wallet, $amountKobo, $type, $description, $ledgerable, $metadata);
    }

    public function debit(
        Wallet $wallet,
        int    $amountKobo,
        TransactionType $type,
        string $description,
        $ledgerable = null,
        array  $metadata = []
    ): WalletLedger {
        return $this->debitAction->execute($wallet, $amountKobo, $type, $description, $ledgerable, $metadata);
    }

    /**
     * Move funds into the wallet's pending escrow balance (vendor side, on hold).
     * Does NOT write a balance-affecting ledger entry — escrow holds are tracked
     * separately in escrow_transactions so wallet reconciliation stays valid.
     */
    public function incrementEscrow(Wallet $wallet, int $amountKobo): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($wallet, $amountKobo) {
            $locked = Wallet::where('id', $wallet->id)->lockForUpdate()->first();
            $locked->update(['escrow_balance' => $locked->escrow_balance + $amountKobo]);
        });
    }

    /**
     * Remove funds from the wallet's pending escrow balance (on release or refund).
     */
    public function decrementEscrow(Wallet $wallet, int $amountKobo): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($wallet, $amountKobo) {
            $locked = Wallet::where('id', $wallet->id)->lockForUpdate()->first();
            $locked->update(['escrow_balance' => max(0, $locked->escrow_balance - $amountKobo)]);
        });
    }

    /**
     * Move funds into the wallet's non-spendable reserve balance (rolling
     * chargeback buffer). Like escrow holds, this writes NO balance-affecting
     * ledger entry — reserves are tracked in wallet_reserves so reconciliation
     * stays valid.
     */
    public function incrementReserve(Wallet $wallet, int $amountKobo): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($wallet, $amountKobo) {
            $locked = Wallet::where('id', $wallet->id)->lockForUpdate()->first();
            $locked->update(['reserve_balance' => ($locked->reserve_balance ?? 0) + $amountKobo]);
        });
    }

    /** Remove funds from the reserve balance (on payout or chargeback clawback). */
    public function decrementReserve(Wallet $wallet, int $amountKobo): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($wallet, $amountKobo) {
            $locked = Wallet::where('id', $wallet->id)->lockForUpdate()->first();
            $locked->update(['reserve_balance' => max(0, ($locked->reserve_balance ?? 0) - $amountKobo)]);
        });
    }

    public function initiateFunding(User $user, int $amountKobo, string $method = 'paystack'): array
    {
        return $this->fundAction->execute($user, $amountKobo, $method);
    }

    public function completeFunding(WalletFunding $funding): void
    {
        if ($funding->status === 'completed') {
            return;
        }

        $funding->update(['status' => 'completed']);

        $this->creditAction->execute(
            $funding->wallet,
            $funding->amount,
            TransactionType::WalletFunding,
            "Wallet funded via payment #{$funding->reference}",
            $funding
        );
    }

    public function requestWithdrawal(User $user, array $data): WalletWithdrawal
    {
        return $this->requestWithdrawalAction->execute($user, $data);
    }

    public function approveWithdrawal(WalletWithdrawal $withdrawal, User $admin): WalletWithdrawal
    {
        return $this->processWithdrawalAction->approve($withdrawal, $admin);
    }

    public function rejectWithdrawal(WalletWithdrawal $withdrawal, User $admin, string $reason): WalletWithdrawal
    {
        return $this->processWithdrawalAction->reject($withdrawal, $admin, $reason);
    }

    public function getTransactions(Wallet $wallet, int $perPage = 20)
    {
        return $this->repo->paginateLedger($wallet, $perPage);
    }

    public function getPendingWithdrawals(int $perPage = 20)
    {
        return $this->repo->pendingWithdrawals($perPage);
    }

    public function getUserWithdrawals(User $user, int $perPage = 20)
    {
        return $this->repo->userWithdrawals($user, $perPage);
    }
}
