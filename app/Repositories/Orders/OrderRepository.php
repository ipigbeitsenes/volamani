<?php

namespace App\Repositories\Orders;

use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository
{
    public function forBuyer(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Order::where('buyer_id', $user->id)
            ->with('vendor')
            ->withCount('items')
            ->latest()
            ->paginate($perPage);
    }

    public function loadForBuyer(Order $order): Order
    {
        return $order->load([
            'vendor',
            'items.product.files',
            'items.product.gallery',
        ]);
    }

    public function forVendor(Vendor $vendor, int $perPage = 15): LengthAwarePaginator
    {
        return Order::where('vendor_id', $vendor->id)
            ->with('buyer')
            ->withCount('items')
            ->latest()
            ->paginate($perPage);
    }

    public function loadForVendor(Order $order): Order
    {
        return $order->load(['buyer', 'items.product']);
    }
}
