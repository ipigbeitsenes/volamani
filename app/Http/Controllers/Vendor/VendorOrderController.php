<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Orders\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorOrderController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function index(Request $request): View
    {
        $orders = $this->orders->forVendor($request->user()->vendor);

        return view('vendor.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $this->authorizeVendor($order);

        $order = $this->orders->loadForVendor($order);

        return view('vendor.orders.show', compact('order'));
    }

    public function markDelivered(Order $order): RedirectResponse
    {
        $this->authorizeVendor($order);

        if ($this->orders->markDelivered($order)) {
            $this->flashSuccess('Order marked as delivered. The buyer has been notified.');
        } else {
            $this->flashError('This order cannot be marked as delivered.');
        }

        return back();
    }

    public function uploadDeliverable(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeVendor($order);

        $request->validate([
            'file' => ['required', 'file', 'max:20480'],
        ]);

        $this->orders->attachDeliverable($order, $request->file('file'));
        $this->flashSuccess('Deliverable uploaded and the buyer notified.');

        return back();
    }

    private function authorizeVendor(Order $order): void
    {
        abort_unless($order->vendor_id === auth()->user()->vendor?->id, 403);
    }
}
