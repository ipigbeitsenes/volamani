<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $vendor = $request->user()->vendor;

        $paidStatuses = [
            OrderStatus::Paid->value,
            OrderStatus::Processing->value,
            OrderStatus::InProgress->value,
            OrderStatus::Delivered->value,
            OrderStatus::Completed->value,
        ];

        $base = Order::where('vendor_id', $vendor->id);

        $stats = [
            'total_orders' => (clone $base)->count(),
            'paid_orders' => (clone $base)->whereIn('status', $paidStatuses)->count(),
            'gross_sales' => (int) (clone $base)->whereIn('status', $paidStatuses)->sum('total_amount'),
            'net_earnings' => (int) (clone $base)->whereIn('status', $paidStatuses)->sum('vendor_earnings'),
            'products' => $vendor->products()->count(),
            'services' => $vendor->services()->count(),
            'avg_rating' => round($vendor->averageRating(), 1),
            'reviews' => $vendor->totalReviews(),
        ];

        // 14-day earnings trend (zero-filled).
        $rows = (clone $base)
            ->whereIn('status', $paidStatuses)
            ->where('created_at', '>=', now()->subDays(14)->startOfDay())
            ->select(DB::raw('DATE(created_at) as d'), DB::raw('SUM(vendor_earnings) as total'))
            ->groupBy('d')
            ->pluck('total', 'd');

        $trend = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $trend[$date] = (int) ($rows[$date] ?? 0);
        }

        // Top products by units sold for this vendor's orders.
        $topProducts = OrderItem::select('name', DB::raw('SUM(quantity) as units'), DB::raw('SUM(subtotal) as revenue'))
            ->whereHas('order', fn ($q) => $q->where('vendor_id', $vendor->id)->whereIn('status', $paidStatuses))
            ->groupBy('name')
            ->orderByDesc('units')
            ->limit(5)
            ->get();

        return view('vendor.analytics', compact('vendor', 'stats', 'trend', 'topProducts'));
    }
}
