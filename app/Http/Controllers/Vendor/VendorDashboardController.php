<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\DocumentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Order;
use App\Models\ServiceOrder;
use App\Services\Documents\DocumentService;
use App\Services\Escrow\EscrowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class VendorDashboardController extends Controller
{
    public function index(Request $request, EscrowService $escrowService, DocumentService $documentService): View
    {
        $vendor = $request->user()->vendor;
        $wallet = $request->user()->wallet;

        // Billing: invoices outstanding/paid + contracts of sale.
        $documents = Schema::hasTable('documents')
            ? array_merge($documentService->vendorStats($vendor), [
                'contracts' => Document::where('vendor_id', $vendor->id)->where('type', DocumentType::Contract)->count(),
            ])
            : ['outstanding' => 0, 'paid_total' => 0, 'draft_count' => 0, 'contracts' => 0];

        $stats = [
            'balance' => $wallet?->balance ?? 0,
            'pending_earnings' => Schema::hasTable('escrows')
                ? $escrowService->heldTotalForVendor($vendor) : 0,
            'total_products' => Schema::hasTable('products')
                ? $vendor->products()->count() : 0,
            'total_services' => Schema::hasTable('freelance_services')
                ? $vendor->services()->count() : 0,
            'total_orders' => Schema::hasTable('orders')
                ? Order::where('vendor_id', $vendor->id)->count() : 0,
            // Paid orders still needing the vendor to act (ship / mark delivered),
            // or to cancel & refund if they can't fulfil them.
            'orders_to_fulfil' => Schema::hasTable('orders')
                ? Order::where('vendor_id', $vendor->id)
                    ->where('payment_status', PaymentStatus::Success->value)
                    ->whereIn('status', [
                        OrderStatus::Paid->value,
                        OrderStatus::Processing->value,
                        OrderStatus::Shipped->value,
                    ])->count()
                : 0,
            'service_orders' => Schema::hasTable('service_orders')
                ? ServiceOrder::where('vendor_id', $vendor->id)->count() : 0,
            'followers' => (int) ($vendor->followers_count ?? 0),
            'total_reviews' => Schema::hasTable('reviews')
                ? $vendor->reviews()->count() : 0,
            'avg_rating' => Schema::hasTable('reviews')
                ? round($vendor->averageRating(), 1) : 0,
        ];

        $recentOrders = Schema::hasTable('orders')
            ? Order::where('vendor_id', $vendor->id)->latest()->limit(5)->get()
            : collect();

        return view('vendor.dashboard', compact('vendor', 'stats', 'recentOrders', 'documents'));
    }
}
