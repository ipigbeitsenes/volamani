<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Services\Admin\AdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorApprovalController extends Controller
{
    public function __construct(private AdminService $admin) {}

    public function index(Request $request): View
    {
        $filters = $request->only('status', 'search');
        $vendors = $this->admin->vendors($filters);
        $counts  = $this->admin->vendorCountsByStatus();

        return view('admin.vendors.index', compact('vendors', 'filters', 'counts'));
    }

    public function show(Vendor $vendor): View
    {
        $vendor->load('user');
        $vendor->loadCount('products', 'services');

        return view('admin.vendors.show', compact('vendor'));
    }

    public function approve(Vendor $vendor): RedirectResponse
    {
        $this->admin->approveVendor($vendor, auth()->user());
        $this->flashSuccess("\"{$vendor->business_name}\" approved and is now live.");

        return back();
    }

    public function reject(Request $request, Vendor $vendor): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $this->admin->rejectVendor($vendor, auth()->user(), $data['reason']);
        $this->flashWarning("\"{$vendor->business_name}\" application declined.");

        return back();
    }

    public function suspend(Request $request, Vendor $vendor): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $this->admin->suspendVendor($vendor, auth()->user(), $data['reason']);
        $this->flashWarning("\"{$vendor->business_name}\" has been suspended.");

        return back();
    }
}
