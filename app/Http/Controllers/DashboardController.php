<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        // Internal staff teams land on their own consoles.
        if ($user->hasRole('support')) {
            return redirect()->route('support.dashboard');
        }

        if ($user->hasRole('finance')) {
            return redirect()->route('finance.dashboard');
        }

        // Only route to the vendor dashboard when the vendor account is actually
        // approved/active. A vendor-role user whose Vendor record is pending or
        // suspended would otherwise bounce between here and EnsureVendorApproved
        // (which redirects non-active vendors back to 'dashboard') → redirect loop.
        if ($user->isVendor() && $user->vendor?->isActive()) {
            return redirect()->route('vendor.dashboard');
        }

        $recentOrders = Schema::hasTable('orders')
            ? $user->orders()->latest()->limit(5)->get()
            : collect();

        return view('dashboard', compact('user', 'recentOrders'));
    }
}
