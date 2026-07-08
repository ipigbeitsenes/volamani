<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\Admin\AdminService;
use App\Services\Documents\DocumentService;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(private AdminService $admin) {}

    public function index(DocumentService $documents): View
    {
        $stats   = $this->admin->dashboardStats();
        $revenue = $this->admin->revenueByDay(14);

        // Volamani-issued documents (platform invoices + contracts of sale).
        $platformDocs = Schema::hasTable('documents')
            ? array_merge($documents->platformStats(), [
                'contracts' => Document::whereNull('vendor_id')->where('type', DocumentType::Contract)->count(),
                'signed'    => Document::whereNull('vendor_id')->where('type', DocumentType::Contract)->where('status', DocumentStatus::Signed)->count(),
            ])
            : ['outstanding' => 0, 'paid_total' => 0, 'draft_count' => 0, 'contracts' => 0, 'signed' => 0];

        return view('admin.dashboard', compact('stats', 'revenue', 'platformDocs'));
    }
}
