<?php

namespace App\Actions\Services;

use App\Enums\ServiceOrderStatus;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderMessage;
use App\Services\Escrow\EscrowService;
use Illuminate\Support\Facades\DB;

class AcceptDeliveryAction
{
    public function __construct(private EscrowService $escrowService) {}

    public function execute(ServiceOrder $order): ServiceOrder
    {
        abort_unless($order->canAcceptDelivery(), 403, 'Order cannot be completed at this stage.');

        return DB::transaction(function () use ($order) {
            $order->update([
                'status' => ServiceOrderStatus::Completed,
                'completed_at' => now(),
            ]);

            $order->service->increment('orders_count');

            // Buyer accepted delivery — release held funds to the vendor.
            $this->escrowService->releaseForPayable($order, $order->buyer);

            ServiceOrderMessage::create([
                'service_order_id' => $order->id,
                'sender_id' => $order->buyer_id,
                'message' => 'Order marked as complete. Funds have been released to the vendor.',
                'is_system' => true,
            ]);

            return $order->fresh();
        });
    }
}
