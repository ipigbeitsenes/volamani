<?php

namespace App\Actions\Payment;

use App\Actions\Products\DecrementStockAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ServiceOrderStatus;
use App\Models\ConsultationSession;
use App\Models\Document;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ServiceOrder;
use App\Models\Subscription;
use App\Models\WalletFunding;
use App\Notifications\OrderDownloadReadyNotification;
use App\Services\Affiliate\AffiliateService;
use App\Services\Documents\DocumentService;
use App\Services\Escrow\EscrowService;
use App\Services\Subscription\SubscriptionService;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\Log;

/**
 * Single source of truth for what happens once a Payment is marked successful:
 * transition the underlying payable, open escrow, and record affiliate
 * conversions. Used by both gateway verification (VerifyPaymentAction) and the
 * internal wallet-settlement path (cart / wallet checkout), so the post-payment
 * side effects can never drift between the two.
 */
class FulfillPaymentAction
{
    public function __construct(
        private EscrowService $escrowService,
        private AffiliateService $affiliateService,
        private SubscriptionService $subscriptionService,
        private DocumentService $documentService,
        private WalletService $walletService,
    ) {}

    /**
     * Run all side effects for an already-successful payment. Idempotent at the
     * escrow layer (HoldEscrowAction is keyed per escrowable).
     */
    public function execute(Payment $payment): void
    {
        $this->fulfillPayable($payment);
        $this->affiliateService->recordConversion($payment);
    }

    private function fulfillPayable(Payment $payment): void
    {
        if (! $payment->payable_type || ! $payment->payable_id) {
            return;
        }

        $payable = $payment->payable;
        if (! $payable) {
            Log::warning("Payment {$payment->reference}: payable not found", [
                'type' => $payment->payable_type,
                'id' => $payment->payable_id,
            ]);

            return;
        }

        match (true) {
            $payable instanceof Order => $this->fulfillOrder($payable, $payment),
            $payable instanceof ServiceOrder => $this->fulfillServiceOrder($payable, $payment),
            $payable instanceof ConsultationSession => $this->fulfillConsultationSession($payable, $payment),
            $payable instanceof Subscription => $this->subscriptionService->activateFromPayment($payment),
            $payable instanceof Document => $this->documentService->settleFromPayment($payment),
            $payable instanceof WalletFunding => $this->fulfillWalletFunding($payable),
            default => null,
        };
    }

    private function fulfillOrder(Order $order, Payment $payment): void
    {
        $order->update([
            'payment_status' => PaymentStatus::Success,
            'status' => OrderStatus::Paid,
            'payment_reference' => $payment->gateway_reference,
            'payment_method' => $payment->gateway->value,
            'paid_at' => now(),
        ]);

        $order = $order->fresh();
        $this->escrowService->holdForPayable($order, $payment);

        if ($order->requires_shipping) {
            // Physical: draw down inventory; nothing to download.
            app(DecrementStockAction::class)->execute($order);

            return;
        }

        $this->notifyDownloadsReady($order);
    }

    /**
     * Email the buyer their download links (and drop an in-app notification) when
     * the paid order contains downloadable digital files. No-op for orders that
     * carry nothing to download.
     */
    private function notifyDownloadsReady(Order $order): void
    {
        $order->loadMissing('buyer', 'items.product.files');

        $hasDownloads = $order->items->contains(
            fn ($item) => $item->product
                && $item->product->is_downloadable
                && $item->product->files->isNotEmpty()
        );

        if ($hasDownloads && $order->buyer) {
            $order->buyer->notify(new OrderDownloadReadyNotification($order));
        }
    }

    private function fulfillServiceOrder(ServiceOrder $order, Payment $payment): void
    {
        $order->update([
            'payment_status' => PaymentStatus::Success,
            'status' => ServiceOrderStatus::Active,
            'payment_reference' => $payment->gateway_reference,
            'payment_method' => $payment->gateway->value,
            'paid_at' => now(),
        ]);

        $this->escrowService->holdForPayable($order->fresh(), $payment);
    }

    private function fulfillConsultationSession(ConsultationSession $session, Payment $payment): void
    {
        $session->update([
            'payment_status' => PaymentStatus::Success,
            'paid_at' => now(),
        ]);

        $this->escrowService->holdForPayable($session->fresh(), $payment);
    }

    private function fulfillWalletFunding(WalletFunding $funding): void
    {
        $this->walletService->completeFunding($funding);
    }
}
