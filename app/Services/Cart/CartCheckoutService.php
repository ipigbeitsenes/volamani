<?php

namespace App\Services\Cart;

use App\Actions\Payment\FulfillPaymentAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ServicePackage;
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
        private CartService           $cart,
        private WalletService         $walletService,
        private ServiceListingService $serviceListing,
        private FulfillPaymentAction  $fulfillAction,
        private PaymentService        $paymentService,
    ) {}

    /**
     * Pay for the entire cart from the buyer's wallet.
     *
     * @return array{status:string, payables?:array, total?:int, shortfall?:int}
     *   status ∈ empty | own_item | insufficient | paid
     */
    public function checkoutWithWallet(User $buyer): array
    {
        $lines = $this->cart->lines();
        if (! $lines) {
            return ['status' => 'empty'];
        }

        // Block buying your own listings.
        foreach ($lines as $line) {
            if ($line['vendor'] && $line['vendor']->user_id === $buyer->id) {
                return ['status' => 'own_item'];
            }
        }

        $total  = array_sum(array_column($lines, 'subtotal'));
        $wallet = $this->walletService->getOrCreate($buyer);

        if (! $wallet->canWithdraw($total)) {
            return [
                'status'    => 'insufficient',
                'total'     => $total,
                'shortfall' => $total - $wallet->availableBalance(),
            ];
        }

        $payables = DB::transaction(function () use ($lines, $buyer, $wallet) {
            $payables = $this->createPayables($lines, $buyer);

            foreach ($payables as $payable) {
                $amount  = (int) $payable->total_amount;
                $payment = Payment::create([
                    'user_id'           => $buyer->id,
                    'payable_type'      => $payable->getMorphClass(),
                    'payable_id'        => $payable->getKey(),
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
     *   status ∈ empty | own_item | multi | redirect
     */
    public function checkoutWithGateway(User $buyer, string $gateway): array
    {
        $lines = $this->cart->lines();
        if (! $lines) {
            return ['status' => 'empty'];
        }

        foreach ($lines as $line) {
            if ($line['vendor'] && $line['vendor']->user_id === $buyer->id) {
                return ['status' => 'own_item'];
            }
        }

        if ($this->cart->payableCount() !== 1) {
            return ['status' => 'multi'];
        }

        $payable = DB::transaction(fn () => $this->createPayables($lines, $buyer)[0]);
        $amount  = (int) $payable->total_amount;

        $result = $gateway === PaymentGateway::BankTransfer->value
            ? $this->paymentService->initiateBankTransferPayment($buyer, $amount, $payable)
            : $this->paymentService->initiatePaystackPayment($buyer, $amount, $payable, [
                'payable_type' => $payable->getMorphClass(),
            ]);

        $this->cart->clear();

        return ['status' => 'redirect', 'gateway' => $gateway, 'result' => $result];
    }

    /**
     * Build Orders (products grouped per vendor) + ServiceOrders (one each).
     *
     * @return array<int, Model>  freshly created pending payables
     */
    private function createPayables(array $lines, User $buyer): array
    {
        $payables   = [];
        $byVendor   = [];

        foreach ($lines as $line) {
            if ($line['kind'] === 'product') {
                $byVendor[$line['vendor']->id][] = $line;
            } else {
                // each service package → its own ServiceOrder
                $package = $line['model'];
                $payables[] = $this->serviceListing->placeOrder(
                    $package->service,
                    $package,
                    $buyer,
                );
            }
        }

        foreach ($byVendor as $vendorId => $vendorLines) {
            $payables[] = $this->createProductOrder($buyer, $vendorId, $vendorLines);
        }

        return $payables;
    }

    private function createProductOrder(User $buyer, int $vendorId, array $lines): Order
    {
        $total      = array_sum(array_column($lines, 'subtotal'));
        $feePercent = (float) config('payment.platform_fee_percent', 10);
        $fee        = (int) round($total * $feePercent / 100);

        $order = Order::create([
            'buyer_id'        => $buyer->id,
            'vendor_id'       => $vendorId,
            'status'          => OrderStatus::Pending,
            'payment_status'  => PaymentStatus::Pending,
            'total_amount'    => $total,
            'platform_fee'    => $fee,
            'vendor_earnings' => $total - $fee,
            'currency'        => 'NGN',
        ]);

        foreach ($lines as $line) {
            $order->items()->create([
                'product_id' => $line['id'],
                'name'       => $line['name'],
                'type'       => 'product',
                'quantity'   => $line['qty'],
                'unit_price' => $line['unit_price'],
                'subtotal'   => $line['subtotal'],
            ]);
        }

        return $order;
    }
}
