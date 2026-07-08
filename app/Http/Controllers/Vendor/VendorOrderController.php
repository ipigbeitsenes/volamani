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

    public function markShipped(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeVendor($order);

        $data = $request->validate([
            'tracking_number' => ['nullable', 'string', 'max:120'],
            'courier'         => ['nullable', 'string', 'max:120'],
        ]);

        if ($this->orders->markShipped($order, $data['tracking_number'] ?? null, $data['courier'] ?? null)) {
            $this->flashSuccess('Order marked as shipped. The buyer has been notified.');
        } else {
            $this->flashError('This order cannot be marked as shipped.');
        }

        return back();
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

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeVendor($order);

        $data = $request->validate([
            'cancellation_reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        if ($this->orders->cancelByVendor($order, $request->user(), $data['cancellation_reason'])) {
            $this->flashSuccess('Order cancelled and the buyer refunded. They have been notified.');
        } else {
            $this->flashError('This order can no longer be cancelled.');
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
