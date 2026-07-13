<?php

namespace App\Services\Orders;

use App\Actions\Products\RestockOrderAction;
use App\Enums\EscrowStatus;
use App\Enums\NotificationCategory;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use App\Repositories\Orders\OrderRepository;
use App\Services\Escrow\EscrowService;
use App\Services\Notifications\NotificationService;
use App\Services\Wallet\WalletService;
use App\Support\BusinessDayCalculator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private OrderRepository $repo,
        private EscrowService $escrow,
        private NotificationService $notifications,
        private RestockOrderAction $restock,
        private WalletService $wallet,
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
        if ($order->isCompleted()) {
            return false;
        }

        // POD orders hold no escrow: the buyer paid the seller cash on delivery,
        // so completion just settles the platform's commission from the seller.
        if ($order->isPod()) {
            DB::transaction(function () use ($order) {
                $this->settlePodCommission($order);
                $order->update([
                    'status' => OrderStatus::Completed,
                    'completed_at' => now(),
                ]);
            });

            return true;
        }

        if (! $order->isPaid()) {
            return false;
        }

        DB::transaction(function () use ($order, $actor) {
            $order->update([
                'status' => OrderStatus::Completed,
                'completed_at' => now(),
            ]);

            $this->escrow->releaseForPayable($order, $actor);
        });

        return true;
    }

    /**
     * Settle the platform commission on a delivered Pay-on-Delivery order by
     * debiting the seller's wallet — but ONLY when the wallet subsystem is enabled.
     *
     * When BOTH wallet and escrow are toggled off, the platform runs on seller
     * subscriptions alone, so POD takes no commission at all (not even recorded as
     * owed) until one of those features is turned back on. When wallet is off but
     * escrow is on, or the seller can't cover the debit, the commission is recorded
     * as owed for finance to reconcile out of band. Idempotent: payment_status
     * flips to Success on first settlement and short-circuits any repeat.
     */
    private function settlePodCommission(Order $order): void
    {
        if ($order->isPaid()) {
            return; // already settled
        }

        $order->update([
            'payment_status' => PaymentStatus::Success,
            'paid_at' => $order->paid_at ?? now(),
        ]);

        // Subscription-only mode: no commission on POD while both wallet and escrow
        // are disabled — sellers already pay to be on the platform.
        if (! feature('wallet') && ! feature('escrow')) {
            return;
        }

        $commission = (int) $order->platform_fee;
        $vendor = $order->vendor;
        $owner = $vendor instanceof Vendor ? $vendor->user : null;

        if ($commission <= 0 || ! $owner instanceof User) {
            return;
        }

        // Collect via the wallet only when that subsystem is on and can cover it;
        // otherwise POD stays wallet-independent and the debt is logged.
        if (feature('wallet')) {
            $wallet = $this->wallet->getOrCreate($owner);

            if ($wallet->canWithdraw($commission)) {
                $this->wallet->debit(
                    $wallet,
                    $commission,
                    TransactionType::Commission,
                    "Platform commission — cash-on-delivery order {$order->reference}",
                    $order,
                );

                return;
            }
        }

        $this->recordCommissionOwed($order, $owner, $commission);
    }

    /**
     * Record an uncollected POD commission as owed (note on the order + seller
     * notification) instead of debiting a wallet. Used when the wallet feature is
     * off, or when the seller's balance can't cover the debit.
     */
    private function recordCommissionOwed(Order $order, User $owner, int $commission): void
    {
        $walletOn = feature('wallet');

        $reason = $walletOn ? ' (insufficient wallet balance)' : '';
        $note = '['.now()->format('d M Y H:i').'] Platform commission of '.money($commission).' due on this pay-on-delivery order'.$reason.'.';
        $order->update([
            'notes' => trim(($order->notes ? $order->notes."\n" : '').$note),
        ]);

        [$closing, $url, $label] = $walletOn
            ? ['Please top up your wallet to clear it.', route('vendor.wallet.index'), 'Top up wallet']
            : ['Our team will reconcile it with you.', route('vendor.dashboard'), 'View dashboard'];

        $this->notifications->send(
            $owner,
            NotificationCategory::Payments,
            'Commission due',
            'A platform commission of '.money($commission).' is due on your delivered pay-on-delivery order '.$order->reference.'. '.$closing,
            $url,
            $label,
        );
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
            'status' => OrderStatus::Shipped,
            'tracking_number' => $trackingNumber,
            'courier' => $courier,
            'shipped_at' => now(),
        ]);

        $tracking = $trackingNumber ? " Tracking: {$trackingNumber}".($courier ? " ({$courier})" : '').'.' : '';
        $this->notifyBuyer(
            $order,
            'Order shipped',
            'Your order '.$order->reference.' is on its way.'.$tracking,
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
        if ((! $order->isPaid() && ! $order->isPod()) || in_array($order->status, [OrderStatus::Completed, OrderStatus::Delivered, OrderStatus::Cancelled, OrderStatus::Refunded], true)) {
            return false;
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'status' => OrderStatus::Delivered,
                'delivered_at' => now(),
            ]);

            // POD: cash was collected on delivery — take the platform's cut now.
            if ($order->isPod()) {
                $this->settlePodCommission($order);

                return;
            }

            if ($order->requires_shipping) {
                $escrow = $this->escrow->forPayable($order);
                if ($escrow && $escrow->auto_release_at === null && $escrow->status === EscrowStatus::Holding) {
                    $days = (int) config('business_days.release_days', 3);
                    $escrow->update([
                        'auto_release_at' => app(BusinessDayCalculator::class)->addBusinessDays(now(), max(1, $days)),
                    ]);
                }
            }
        });

        $this->notifyBuyer(
            $order,
            'Order delivered',
            $order->isPod()
                ? 'Your order '.$order->reference.' has been marked as delivered. Thanks for shopping with us!'
                : 'Your order '.$order->reference.' has been marked as delivered. Confirm receipt to release payment.',
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
                $this->escrow->refund($escrow, $actor, 'Order cancelled by seller: '.$reason);
            }

            // Return any physical stock to inventory.
            if ($order->requires_shipping) {
                $this->restock->execute($order);
            }

            $order->update([
                'status' => OrderStatus::Cancelled,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'cancelled_by' => $actor->id,
            ]);
        });

        $this->notifyBuyer(
            $order,
            'Order cancelled by seller',
            'Your order '.$order->reference.' was cancelled by the seller and any payment has been refunded to your Volamani wallet. Reason: '.$reason,
        );

        return true;
    }

    /** Attach a deliverable file to an order (custom work the buyer ordered). */
    public function attachDeliverable(Order $order, UploadedFile $file): string
    {
        $path = $file->store('order-deliverables/'.$order->id, 'public');

        $note = '['.now()->format('d M Y H:i').'] Deliverable uploaded: '.$file->getClientOriginalName().' ('.$path.')';
        $order->update([
            'notes' => trim(($order->notes ? $order->notes."\n" : '').$note),
        ]);

        $this->notifyBuyer(
            $order,
            'New deliverable available',
            'The seller uploaded a file for your order '.$order->reference.'.',
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
