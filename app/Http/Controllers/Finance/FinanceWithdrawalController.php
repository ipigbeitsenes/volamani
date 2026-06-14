<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\WalletWithdrawal;
use App\Repositories\Wallet\WalletRepository;
use App\Services\Admin\AdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceWithdrawalController extends Controller
{
    public function __construct(
        private AdminService     $admin,
        private WalletRepository $walletRepo,
    ) {}

    public function index(Request $request): View
    {
        $filters     = $request->only('status', 'search');
        $withdrawals = $this->walletRepo->allWithdrawalsForAdmin(20, $filters);

        return view('finance.withdrawals.index', compact('withdrawals', 'filters'));
    }

    public function approve(WalletWithdrawal $withdrawal): RedirectResponse
    {
        if (! $withdrawal->canBeProcessed()) {
            $this->flashError('This withdrawal can no longer be processed.');

            return back();
        }

        $this->admin->approveWithdrawal($withdrawal, auth()->user());
        $this->flashSuccess('Withdrawal approved.');

        return back();
    }

    public function reject(Request $request, WalletWithdrawal $withdrawal): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        if (! $withdrawal->canBeProcessed()) {
            $this->flashError('This withdrawal can no longer be processed.');

            return back();
        }

        $this->admin->rejectWithdrawal($withdrawal, auth()->user(), $data['reason']);
        $this->flashWarning('Withdrawal rejected and funds returned.');

        return back();
    }
}
