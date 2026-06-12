<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Orders\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function index(Request $request): View
    {
        $orders = $this->orders->forBuyer($request->user());

        return view('marketplace.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $this->authorizeBuyer($order);

        $order = $this->orders->loadForBuyer($order);

        return view('marketplace.orders.show', compact('order'));
    }

    public function markComplete(Order $order): RedirectResponse
    {
        $this->authorizeBuyer($order);

        if ($this->orders->markComplete($order, auth()->user())) {
            $this->flashSuccess('Order confirmed and payment released to the seller.');
        } else {
            $this->flashError('This order cannot be completed.');
        }

        return back();
    }

    private function authorizeBuyer(Order $order): void
    {
        abort_unless($order->buyer_id === auth()->id(), 403);
    }
}
