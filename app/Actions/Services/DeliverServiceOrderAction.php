<?php

namespace App\Actions\Services;

use App\Enums\NotificationCategory;
use App\Enums\ServiceOrderStatus;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderMessage;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeliverServiceOrderAction
{
    public function __construct(private NotificationService $notifications) {}

    public function execute(ServiceOrder $order, string $message, ?UploadedFile $attachment = null): ServiceOrderMessage
    {
        $deliveryMessage = DB::transaction(function () use ($order, $message, $attachment) {
            $order->update([
                'status'       => ServiceOrderStatus::Delivered,
                'delivered_at' => now(),
            ]);

            $attachmentPath = null;
            $attachmentName = null;
            if ($attachment) {
                $attachmentPath = $attachment->store('service-orders/' . $order->id, 'public');
                $attachmentName = $attachment->getClientOriginalName();
            }

            $deliveryMessage = ServiceOrderMessage::create([
                'service_order_id' => $order->id,
                'sender_id'        => $order->vendor->user_id,
                'message'          => $message,
                'attachment'       => $attachmentPath,
                'attachment_name'  => $attachmentName,
                'is_delivery'      => true,
            ]);

            ServiceOrderMessage::create([
                'service_order_id' => $order->id,
                'sender_id'        => $order->vendor->user_id,
                'message'          => 'Delivery submitted. The buyer has been notified to review and accept or request a revision.',
                'is_system'        => true,
            ]);

            return $deliveryMessage;
        });

        if ($order->buyer) {
            $this->notifications->send(
                $order->buyer,
                NotificationCategory::ServiceOrders,
                'Your order has been delivered',
                'The vendor submitted a delivery for order ' . $order->reference . '. Review it to accept or request a revision.',
                route('service-orders.show', $order),
                'Review delivery',
            );
        }

        return $deliveryMessage;
    }
}
