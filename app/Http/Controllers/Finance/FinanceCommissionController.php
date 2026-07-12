<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceCommissionController extends Controller
{
    public function __construct(private AdminService $admin) {}

    public function index(): View
    {
        $commissions = [
            'platform_commission' => (int) settings('platform_commission', 10),
            'affiliate_commission' => (int) settings('affiliate_commission', 5),
            'withdrawal_fee' => (int) settings('withdrawal_fee', 5000),
            'min_withdrawal' => (int) settings('min_withdrawal', 200000),
        ];

        return view('finance.commissions.index', compact('commissions'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'platform_commission' => ['required', 'integer', 'min:0', 'max:100'],
            'affiliate_commission' => ['required', 'integer', 'min:0', 'max:100'],
            'withdrawal_fee' => ['required', 'integer', 'min:0'],
            'min_withdrawal' => ['required', 'integer', 'min:0'],
        ]);

        $this->admin->updateCommissions($data);
        $this->flashSuccess('Commission settings updated.');

        return redirect()->route('finance.commissions.index');
    }
}
