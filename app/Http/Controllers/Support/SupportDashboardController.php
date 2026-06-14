<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminService;
use Illuminate\View\View;

class SupportDashboardController extends Controller
{
    public function __construct(private AdminService $admin) {}

    public function index(): View
    {
        $queues = $this->admin->supportQueues();

        return view('support.dashboard', compact('queues'));
    }
}
