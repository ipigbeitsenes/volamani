<?php

namespace App\Http\Controllers\Payment;

use App\Enums\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Checkout\PhysicalCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhysicalCheckoutController extends Controller
{
    public function __construct(private PhysicalCheckoutService $checkout) {}

    public function show(Product $product): View|RedirectResponse
    {
        if (! $product->isPhysical() || ! $product->isActive()) {
            abort(404);
        }

        $product->load(['vendor', 'variants', 'physicalDetail']);

        if ($product->vendor->user_id === auth()->id()) {
            return redirect()->route('marketplace.products.show', $product->slug)
                ->with('error', 'You cannot buy your own product.');
        }

        if (! $product->inStock()) {
            return redirect()->route('marketplace.products.show', $product->slug)
                ->with('error', 'This product is currently out of stock.');
        }

        return view('marketplace.checkout.physical', [
            'product'  => $product,
            'gateways' => PaymentGateway::cases(),
        ]);
    }

    public function process(Request $request, Product $product): RedirectResponse
    {
        if (! $product->isPhysical() || ! $product->isActive()) {
            abort(404);
        }

        $data = $request->validate([
            'variant_id'      => ['nullable', 'integer', 'exists:product_variants,id'],
            'quantity'        => ['required', 'integer', 'min:1', 'max:999'],
            'ship_to_name'    => ['required', 'string', 'max:255'],
            'ship_to_phone'   => ['required', 'string', 'max:30'],
            'ship_to_address' => ['required', 'string', 'max:255'],
            'ship_to_city'    => ['nullable', 'string', 'max:80'],
            'ship_to_state'   => ['nullable', 'string', 'max:80'],
            'gateway'         => ['required', 'in:wallet,paystack,bank_transfer'],
        ]);

        $product->load(['vendor', 'variants', 'physicalDetail']);

        // Resolve + validate the variant belongs to this product (when it has any).
        $variant = null;
        if ($product->hasVariants()) {
            $variant = $product->variants->firstWhere('id', (int) ($data['variant_id'] ?? 0));
            if (! $variant) {
                return back()->withInput()->with('error', 'Please choose a valid option.');
            }
        }

        $result = $this->checkout->place(
            auth()->user(),
            $product,
            $variant,
            (int) $data['quantity'],
            $data,
            $data['gateway'],
        );

        return match ($result['status']) {
            'paid'        => redirect()->route('orders.show', $result['order'])
                                ->with('success', 'Order placed! The seller will ship it to you shortly.'),
            'redirect'    => redirect()->away($result['redirect']),
            'own_item'    => back()->with('error', 'You cannot buy your own product.'),
            'no_delivery' => back()->withInput()->with('error', 'Sorry, this seller does not deliver to ' . ($result['location'] ?? 'your location') . '. Try a different delivery address.'),
            'insufficient'=> back()->withInput()->with('error', 'Insufficient wallet balance. Top up or choose another payment method.'),
            default       => back()->withInput()->with('error', 'This product is currently unavailable or out of stock.'),
        };
    }
}
