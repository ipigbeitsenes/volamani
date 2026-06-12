<?php

namespace App\Services\Orders;

use App\Enums\NotificationCategory;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use App\Repositories\Orders\OrderRepository;
use App\Services\Escrow\EscrowService;
use App\Services\Notifications\NotificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderService
{
    public function __construct(
        private OrderRepository     $repo,
        private EscrowService       $escrow,
        private NotificationService $notifications,
    ) {}

    public function forBuyer(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repo->forBuyer($user, $perPage);
    }

    public function loadForBuyer(Order $order): Order
    {
        return $this->repo->loadForBuyer($order);
    }

    /**
     * Buyer confirms receipt of a paid order: mark it completed and release the
     * held escrow to the vendor (mirrors the escrow "confirm & release" flow for
     * product orders). No-op if the order isn't paid or is already completed.
     */
    public function markComplete(Order $order, User $actor): bool
    {
        if (! $order->isPaid() || $order->isCompleted()) {
            return false;
        }

        DB::transaction(function () use ($order, $actor) {
            $order->update([
                'status'       => OrderStatus::Completed,
                'completed_at' => now(),
            ]);

            $this->escrow->releaseForPayable($order, $actor);
        });

        return true;
    }

    // ─── Vendor side ──────────────────────────────────────────────────────────────

    public function forVendor(Vendor $vendor, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repo->forVendor($vendor, $perPage);
    }

    public function loadForVendor(Order $order): Order
    {
        return $this->repo->loadForVendor($order);
    }

    /** Vendor marks a paid order as delivered and notifies the buyer. */
    public function markDelivered(Order $order): bool
    {
        if (! $order->isPaid() || in_array($order->status, [OrderStatus::Completed, OrderStatus::Delivered, OrderStatus::Cancelled, OrderStatus::Refunded], true)) {
            return false;
        }

        $order->update(['status' => OrderStatus::Delivered]);

        $this->notifyBuyer(
            $order,
            'Order delivered',
            'Your order ' . $order->reference . ' has been marked as delivered. Confirm receipt to release payment.',
        );

        return true;
    }

    /** Attach a deliverable file to an order (custom work the buyer ordered). */
    public function attachDeliverable(Order $order, UploadedFile $file): string
    {
        $path = $file->store('order-deliverables/' . $order->id, 'public');

        $note = '[' . now()->format('d M Y H:i') . '] Deliverable uploaded: ' . $file->getClientOriginalName() . ' (' . $path . ')';
        $order->update([
            'notes' => trim(($order->notes ? $order->notes . "\n" : '') . $note),
        ]);

        $this->notifyBuyer(
            $order,
            'New deliverable available',
            'The seller uploaded a file for your order ' . $order->reference . '.',
        );

        return $path;
    }

    private function notifyBuyer(Order $order, string $title, string $message): void
    {
        if ($order->buyer) {
            $this->notifications->send(
                $order->buyer,
                NotificationCategory::Orders,
                $title,
                $message,
                route('orders.show', $order),
                'View order',
            );
        }
    }
}
