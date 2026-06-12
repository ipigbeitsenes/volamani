<?php

namespace App\Actions\Services;

use App\Enums\ServiceOrderStatus;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderMessage;
use Illuminate\Support\Facades\DB;

class SubmitRequirementsAction
{
    public function execute(ServiceOrder $order, string $requirements): ServiceOrder
    {
        return DB::transaction(function () use ($order, $requirements) {
            $order->update([
                'requirements' => $requirements,
                'status'       => ServiceOrderStatus::InProgress,
                'started_at'   => now(),
                'due_at'       => now()->addDays($order->package->delivery_days),
            ]);

            ServiceOrderMessage::create([
                'service_order_id' => $order->id,
                'sender_id'        => $order->buyer_id,
                'message'          => $requirements,
                'is_system'        => false,
            ]);

            ServiceOrderMessage::create([
                'service_order_id' => $order->id,
                'sender_id'        => $order->buyer_id,
                'message'          => "Requirements submitted. Order is now in progress. Expected delivery: {$order->due_at->format('M j, Y')}.",
                'is_system'        => true,
            ]);

            return $order->fresh();
        });
    }
}
