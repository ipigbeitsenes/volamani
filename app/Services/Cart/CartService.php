<?php

namespace App\Services\Cart;

use App\Models\Product;
use App\Models\ServicePackage;
use Illuminate\Support\Facades\Session;

/**
 * Session-backed shopping cart. Holds digital products (with quantity) and
 * service packages (quantity always 1 — a service order is a one-off booking).
 *
 * Session shape:
 *   cart => [
 *     'products' => [productId => qty, ...],
 *     'services' => [packageId => true, ...],
 *   ]
 *
 * Resolution to models happens lazily on read, and anything that no longer
 * exists / is inactive is silently dropped so the cart can never render or
 * check out stale items.
 */
class CartService
{
    private const KEY = 'cart';

    // ─── Mutators ─────────────────────────────────────────────────────────────

    public function addProduct(int $productId, int $qty = 1): void
    {
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

    /**
     * Total number of line units in the cart (product quantities + service count).
     */
    public function count(): int
    {
        $cart = $this->raw();
        return array_sum($cart['products']) + count($cart['services']);
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * Flat list of resolved, still-valid line items. Each line:
     *   ['kind' => 'product'|'service', 'id' => int, 'model' => Model,
     *    'vendor' => Vendor|null, 'name' => string, 'qty' => int,
     *    'unit_price' => int(kobo), 'subtotal' => int(kobo)]
     */
    public function lines(): array
    {
        $cart  = $this->raw();
        $lines = [];

        if ($cart['products']) {
            $products = Product::with('vendor')->whereIn('id', array_keys($cart['products']))->get()->keyBy('id');
            foreach ($cart['products'] as $id => $qty) {
                $product = $products->get($id);
                if (! $product || ! $product->isActive()) {
                    continue;
                }
                $qty = max(1, (int) $qty);
                $lines[] = [
                    'kind'       => 'product',
                    'id'         => $product->id,
                    'model'      => $product,
                    'vendor'     => $product->vendor,
                    'name'       => $product->name,
                    'qty'        => $qty,
                    'unit_price' => $product->price,
                    'subtotal'   => $product->price * $qty,
                ];
            }
        }

        if ($cart['services']) {
            $packages = ServicePackage::with('service.vendor')->whereIn('id', array_keys($cart['services']))->get()->keyBy('id');
            foreach (array_keys($cart['services']) as $id) {
                $package = $packages->get($id);
                if (! $package || ! $package->service || ! $package->service->isActive()) {
                    continue;
                }
                $lines[] = [
                    'kind'       => 'service',
                    'id'         => $package->id,
                    'model'      => $package,
                    'vendor'     => $package->service->vendor,
                    'name'       => $package->service->title . ' — ' . ucfirst($package->tier->value),
                    'qty'        => 1,
                    'unit_price' => $package->price,
                    'subtotal'   => $package->price,
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
        $total  = 0;

        foreach ($this->lines() as $line) {
            $vendorId = $line['vendor']?->id ?? 0;
            if (! isset($groups[$vendorId])) {
                $groups[$vendorId] = [
                    'vendor'   => $line['vendor'],
                    'lines'    => [],
                    'subtotal' => 0,
                ];
            }
            $groups[$vendorId]['lines'][]   = $line;
            $groups[$vendorId]['subtotal'] += $line['subtotal'];
            $total                         += $line['subtotal'];
        }

        return ['groups' => array_values($groups), 'total' => $total];
    }

    public function total(): int
    {
        return array_sum(array_column($this->lines(), 'subtotal'));
    }

    /**
     * How many payables a checkout would create: one Order per product-vendor
     * group plus one ServiceOrder per service line. A gateway (card/bank)
     * checkout is only possible when this is exactly 1.
     */
    public function payableCount(): int
    {
        $vendors  = [];
        $services = 0;

        foreach ($this->lines() as $line) {
            if ($line['kind'] === 'product') {
                $vendors[$line['vendor']?->id ?? 0] = true;
            } else {
                $services++;
            }
        }

        return count($vendors) + $services;
    }

    // ─── Internals ────────────────────────────────────────────────────────────

    private function raw(): array
    {
        $cart = Session::get(self::KEY, []);

        return [
            'products' => $cart['products'] ?? [],
            'services' => $cart['services'] ?? [],
        ];
    }

    private function put(array $cart): void
    {
        Session::put(self::KEY, $cart);
    }
}
