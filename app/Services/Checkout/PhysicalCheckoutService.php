<?php

namespace App\Services\Checkout;

use App\Actions\Payment\FulfillPaymentAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Payment\PaymentService;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;

/**
 * Direct "Buy Now" checkout for a single PHYSICAL product (variant + quantity +
 * shipping address). Creates a shipping Order, settles it via wallet or an
 * external gateway, and holds the funds in escrow with NO auto-release timer —
 * release happens on delivery confirmation (Phase 3 money model).
 */
class PhysicalCheckoutService
{
    public function __construct(
        private WalletService        $walletService,
        private FulfillPaymentAction $fulfillAction,
        private PaymentService       $paymentService,
    ) {}

    /**
     * @return array{status:string, order?:Order, redirect?:string, shortfall?:int}
     *   status ∈ own_item | unavailable | insufficient | paid | redirect
     */
    public function place(User $buyer, Product $product, ?ProductVariant $variant, int $qty, array $address, string $gateway): array
    {
        $qty = max(1, $qty);

        if (! $product->isPhysical() || ! $product->isActive()) {
            return ['status' => 'unavailable'];
        }

        if ($product->vendor && $product->vendor->user_id === $buyer->id) {
            return ['status' => 'own_item'];
        }

        if (! $this->canFulfil($product, $variant, $qty)) {
            return ['status' => 'unavailable'];
        }

        $unitPrice = $variant ? $variant->effectivePrice() : (int) $product->price;
        $subtotal  = $unitPrice * $qty;
        $shipping  = $product->vendor->shippingFeeFor($subtotal);
        $total     = $subtotal + $shipping;
        $feePct    = (float) config('payment.platform_fee_percent', 10);
        $fee       = (int) round($subtotal * $feePct / 100); // commission on goods only
        $earnings  = $total - $fee;

        // Wallet: verify funds before creating an order to avoid orphans.
        if ($gateway === PaymentGateway::Wallet->value) {
            $wallet = $this->walletService->getOrCreate($buyer);
            if (! $wallet->canWithdraw($total)) {
                return [
                    'status'    => 'insufficient',
                    'shortfall' => $total - $wallet->availableBalance(),
                ];
            }
        }

        $order = DB::transaction(function () use ($buyer, $product, $variant, $qty, $address, $unitPrice, $subtotal, $shipping, $total, $fee, $earnings) {
            $order = Order::create([
                'buyer_id'          => $buyer->id,
                'vendor_id'         => $product->vendor_id,
                'status'            => OrderStatus::Pending,
                'payment_status'    => PaymentStatus::Pending,
                'requires_shipping' => true,
                'total_amount'      => $total,
                'platform_fee'      => $fee,
                'vendor_earnings'   => $earnings,
                'shipping_fee'      => $shipping,
                'ship_to_name'      => $address['ship_to_name'],
                'ship_to_phone'     => $address['ship_to_phone'],
                'ship_to_address'   => $address['ship_to_address'],
                'ship_to_city'      => $address['ship_to_city'] ?? null,
                'ship_to_state'     => $address['ship_to_state'] ?? null,
                'currency'          => 'NGN',
            ]);

            $order->items()->create([
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'name'       => $product->name . ($variant ? ' — ' . $variant->name : ''),
                'type'       => 'product',
                'quantity'   => $qty,
                'unit_price' => $unitPrice,
                'subtotal'   => $subtotal,
            ]);

            return $order;
        });

        return $gateway === PaymentGateway::Wallet->value
            ? $this->settleWithWallet($buyer, $order)
            : $this->settleWithGateway($buyer, $order, $gateway);
    }

    private function settleWithWallet(User $buyer, Order $order): array
    {
        DB::transaction(function () use ($buyer, $order) {
            $wallet = $this->walletService->getOrCreate($buyer);
            $amount = (int) $order->total_amount;

            $payment = Payment::create([
                'user_id'           => $buyer->id,
                'payable_type'      => $order->getMorphClass(),
                'payable_id'        => $order->getKey(),
                'gateway'           => PaymentGateway::Wallet->value,
                'gateway_reference' => generate_reference('WAL'),
                'status'            => PaymentStatus::Success,
                'currency'          => 'NGN',
                'amount'            => $amount,
                'paid_at'           => now(),
                'ip_address'        => request()->ip(),
            ]);

            $this->walletService->debit(
                $wallet,
                $amount,
                TransactionType::Debit,
                "Purchase — order {$order->reference}",
                $order,
            );

            $this->fulfillAction->execute($payment);
        });

        return ['status' => 'paid', 'order' => $order->fresh()];
    }

    private function settleWithGateway(User $buyer, Order $order, string $gateway): array
    {
        $amount = (int) $order->total_amount;

        if ($gateway === PaymentGateway::BankTransfer->value) {
            $result = $this->paymentService->initiateBankTransferPayment($buyer, $amount, $order);

            return ['status' => 'redirect', 'redirect' => route('checkout.bank-transfer', $result['payment'])];
        }

        $result = $this->paymentService->initiatePaystackPayment($buyer, $amount, $order, [
            'payable_type' => $order->getMorphClass(),
        ]);

        return ['status' => 'redirect', 'redirect' => $result['authorization_url']];
    }

    private function canFulfil(Product $product, ?ProductVariant $variant, int $qty): bool
    {
        $detail = $product->physicalDetail;

        if ($detail && $detail->allow_backorder) {
            return true;
        }

        if ($variant) {
            return $variant->stock_quantity >= $qty;
        }

        if (! $detail || ! $detail->track_inventory) {
            return true;
        }

        return $detail->stock_quantity >= $qty;
    }
}
