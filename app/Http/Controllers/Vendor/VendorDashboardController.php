<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Services\Escrow\EscrowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class VendorDashboardController extends Controller
{
    public function index(Request $request, EscrowService $escrowService): View
    {
        $vendor = $request->user()->vendor;
        $wallet = $request->user()->wallet;

        $stats = [
            'balance'        => $wallet?->balance ?? 0,
            'pending_earnings' => Schema::hasTable('escrows')
                ? $escrowService->heldTotalForVendor($vendor) : 0,
            'total_products' => Schema::hasTable('products')
                ? $vendor->products()->count() : 0,
            'total_services' => Schema::hasTable('freelance_services')
                ? $vendor->services()->count() : 0,
            'total_orders'   => Schema::hasTable('orders')
                ? \App\Models\Order::where('vendor_id', $vendor->id)->count() : 0,
            'service_orders' => Schema::hasTable('service_orders')
                ? \App\Models\ServiceOrder::where('vendor_id', $vendor->id)->count() : 0,
            'followers'      => (int) ($vendor->followers_count ?? 0),
            'total_reviews'  => Schema::hasTable('reviews')
                ? $vendor->reviews()->count() : 0,
            'avg_rating'     => Schema::hasTable('reviews')
                ? round($vendor->averageRating(), 1) : 0,
        ];

        $recentOrders = Schema::hasTable('orders')
            ? \App\Models\Order::where('vendor_id', $vendor->id)->latest()->limit(5)->get()
            : collect();

        return view('vendor.dashboard', compact('vendor', 'stats', 'recentOrders'));
    }
}
