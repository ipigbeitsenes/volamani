<?php

namespace App\Http\Controllers\Wallet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wallet\FundWalletRequest;
use App\Http\Requests\Wallet\WithdrawalRequest;
use App\Services\Wallet\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(private WalletService $walletService) {}

    public function index(): View
    {
        $user         = auth()->user();
        $wallet       = $this->walletService->getOrCreate($user);
        $transactions = $this->walletService->getTransactions($wallet, 10);
        $withdrawals  = $this->walletService->getUserWithdrawals($user, 5);

        return view('marketplace.wallet.index', compact('wallet', 'transactions', 'withdrawals'));
    }

    public function fund(FundWalletRequest $request): RedirectResponse
    {
        $amountKobo = to_kobo((float) $request->input('amount'));
        $result     = $this->walletService->initiateFunding(auth()->user(), $amountKobo);

        return redirect()->away($result['authorization_url']);
    }

    public function withdraw(WithdrawalRequest $request): RedirectResponse
    {
        $this->walletService->requestWithdrawal(auth()->user(), $request->validated());

        return redirect()->route('wallet.index')
            ->with('success', 'Withdrawal request submitted. We will process it within 24 hours.');
    }

    public function transactions(): View
    {
        $wallet       = $this->walletService->getOrCreate(auth()->user());
        $transactions = $this->walletService->getTransactions($wallet, 20);

        return view('marketplace.wallet.transactions', compact('wallet', 'transactions'));
    }
}
