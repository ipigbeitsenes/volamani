<?php

namespace App\Actions\Products\Concerns;

use App\Models\Product;
use App\Models\Vendor;

trait SyncsPhysicalProduct
{
    /**
     * Create/update the physical detail row, secondary categories and variants
     * for a physical product. Safe to call on create or update.
     */
    protected function syncPhysical(Product $product, array $data): void
    {
        $product->physicalDetail()->updateOrCreate(
            ['product_id' => $product->id],
            [
                'stock_quantity' => (int) ($data['stock_quantity'] ?? 0),
                'track_inventory' => (bool) ($data['track_inventory'] ?? true),
                'allow_backorder' => (bool) ($data['allow_backorder'] ?? false),
                'condition' => $data['condition'] ?? 'new',
                'brand' => $data['brand'] ?? null,
                'weight_grams' => $data['weight_grams'] ?? null,
                'length_mm' => $data['length_mm'] ?? null,
                'width_mm' => $data['width_mm'] ?? null,
                'height_mm' => $data['height_mm'] ?? null,
            ],
        );

        // Secondary categories (primary is physical_category_id on the product).
        $product->secondaryPhysicalCategories()->sync($data['secondary_categories'] ?? []);

        // Variants: replace wholesale from the parallel arrays. Rows with a blank
        // name are skipped (empty trailing form rows).
        if (array_key_exists('variant_names', $data)) {
            $product->variants()->delete();
            $vendor = $product->vendor;                       // variant prices are in the vendor's currency
            $currency = $vendor instanceof Vendor ? $vendor->currencyCode() : currency()->base();

            foreach (($data['variant_names'] ?? []) as $i => $name) {
                $name = trim((string) $name);
                if ($name === '') {
                    continue;
                }

                $price = $data['variant_prices'][$i] ?? null;

                $product->variants()->create([
                    'name' => $name,
                    'sku' => $data['variant_skus'][$i] ?? null,
                    'price_override' => ($price === null || $price === '') ? null : currency()->toBase(to_kobo($price), $currency),
                    'stock_quantity' => (int) ($data['variant_stocks'][$i] ?? 0),
                    'is_active' => true,
                    'sort_order' => $i,
                ]);
            }
        }
    }
}
