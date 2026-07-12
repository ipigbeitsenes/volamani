<?php

namespace App\Services\Cart;

use App\Actions\Payment\FulfillPaymentAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payment\PaymentService;
use App\Services\Services\ServiceListingService;
use App\Services\Wallet\WalletService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Turns a cart into payables and settles them.
 *
 * Multi-vendor model: digital products are grouped into one Order per vendor;
 * each service package becomes its own ServiceOrder (reusing the normal
 * placement flow). The whole basket is then settled in a single wallet
 * transaction — the buyer is debited once per payable and each vendor's escrow
 * is opened — so "buy many items at a go" stays atomic and consistent with the
 * existing one-payment-one-order-one-escrow money model.
 */
class CartCheckoutService
{
    public function __construct(
        private CartService $cart,
        private WalletService $walletService,
        private ServiceListingService $serviceListing,
        private FulfillPaymentAction $fulfillAction,
        private PaymentService $paymentService,
    ) {}

    /**
     * Pay for the entire cart from the buyer's wallet.
     *
     * @param  array|null  $address  Shipping address (required when the cart has physical items)
     * @return array{status:string, payables?:array, total?:int, shortfall?:int, item?:string}
     *                                                                                         status ∈ empty | own_item | address_required | unavailable | insufficient | paid
     */
    public function checkoutWithWallet(User $buyer, ?array $address = null): array
    {
        $lines = $this->cart->lines();
        if (! $lines) {
            return ['status' => 'empty'];
        }

        if ($guard = $this->preCheck($lines, $buyer, $address)) {
            return $guard;
        }

        $total = $this->cart->grandTotal(); // items + physical shipping
        $wallet = $this->walletService->getOrCreate($buyer);

        if (! $wallet->canWithdraw($total)) {
            return [
                'status' => 'insufficient',
                'total' => $total,
                'shortfall' => $total - $wallet->availableBalance(),
            ];
        }

        $payables = DB::transaction(function () use ($lines, $buyer, $wallet, $address) {
            $payables = $this->createPayables($lines, $buyer, $address);

            foreach ($payables as $payable) {
                $amount = (int) $payable->total_amount;
                $payment = Payment::create([
                    'user_id' => $buyer->id,
                    'payable_type' => $payable->getMorphClass(),
                    'payable_id' => $payable->getKey(),
                    'gateway' => PaymentGateway::Wallet->value,
                    'gateway_reference' => generate_reference('WAL'),
                    'status' => PaymentStatus::Success,
                    'currency' => currency_code(),
                    'amount' => $amount,
                    'paid_at' => now(),
                    'ip_address' => request()->ip(),
                ]);

                $this->walletService->debit(
                    $wallet,
                    $amount,
                    TransactionType::Debit,
                    "Purchase — order {$payable->reference}",
                    $payable,
                );

                // Open escrow / transition the payable (shared with gateway flow).
                $this->fulfillAction->execute($payment);
            }

            return $payables;
        });

        $this->cart->clear();

        return ['status' => 'paid', 'payables' => $payables, 'total' => $total];
    }

    /**
     * Pay for a cart via an external gateway (Paystack / bank transfer). A
     * gateway redirect can only settle ONE payment, so this is restricted to
     * carts that resolve to a single payable (multi-vendor baskets must use the
     * wallet). Creates the payable, initiates the gateway, and returns where to
     * send the buyer.
     *
     * @return array{status:string, gateway?:string, result?:array}
     *                                                              status ∈ empty | own_item | multi | redirect
     */
    public function checkoutWithGateway(User $buyer, string $gateway, ?array $address = null): array
    {
        $lines = $this->cart->lines();
        if (! $lines) {
            return ['status' => 'empty'];
        }

        if ($this->cart->payableCount() !== 1) {
            return ['status' => 'multi'];
        }

        if ($guard = $this->preCheck($lines, $buyer, $address)) {
            return $guard;
        }

        $payable = DB::transaction(fn () => $this->createPayables($lines, $buyer, $address)[0]);
        $amount = (int) $payable->total_amount;

        $result = $gateway === PaymentGateway::BankTransfer->value
            ? $this->paymentService->initiateBankTransferPayment($buyer, $amount, $payable)
            : $this->paymentService->initiatePaystackPayment($buyer, $amount, $payable, [
                'payable_type' => $payable->getMorphClass(),
            ]);

        $this->cart->clear();

        return ['status' => 'redirect', 'gateway' => $gateway, 'result' => $result];
    }

    /**
     * Shared guards for both checkout paths: reject own listings, require a
     * shipping address when physical items are present, and block out-of-stock
     * physical lines. Returns a status array to short-circuit, or null to proceed.
     */
    private function preCheck(array $lines, User $buyer, ?array $address): ?array
    {
        $hasPhysical = false;

        foreach ($lines as $line) {
            if ($line['vendor'] && $line['vendor']->user_id === $buyer->id) {
                return ['status' => 'own_item'];
            }
            if ($line['kind'] === 'physical') {
                $hasPhysical = true;
                if (! ($line['in_stock'] ?? true)) {
                    return ['status' => 'unavailable', 'item' => $line['name']];
                }
            }
        }

        if ($hasPhysical && ! $this->hasAddress($address)) {
            return ['status' => 'address_required'];
        }

        // Block physical items whose seller doesn't deliver to the buyer's address.
        if ($hasPhysical) {
            foreach ($lines as $line) {
                if ($line['kind'] === 'physical' && $line['vendor']
                    && ! $line['vendor']->deliversTo($address['ship_to_state'] ?? null, $address['ship_to_city'] ?? null)) {
                    return ['status' => 'no_delivery', 'item' => $line['name']];
                }
            }
        }

        return null;
    }

    private function hasAddress(?array $address): bool
    {
        return $address
            && ! empty($address['ship_to_name'])
            && ! empty($address['ship_to_phone'])
            && ! empty($address['ship_to_address']);
    }

    /**
     * Build Orders (digital products per vendor + physical products per vendor)
     * + ServiceOrders (one each). Digital and physical from the same vendor are
     * separate orders so escrow/shipping stay coherent.
     *
     * @return array<int, Model> freshly created pending payables
     */
    private function createPayables(array $lines, User $buyer, ?array $address = null): array
    {
        $payables = [];
        $digital = [];
        $physical = [];

        foreach ($lines as $line) {
            if ($line['kind'] === 'product') {
                $digital[$line['vendor']->id][] = $line;
            } elseif ($line['kind'] === 'physical') {
                $physical[$line['vendor']->id][] = $line;
            } else {
                $package = $line['model'];
                $payables[] = $this->serviceListing->placeOrder($package->service, $package, $buyer);
            }
        }

        foreach ($digital as $vendorId => $vendorLines) {
            $payables[] = $this->createProductOrder($buyer, $vendorId, $vendorLines);
        }

        foreach ($physical as $vendorId => $vendorLines) {
            $payables[] = $this->createPhysicalOrder($buyer, $vendorLines[0]['vendor'], $vendorLines, $address);
        }

        return $payables;
    }

    private function createPhysicalOrder(User $buyer, $vendor, array $lines, ?array $address): Order
    {
        $subtotal = array_sum(array_column($lines, 'subtotal'));
        $shipping = $vendor->shippingFeeFor($subtotal);
        $total = $subtotal + $shipping;
        $feePercent = (float) config('payment.platform_fee_percent', 10);
        $fee = (int) round($subtotal * $feePercent / 100); // commission on goods only
        $address ??= [];

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'vendor_id' => $vendor->id,
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Pending,
            'requires_shipping' => true,
            'total_amount' => $total,
            'platform_fee' => $fee,
            'vendor_earnings' => $total - $fee,
            'shipping_fee' => $shipping,
            'ship_to_name' => $address['ship_to_name'] ?? null,
            'ship_to_phone' => $address['ship_to_phone'] ?? null,
            'ship_to_address' => $address['ship_to_address'] ?? null,
            'ship_to_city' => $address['ship_to_city'] ?? null,
            'ship_to_state' => $address['ship_to_state'] ?? null,
            'currency' => currency_code(),
        ]);

        foreach ($lines as $line) {
            $order->items()->create([
                'product_id' => $line['id'],
                'variant_id' => $line['variant_id'] ?: null,
                'name' => $line['name'],
                'type' => 'product',
                'quantity' => $line['qty'],
                'unit_price' => $line['unit_price'],
                'subtotal' => $line['subtotal'],
            ]);
        }

        return $order;
    }

    private function createProductOrder(User $buyer, int $vendorId, array $lines): Order
    {
        $total = array_sum(array_column($lines, 'subtotal'));
        $feePercent = (float) config('payment.platform_fee_percent', 10);
        $fee = (int) round($total * $feePercent / 100);

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'vendor_id' => $vendorId,
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Pending,
            'total_amount' => $total,
            'platform_fee' => $fee,
            'vendor_earnings' => $total - $fee,
            'currency' => currency_code(),
        ]);

        foreach ($lines as $line) {
            $order->items()->create([
                'product_id' => $line['id'],
                'name' => $line['name'],
                'type' => 'product',
                'quantity' => $line['qty'],
                'unit_price' => $line['unit_price'],
                'subtotal' => $line['subtotal'],
            ]);
        }

        return $order;
    }
}
