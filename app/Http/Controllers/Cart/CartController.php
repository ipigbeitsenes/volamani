<?php

namespace App\Http\Controllers\Cart;

use App\Enums\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ServicePackage;
use App\Services\Cart\CartCheckoutService;
use App\Services\Cart\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CartController extends Controller
{
    public function __construct(
        private CartService         $cart,
        private CartCheckoutService $checkout,
    ) {}

    public function index()
    {
        return view('cart.index', ['summary' => $this->cart->summary()]);
    }

    // ─── Products ─────────────────────────────────────────────────────────────

    public function addProduct(Request $request, Product $product)
    {
        if (! $product->isActive()) {
            return back()->with('error', 'That product is not available.');
        }

        if (auth()->check() && $product->vendor?->user_id === auth()->id()) {
            return back()->with('error', 'You cannot add your own product to the cart.');
        }

        $qty = max(1, (int) $request->input('qty', 1));
        $this->cart->addProduct($product->id, $qty);

        return back()->with('success', $product->name . ' added to your cart.');
    }

    public function updateProduct(Request $request, Product $product)
    {
        $this->cart->setProductQty($product->id, (int) $request->input('qty', 1));

        return back()->with('success', 'Cart updated.');
    }

    public function removeProduct(Product $product)
    {
        $this->cart->removeProduct($product->id);

        return back()->with('success', 'Item removed from cart.');
    }

    // ─── Service packages ─────────────────────────────────────────────────────

    public function addService(ServicePackage $package)
    {
        $package->loadMissing('service.vendor');

        if (! $package->service || ! $package->service->isActive()) {
            return back()->with('error', 'That service is not available.');
        }

        if (auth()->check() && $package->service->vendor?->user_id === auth()->id()) {
            return back()->with('error', 'You cannot add your own service to the cart.');
        }

        $this->cart->addService($package->id);

        return back()->with('success', 'Service package added to your cart.');
    }

    public function removeService(ServicePackage $package)
    {
        $this->cart->removeService($package->id);

        return back()->with('success', 'Item removed from cart.');
    }

    public function clear()
    {
        $this->cart->clear();

        return back()->with('success', 'Cart cleared.');
    }

    // ─── Checkout ─────────────────────────────────────────────────────────────

    public function checkout()
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('info', 'Your cart is empty.');
        }

        $wallet = app(\App\Services\Wallet\WalletService::class)->getOrCreate(auth()->user());

        return view('cart.checkout', [
            'summary'       => $this->cart->summary(),
            'wallet'        => $wallet,
            'payableCount'  => $this->cart->payableCount(),
        ]);
    }

    public function process(Request $request)
    {
        $request->validate([
            'gateway' => ['required', 'in:wallet,paystack,bank_transfer'],
        ]);

        $gateway = $request->input('gateway');
        $user    = auth()->user();

        // Idempotency guard: an atomic per-user lock stops a double-click /
        // double-submit from settling the cart twice. The wallet path debits
        // instantly and irreversibly, so a duplicate must never get through.
        // The first request settles + clears the cart; a concurrent duplicate
        // can't acquire the lock, and a later replay finds the cart empty.
        $lock = Cache::lock("cart-checkout:{$user->id}", 30);

        if (! $lock->get()) {
            return back()->with('info', 'Your checkout is already being processed — please hold on a moment.');
        }

        try {
            return $gateway === PaymentGateway::Wallet->value
                ? $this->handleWalletCheckout($user)
                : $this->handleGatewayCheckout($user, $gateway);
        } finally {
            $lock->release();
        }
    }

    private function handleWalletCheckout($user)
    {
        $result = $this->checkout->checkoutWithWallet($user);

        return match ($result['status']) {
            'empty'        => redirect()->route('cart.index')->with('info', 'Your cart is empty.'),
            'own_item'     => back()->with('error', 'Your cart contains your own listing. Remove it to check out.'),
            'insufficient' => back()->with('error',
                'Insufficient wallet balance. You need ' . money($result['shortfall']) . ' more — fund your wallet or pay per item with card.'),
            'paid'         => redirect()->route('orders.index')->with('success',
                'Payment successful! ' . count($result['payables']) . ' order(s) placed.'),
            default        => back()->with('error', 'Checkout could not be completed.'),
        };
    }

    private function handleGatewayCheckout($user, string $gateway)
    {
        // Card / bank transfer — single payable only.
        $result = $this->checkout->checkoutWithGateway($user, $gateway);

        return match ($result['status']) {
            'empty'    => redirect()->route('cart.index')->with('info', 'Your cart is empty.'),
            'own_item' => back()->with('error', 'Your cart contains your own listing. Remove it to check out.'),
            'multi'    => back()->with('error',
                'Card/bank checkout covers one seller at a time. Pay with your wallet to check out everything at once, or buy items individually.'),
            'redirect' => $gateway === PaymentGateway::BankTransfer->value
                ? redirect()->route('checkout.bank-transfer', $result['result']['payment'])
                : redirect()->away($result['result']['authorization_url']),
            default    => back()->with('error', 'Checkout could not be completed.'),
        };
    }
}
