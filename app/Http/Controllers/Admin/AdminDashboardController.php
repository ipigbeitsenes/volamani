<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminService;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(private AdminService $admin) {}

    public function index(): View
    {
        $stats   = $this->admin->dashboardStats();
        $revenue = $this->admin->revenueByDay(14);

        return view('admin.dashboard', compact('stats', 'revenue'));
    }
}
