<?php

namespace App\Http\Controllers\Payment;

use App\Enums\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Models\ConsultantProfile;
use App\Models\ConsultationPackage;
use App\Models\ConsultationSession;
use App\Models\FreelanceService;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ServiceOrder;
use App\Repositories\Payment\PaymentRepository;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        private PaymentService    $paymentService,
        private PaymentRepository $repository,
    ) {}

    // ─── Product Checkout ─────────────────────────────────────────────────────

    public function product(Product $product)
    {
        abort_unless($product->isActive(), 404);

        if ($product->hasPurchased(auth()->user())) {
            return redirect()->route('orders.index')
                ->with('info', 'You already own this product — find it under your orders.');
        }

        $gateways = PaymentGateway::cases();

        return view('marketplace.checkout.product', compact('product', 'gateways'));
    }

    // ─── Service Order Checkout ───────────────────────────────────────────────

    public function serviceOrder(ServiceOrder $serviceOrder)
    {
        abort_unless($serviceOrder->buyer_id === auth()->id(), 403);

        if ($serviceOrder->isPaid()) {
            return redirect()->route('service-orders.show', $serviceOrder);
        }

        $gateways = PaymentGateway::cases();

        return view('marketplace.checkout.service-order', compact('serviceOrder', 'gateways'));
    }

    // ─── Consultation Checkout ────────────────────────────────────────────────

    public function consultation(ConsultationSession $session)
    {
        abort_unless($session->buyer_id === auth()->id(), 403);

        if ($session->isPaid()) {
            return redirect()->route('consultations.sessions.show', $session);
        }

        $gateways = PaymentGateway::cases();

        return view('marketplace.checkout.consultation', compact('session', 'gateways'));
    }

    // ─── Universal Process ────────────────────────────────────────────────────

    public function process(Request $request)
    {
        $request->validate([
            'payable_type' => ['required', 'string', 'in:product,service_order,consultation'],
            'payable_id'   => ['required', 'integer'],
            'gateway'      => ['required', 'in:paystack,bank_transfer'],
        ]);

        $user    = auth()->user();
        $gateway = $request->gateway;

        [$payable, $amountKobo] = $this->resolvePayable($request->payable_type, $request->payable_id, $user);

        if ($gateway === 'paystack') {
            $result = $this->paymentService->initiatePaystackPayment($user, $amountKobo, $payable, [
                'payable_type' => $request->payable_type,
            ]);
            return redirect($result['authorization_url']);
        }

        // Bank transfer
        $result = $this->paymentService->initiateBankTransferPayment($user, $amountKobo, $payable);
        return redirect()->route('checkout.bank-transfer', $result['payment']);
    }

    // ─── Paystack Callback ────────────────────────────────────────────────────

    public function callback(Request $request)
    {
        $reference = $request->query('reference') ?? $request->query('trxref');
        abort_if(!$reference, 400);

        $payment = $this->paymentService->verifyByReference($reference);

        if (!$payment || !$payment->isSuccessful()) {
            return redirect()->route('checkout.failed')->with('error', 'Payment could not be verified. Please contact support.');
        }

        return redirect()->route('checkout.success', ['ref' => $payment->reference]);
    }

    // ─── Success ──────────────────────────────────────────────────────────────

    public function success(Request $request)
    {
        $payment = $request->ref
            ? $this->repository->findByReference($request->ref)
            : null;

        return view('marketplace.checkout.success', compact('payment'));
    }

    public function failed()
    {
        return view('marketplace.checkout.failed');
    }

    // ─── Bank Transfer ────────────────────────────────────────────────────────

    public function bankTransfer(Payment $payment)
    {
        abort_unless($payment->user_id === auth()->id(), 403);
        $bankDetails = config('payment.bank_transfer');
        $proof       = $payment->bankTransferProof()->latest()->first();

        return view('marketplace.checkout.bank-transfer', compact('payment', 'bankDetails', 'proof'));
    }

    public function pending(Payment $payment)
    {
        abort_unless($payment->user_id === auth()->id(), 403);
        return view('marketplace.checkout.pending', compact('payment'));
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function resolvePayable(string $type, int $id, $user): array
    {
        return match ($type) {
            'product' => $this->resolveProductPayable($id, $user),
            'service_order' => $this->resolveServiceOrderPayable($id, $user),
            'consultation' => $this->resolveConsultationPayable($id, $user),
        };
    }

    private function resolveProductPayable(int $productId, $user): array
    {
        $product = Product::findOrFail($productId);
        abort_unless($product->isActive(), 422);

        // Create or retrieve existing pending order
        $order = Order::firstOrCreate(
            ['buyer_id' => $user->id, 'status' => 'pending', 'payment_status' => 'pending'],
            [
                'vendor_id'       => $product->vendor_id,
                'total_amount'    => $product->price,
                'platform_fee'    => (int) round($product->price * (config('payment.platform_fee_percent') / 100)),
                'vendor_earnings' => $product->price - (int) round($product->price * (config('payment.platform_fee_percent') / 100)),
                'currency'        => 'NGN',
            ]
        );

        if (! $order->items()->exists()) {
            $order->items()->create([
                'product_id' => $productId,
                'name'       => $product->name,
                'type'       => 'product',
                'quantity'   => 1,
                'unit_price' => $product->price,
                'subtotal'   => $product->price,
            ]);
        }

        return [$order, $product->price];
    }

    private function resolveServiceOrderPayable(int $orderId, $user): array
    {
        $serviceOrder = ServiceOrder::findOrFail($orderId);
        abort_unless($serviceOrder->buyer_id === $user->id, 403);
        abort_if($serviceOrder->isPaid(), 422, 'Already paid.');
        return [$serviceOrder, $serviceOrder->total_amount];
    }

    private function resolveConsultationPayable(int $sessionId, $user): array
    {
        $session = ConsultationSession::findOrFail($sessionId);
        abort_unless($session->buyer_id === $user->id, 403);
        abort_if($session->isPaid(), 422, 'Already paid.');
        return [$session, $session->price];
    }
}
