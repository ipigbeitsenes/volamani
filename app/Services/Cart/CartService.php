<?php

namespace App\Services\Cart;

use App\Models\Product;
use App\Models\ServicePackage;
use Illuminate\Support\Facades\Session;

/**
 * Session-backed shopping cart. Holds:
 *   - digital products (qty)            → 'products' => [productId => qty]
 *   - service packages (qty always 1)   → 'services' => [packageId => true]
 *   - physical products (qty, variant)  → 'physical' => ["{productId}:{variantId}" => qty]
 *     (variantId 0 means "no variant").
 *
 * Resolution to models happens lazily on read; anything that no longer exists /
 * is inactive is silently dropped so the cart can never render or check out
 * stale items. Physical lines additionally carry shipping (flat per vendor),
 * applied at checkout — see CartCheckoutService.
 */
class CartService
{
    private const KEY = 'cart';

    // ─── Mutators: digital products ───────────────────────────────────────────

    public function addProduct(int $productId, int $qty = 1): void
    {
        // Physical products live in the 'physical' bucket (need a variant + ship).
        if (Product::whereKey($productId)->physical()->exists()) {
            return;
        }

        $cart = $this->raw();
        $cart['products'][$productId] = max(1, ($cart['products'][$productId] ?? 0) + $qty);
        $this->put($cart);
    }

    public function setProductQty(int $productId, int $qty): void
    {
        $cart = $this->raw();
        if ($qty < 1) {
            unset($cart['products'][$productId]);
        } else {
            $cart['products'][$productId] = $qty;
        }
        $this->put($cart);
    }

    public function removeProduct(int $productId): void
    {
        $cart = $this->raw();
        unset($cart['products'][$productId]);
        $this->put($cart);
    }

    // ─── Mutators: physical products ──────────────────────────────────────────

    public function addPhysical(int $productId, int $variantId = 0, int $qty = 1): void
    {
        $key = $this->physicalKey($productId, $variantId);
        $cart = $this->raw();
        $cart['physical'][$key] = max(1, ($cart['physical'][$key] ?? 0) + $qty);
        $this->put($cart);
    }

    public function setPhysicalQty(int $productId, int $variantId, int $qty): void
    {
        $key = $this->physicalKey($productId, $variantId);
        $cart = $this->raw();
        if ($qty < 1) {
            unset($cart['physical'][$key]);
        } else {
            $cart['physical'][$key] = $qty;
        }
        $this->put($cart);
    }

    public function removePhysical(int $productId, int $variantId): void
    {
        $cart = $this->raw();
        unset($cart['physical'][$this->physicalKey($productId, $variantId)]);
        $this->put($cart);
    }

    // ─── Mutators: services ───────────────────────────────────────────────────

    public function addService(int $packageId): void
    {
        $cart = $this->raw();
        $cart['services'][$packageId] = true;
        $this->put($cart);
    }

    public function removeService(int $packageId): void
    {
        $cart = $this->raw();
        unset($cart['services'][$packageId]);
        $this->put($cart);
    }

    public function clear(): void
    {
        Session::forget(self::KEY);
    }

    // ─── Reads ────────────────────────────────────────────────────────────────

    public function count(): int
    {
        $cart = $this->raw();

        return array_sum($cart['products']) + array_sum($cart['physical']) + count($cart['services']);
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function hasPhysical(): bool
    {
        foreach ($this->lines() as $line) {
            if ($line['kind'] === 'physical') {
                return true;
            }
        }

        return false;
    }

    /**
     * Flat list of resolved, still-valid line items. Each line:
     *   ['kind' => 'product'|'physical'|'service', 'id' => int, 'model' => Model,
     *    'product' => Product|null, 'variant' => ProductVariant|null,
     *    'variant_id' => int, 'vendor' => Vendor|null, 'name' => string,
     *    'qty' => int, 'unit_price' => int(kobo), 'subtotal' => int(kobo),
     *    'in_stock' => bool]
     */
    public function lines(): array
    {
        $cart = $this->raw();
        $lines = [];

        // Digital products
        if ($cart['products']) {
            $products = Product::with('vendor')->whereIn('id', array_keys($cart['products']))->get()->keyBy('id');
            foreach ($cart['products'] as $id => $qty) {
                $product = $products->get($id);
                if (! $product || ! $product->isActive() || ! $product->isDigital()) {
                    continue;
                }
                $qty = max(1, (int) $qty);
                $lines[] = [
                    'kind' => 'product', 'id' => $product->id, 'model' => $product,
                    'product' => $product, 'variant' => null, 'variant_id' => 0,
                    'vendor' => $product->vendor, 'name' => $product->name, 'qty' => $qty,
                    'unit_price' => $product->price, 'subtotal' => $product->price * $qty,
                    'in_stock' => true,
                ];
            }
        }

        // Physical products (keyed by product:variant)
        if ($cart['physical']) {
            $pids = [];
            foreach (array_keys($cart['physical']) as $key) {
                [$pid] = explode(':', $key);
                $pids[(int) $pid] = true;
            }
            $products = Product::with('vendor', 'physicalDetail', 'variants')
                ->whereIn('id', array_keys($pids))->get()->keyBy('id');

            foreach ($cart['physical'] as $key => $qty) {
                [$pid, $vid] = array_map('intval', explode(':', $key));
                $product = $products->get($pid);
                if (! $product || ! $product->isActive() || ! $product->isPhysical()) {
                    continue;
                }

                $variant = null;
                if ($vid > 0) {
                    $variant = $product->variants->firstWhere('id', $vid);
                    if (! $variant || ! $variant->is_active) {
                        continue; // variant gone — drop the stale line
                    }
                }

                $qty = max(1, (int) $qty);
                $unitPrice = $variant ? $variant->effectivePrice() : (int) $product->price;
                $lines[] = [
                    'kind' => 'physical', 'id' => $product->id, 'model' => $product,
                    'product' => $product, 'variant' => $variant, 'variant_id' => $vid,
                    'vendor' => $product->vendor,
                    'name' => $product->name.($variant ? ' — '.$variant->name : ''),
                    'qty' => $qty, 'unit_price' => $unitPrice, 'subtotal' => $unitPrice * $qty,
                    'in_stock' => $product->canFulfilQuantity($qty, $variant),
                ];
            }
        }

        // Services
        if ($cart['services']) {
            $packages = ServicePackage::with('service.vendor')->whereIn('id', array_keys($cart['services']))->get()->keyBy('id');
            foreach (array_keys($cart['services']) as $id) {
                $package = $packages->get($id);
                if (! $package || ! $package->service || ! $package->service->isActive()) {
                    continue;
                }
                $lines[] = [
                    'kind' => 'service', 'id' => $package->id, 'model' => $package,
                    'product' => null, 'variant' => null, 'variant_id' => 0,
                    'vendor' => $package->service->vendor,
                    'name' => $package->service->title.' — '.ucfirst($package->tier->value),
                    'qty' => 1, 'unit_price' => $package->price, 'subtotal' => $package->price,
                    'in_stock' => true,
                ];
            }
        }

        return $lines;
    }

    /**
     * Lines grouped by vendor for display + per-vendor subtotals.
     * Returns ['groups' => [['vendor'=>Vendor|null,'lines'=>[...],'subtotal'=>int], ...], 'total'=>int].
     */
    public function summary(): array
    {
        $groups = [];
        $total = 0;

        foreach ($this->lines() as $line) {
            $vendorId = $line['vendor']?->id ?? 0;
            if (! isset($groups[$vendorId])) {
                $groups[$vendorId] = ['vendor' => $line['vendor'], 'lines' => [], 'subtotal' => 0];
            }
            $groups[$vendorId]['lines'][] = $line;
            $groups[$vendorId]['subtotal'] += $line['subtotal'];
            $total += $line['subtotal'];
        }

        return ['groups' => array_values($groups), 'total' => $total];
    }

    public function total(): int
    {
        return array_sum(array_column($this->lines(), 'subtotal'));
    }

    /** Flat shipping across physical vendor groups (each vendor's flat fee, free-threshold aware). */
    public function physicalShippingTotal(): int
    {
        $byVendor = [];
        foreach ($this->lines() as $line) {
            if ($line['kind'] === 'physical' && $line['vendor']) {
                $byVendor[$line['vendor']->id]['vendor'] = $line['vendor'];
                $byVendor[$line['vendor']->id]['subtotal'] = ($byVendor[$line['vendor']->id]['subtotal'] ?? 0) + $line['subtotal'];
            }
        }

        $shipping = 0;
        foreach ($byVendor as $g) {
            $shipping += $g['vendor']->shippingFeeFor($g['subtotal']);
        }

        return $shipping;
    }

    /** Items + physical shipping — the amount the buyer actually pays. */
    public function grandTotal(): int
    {
        return $this->total() + $this->physicalShippingTotal();
    }

    /**
     * How many payables a checkout would create: one Order per (kind, vendor)
     * product group (digital and physical from the same vendor are separate
     * orders) plus one ServiceOrder per service line. Gateway checkout needs 1.
     */
    public function payableCount(): int
    {
        $keys = [];
        $services = 0;

        foreach ($this->lines() as $line) {
            $vendorId = $line['vendor']?->id ?? 0;
            if ($line['kind'] === 'product') {
                $keys['d:'.$vendorId] = true;
            } elseif ($line['kind'] === 'physical') {
                $keys['p:'.$vendorId] = true;
            } else {
                $services++;
            }
        }

        return count($keys) + $services;
    }

    // ─── Internals ────────────────────────────────────────────────────────────

    private function physicalKey(int $productId, int $variantId): string
    {
        return $productId.':'.max(0, $variantId);
    }

    private function raw(): array
    {
        $cart = Session::get(self::KEY, []);

        return [
            'products' => $cart['products'] ?? [],
            'physical' => $cart['physical'] ?? [],
            'services' => $cart['services'] ?? [],
        ];
    }

    private function put(array $cart): void
    {
        Session::put(self::KEY, $cart);
    }
}
