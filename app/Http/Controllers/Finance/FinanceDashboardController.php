<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminService;
use Illuminate\View\View;

class FinanceDashboardController extends Controller
{
    public function __construct(private AdminService $admin) {}

    public function index(): View
    {
        $stats = $this->admin->financeStats();
        $revenue = $this->admin->revenueByDay(14);

        return view('finance.dashboard', compact('stats', 'revenue'));
    }
}
