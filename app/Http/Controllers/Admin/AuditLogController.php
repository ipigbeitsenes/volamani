<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __construct(private AdminService $admin) {}

    public function index(Request $request): View
    {
        $filters = $request->only('log', 'search');
        $logs = $this->admin->auditLogs($filters);
        $logNames = $this->admin->auditLogNames();

        return view('admin.audit-logs.index', compact('logs', 'logNames', 'filters'));
    }
}
