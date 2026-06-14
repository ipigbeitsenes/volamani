<?php

namespace App\Actions\Products;

use App\Models\Order;

class DecrementStockAction
{
    /**
     * Reduce stock for a paid physical order — per variant when the line has one,
     * otherwise on the product's physical detail row (only when inventory is
     * tracked). Never drops below zero. Also bumps the product sales counter.
     */
    public function execute(Order $order): void
    {
        $order->loadMissing('items.product.physicalDetail', 'items.variant');

        foreach ($order->items as $item) {
            $qty = max(1, (int) $item->quantity);

            if ($item->variant) {
                $item->variant->decrement('stock_quantity', min($qty, (int) $item->variant->stock_quantity));
            } elseif ($item->product && $item->product->isPhysical()) {
                $detail = $item->product->physicalDetail;
                if ($detail && $detail->track_inventory) {
                    $detail->decrement('stock_quantity', min($qty, (int) $detail->stock_quantity));
                }
            }

            $item->product?->increment('sales_count', $qty);
        }
    }
}
