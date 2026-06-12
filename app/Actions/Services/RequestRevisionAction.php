<?php

namespace App\Actions\Services;

use App\Enums\ServiceOrderStatus;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderMessage;
use Illuminate\Support\Facades\DB;

class RequestRevisionAction
{
    public function execute(ServiceOrder $order, string $feedback): ServiceOrder
    {
        abort_unless($order->canRequestRevision(), 403, 'No revisions remaining.');

        return DB::transaction(function () use ($order, $feedback) {
            $order->increment('revisions_used');
            $order->update(['status' => ServiceOrderStatus::RevisionRequested]);

            ServiceOrderMessage::create([
                'service_order_id' => $order->id,
                'sender_id'        => $order->buyer_id,
                'message'          => $feedback,
            ]);

            ServiceOrderMessage::create([
                'service_order_id' => $order->id,
                'sender_id'        => $order->buyer_id,
                'message'          => "Revision #{$order->revisions_used} requested. Revisions remaining: {$order->remainingRevisions()}.",
                'is_system'        => true,
            ]);

            return $order->fresh();
        });
    }
}
