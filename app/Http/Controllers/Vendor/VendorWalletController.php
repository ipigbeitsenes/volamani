<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wallet\WithdrawalRequest;
use App\Services\Wallet\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VendorWalletController extends Controller
{
    public function __construct(private WalletService $walletService) {}

    public function index(): View
    {
        $user         = auth()->user();
        $wallet       = $this->walletService->getOrCreate($user);
        $transactions = $this->walletService->getTransactions($wallet, 10);
        $withdrawals  = $this->walletService->getUserWithdrawals($user, 5);

        return view('vendor.wallet.index', compact('wallet', 'transactions', 'withdrawals'));
    }

    public function requestWithdrawal(WithdrawalRequest $request): RedirectResponse
    {
        $this->walletService->requestWithdrawal(auth()->user(), $request->validated());

        return redirect()->route('vendor.wallet.index')
            ->with('success', 'Withdrawal request submitted. Processing takes up to 24 hours.');
    }

    public function transactions(): View
    {
        $user         = auth()->user();
        $wallet       = $this->walletService->getOrCreate($user);
        $transactions = $this->walletService->getTransactions($wallet, 20);

        return view('vendor.wallet.transactions', compact('wallet', 'transactions'));
    }
}
