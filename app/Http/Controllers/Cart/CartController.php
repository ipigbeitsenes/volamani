<?php

namespace App\Http\Controllers\Cart;

use App\Enums\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ServicePackage;
use App\Services\Cart\CartCheckoutService;
use App\Services\Cart\CartService;
use App\Services\Wallet\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CartController extends Controller
{
    public function __construct(
        private CartService $cart,
        private CartCheckoutService $checkout,
    ) {}

    public function index()
    {
        return view('cart.index', [
            'summary' => $this->cart->summary(),
            'shipping' => $this->cart->physicalShippingTotal(),
            'grandTotal' => $this->cart->grandTotal(),
        ]);
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

        return back()->with('success', $product->name.' added to your cart.');
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

    // ─── Physical products ────────────────────────────────────────────────────

    public function addPhysical(Request $request, Product $product)
    {
        if (! $product->isActive() || ! $product->isPhysical()) {
            return back()->with('error', 'That product is not available.');
        }

        if (auth()->check() && $product->vendor?->user_id === auth()->id()) {
            return back()->with('error', 'You cannot add your own product to the cart.');
        }

        $product->loadMissing('variants', 'physicalDetail');
        $qty = max(1, (int) $request->input('qty', 1));
        $variantId = (int) $request->input('variant_id', 0);
        $variant = null;

        if ($product->hasVariants()) {
            $variant = $product->variants->firstWhere('id', $variantId);
            if (! $variant) {
                return redirect()->route('marketplace.products.show', $product->slug)
                    ->with('info', 'Please choose an option to add this item to your cart.');
            }
        }

        if (! $product->canFulfilQuantity($qty, $variant)) {
            return back()->with('error', 'Sorry, there isn\'t enough stock for that quantity.');
        }

        $this->cart->addPhysical($product->id, $variant?->id ?? 0, $qty);

        return back()->with('success', $product->name.' added to your cart.');
    }

    public function updatePhysical(Request $request, Product $product)
    {
        $this->cart->setPhysicalQty(
            $product->id,
            (int) $request->input('variant_id', 0),
            (int) $request->input('qty', 1),
        );

        return back()->with('success', 'Cart updated.');
    }

    public function removePhysical(Request $request, Product $product)
    {
        $this->cart->removePhysical($product->id, (int) $request->input('variant_id', 0));

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

        $wallet = app(WalletService::class)->getOrCreate(auth()->user());

        return view('cart.checkout', [
            'summary' => $this->cart->summary(),
            'wallet' => $wallet,
            'payableCount' => $this->cart->payableCount(),
            'hasPhysical' => $this->cart->hasPhysical(),
            'shipping' => $this->cart->physicalShippingTotal(),
            'grandTotal' => $this->cart->grandTotal(),
        ]);
    }

    public function process(Request $request)
    {
        $rules = ['gateway' => ['required', 'in:wallet,paystack,bank_transfer']];

        // A delivery address is required when the cart contains physical items.
        if ($this->cart->hasPhysical()) {
            $rules += [
                'ship_to_name' => ['required', 'string', 'max:255'],
                'ship_to_phone' => ['required', 'string', 'max:30'],
                'ship_to_address' => ['required', 'string', 'max:255'],
                'ship_to_city' => ['nullable', 'string', 'max:80'],
                'ship_to_state' => ['nullable', 'string', 'max:80'],
            ];
        }

        $request->validate($rules);

        $gateway = $request->input('gateway');
        $user = auth()->user();
        $address = $request->only('ship_to_name', 'ship_to_phone', 'ship_to_address', 'ship_to_city', 'ship_to_state');

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
                ? $this->handleWalletCheckout($user, $address)
                : $this->handleGatewayCheckout($user, $gateway, $address);
        } finally {
            $lock->release();
        }
    }

    private function handleWalletCheckout($user, array $address)
    {
        $result = $this->checkout->checkoutWithWallet($user, $address);

        return match ($result['status']) {
            'empty' => redirect()->route('cart.index')->with('info', 'Your cart is empty.'),
            'own_item' => back()->with('error', 'Your cart contains your own listing. Remove it to check out.'),
            'address_required' => back()->withInput()->with('error', 'Please enter a delivery address for the physical items in your cart.'),
            'unavailable' => back()->with('error', '"'.($result['item'] ?? 'An item').'" is out of stock. Adjust the quantity or remove it.'),
            'no_delivery' => back()->withInput()->with('error', 'The seller of "'.($result['item'] ?? 'an item').'" does not deliver to your address. Use a different address or remove the item.'),
            'insufficient' => back()->with('error',
                'Insufficient wallet balance. You need '.money($result['shortfall']).' more — fund your wallet or pay per item with card.'),
            'paid' => redirect()->route('orders.index')->with('success',
                'Payment successful! '.count($result['payables']).' order(s) placed.'),
            default => back()->with('error', 'Checkout could not be completed.'),
        };
    }

    private function handleGatewayCheckout($user, string $gateway, array $address)
    {
        // Card / bank transfer — single payable only.
        $result = $this->checkout->checkoutWithGateway($user, $gateway, $address);

        return match ($result['status']) {
            'empty' => redirect()->route('cart.index')->with('info', 'Your cart is empty.'),
            'own_item' => back()->with('error', 'Your cart contains your own listing. Remove it to check out.'),
            'address_required' => back()->withInput()->with('error', 'Please enter a delivery address for the physical items in your cart.'),
            'unavailable' => back()->with('error', '"'.($result['item'] ?? 'An item').'" is out of stock. Adjust the quantity or remove it.'),
            'no_delivery' => back()->withInput()->with('error', 'The seller of "'.($result['item'] ?? 'an item').'" does not deliver to your address. Use a different address or remove the item.'),
            'multi' => back()->with('error',
                'Card/bank checkout covers one seller at a time. Pay with your wallet to check out everything at once, or buy items individually.'),
            'redirect' => $gateway === PaymentGateway::BankTransfer->value
                ? redirect()->route('checkout.bank-transfer', $result['result']['payment'])
                : redirect()->away($result['result']['authorization_url']),
            default => back()->with('error', 'Checkout could not be completed.'),
        };
    }
}
