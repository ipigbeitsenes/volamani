<?php

namespace App\Actions\Products;

use App\Models\Order;

class RestockOrderAction
{
    /**
     * Return stock to inventory for a physical order (reverse of
     * DecrementStockAction) — used when a return is refunded. Also rolls back
     * the product sales counter.
     */
    public function execute(Order $order): void
    {
        $order->loadMissing('items.product.physicalDetail', 'items.variant');

        foreach ($order->items as $item) {
            $qty = max(1, (int) $item->quantity);

            if ($item->variant) {
                $item->variant->increment('stock_quantity', $qty);
            } elseif ($item->product && $item->product->isPhysical()) {
                $detail = $item->product->physicalDetail;
                if ($detail && $detail->track_inventory) {
                    $detail->increment('stock_quantity', $qty);
                }
            }

            if ($item->product && $item->product->sales_count > 0) {
                $item->product->decrement('sales_count', min($qty, (int) $item->product->sales_count));
            }
        }
    }
}
