<?php

namespace App\Services\Orders;

use App\Enums\NotificationCategory;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use App\Actions\Products\RestockOrderAction;
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
        private RestockOrderAction  $restock,
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

    /** Vendor marks a physical order shipped, recording tracking details. */
    public function markShipped(Order $order, ?string $trackingNumber, ?string $courier): bool
    {
        if (! $order->canShip()) {
            return false;
        }

        $order->update([
            'status'          => OrderStatus::Shipped,
            'tracking_number' => $trackingNumber,
            'courier'         => $courier,
            'shipped_at'      => now(),
        ]);

        $tracking = $trackingNumber ? " Tracking: {$trackingNumber}" . ($courier ? " ({$courier})" : '') . '.' : '';
        $this->notifyBuyer(
            $order,
            'Order shipped',
            'Your order ' . $order->reference . ' is on its way.' . $tracking,
        );

        return true;
    }

    /**
     * Vendor marks a paid order as delivered and notifies the buyer. For physical
     * orders this also arms the escrow FALLBACK auto-release timer (N business
     * days from delivery), so a silent buyer can't freeze the vendor's funds —
     * while still letting the buyer "confirm receipt" to release immediately.
     */
    public function markDelivered(Order $order): bool
    {
        if (! $order->isPaid() || in_array($order->status, [OrderStatus::Completed, OrderStatus::Delivered, OrderStatus::Cancelled, OrderStatus::Refunded], true)) {
            return false;
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'status'       => OrderStatus::Delivered,
                'delivered_at' => now(),
            ]);

            if ($order->requires_shipping) {
                $escrow = $this->escrow->forPayable($order);
                if ($escrow && $escrow->auto_release_at === null && $escrow->status === \App\Enums\EscrowStatus::Holding) {
                    $days = (int) config('business_days.release_days', 3);
                    $escrow->update([
                        'auto_release_at' => app(\App\Support\BusinessDayCalculator::class)->addBusinessDays(now(), max(1, $days)),
                    ]);
                }
            }
        });

        $this->notifyBuyer(
            $order,
            'Order delivered',
            'Your order ' . $order->reference . ' has been marked as delivered. Confirm receipt to release payment.',
        );

        return true;
    }

    /**
     * Seller cancels a paid order they cannot fulfil (undeliverable address,
     * out of stock, technical issue, etc.). Refunds the buyer by returning the
     * held escrow to their wallet, restocks physical inventory, marks the order
     * Cancelled with the reason, and notifies the buyer. No-op if not cancellable.
     */
    public function cancelByVendor(Order $order, User $actor, string $reason): bool
    {
        if (! $order->canVendorCancel()) {
            return false;
        }

        DB::transaction(function () use ($order, $actor, $reason) {
            // Refund the buyer: release the held escrow back to their wallet.
            $escrow = $this->escrow->forPayable($order);
            if ($escrow && $escrow->canRefund()) {
                $this->escrow->refund($escrow, $actor, 'Order cancelled by seller: ' . $reason);
            }

            // Return any physical stock to inventory.
            if ($order->requires_shipping) {
                $this->restock->execute($order);
            }

            $order->update([
                'status'              => OrderStatus::Cancelled,
                'cancelled_at'        => now(),
                'cancellation_reason' => $reason,
                'cancelled_by'        => $actor->id,
            ]);
        });

        $this->notifyBuyer(
            $order,
            'Order cancelled by seller',
            'Your order ' . $order->reference . ' was cancelled by the seller and any payment has been refunded to your Volamani wallet. Reason: ' . $reason,
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
