<?php

namespace App\Http\Requests\Products;

use App\Enums\ProductCondition;
use App\Enums\ProductType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');
        return auth()->check()
            && $product
            && $product->vendor_id === auth()->user()->vendor?->id;
    }

    public function rules(): array
    {
        return [
            'name'                   => ['sometimes', 'required', 'string', 'max:255'],
            'short_description'      => ['nullable', 'string', 'max:500'],
            'description'            => ['sometimes', 'required', 'string', 'min:50'],
            'price'                  => ['sometimes', 'required', 'numeric', 'min:0'],
            'compare_price'          => ['nullable', 'numeric'],
            'thumbnail'              => ['nullable', 'image', 'max:2048'],
            'preview_url'            => ['nullable', 'url', 'max:500'],
            'tags'                   => ['nullable', 'array'],
            'tags.*'                 => ['exists:product_tags,id'],
            'gallery'                => ['nullable', 'array', 'max:10'],
            'gallery.*'              => ['image', 'max:4096'],
            'seo_title'              => ['nullable', 'string', 'max:255'],
            'seo_description'        => ['nullable', 'string', 'max:500'],

            // ── Digital ───────────────────────────────────────────────────────
            'category_id'            => ['nullable', 'required_if:kind,digital', 'exists:product_categories,id'],
            'type'                   => ['nullable', 'required_if:kind,digital', new Enum(ProductType::class)],
            'download_limit'         => ['nullable', 'integer', 'min:1'],
            'download_expiry_hours'  => ['nullable', 'integer', 'min:1', 'max:8760'],
            'files'                  => ['nullable', 'array'],
            'files.*'                => ['file', 'max:102400'],
            'file_labels'            => ['nullable', 'array'],
            'file_labels.*'          => ['nullable', 'string', 'max:255'],

            // ── Physical ──────────────────────────────────────────────────────
            'physical_category_id'   => ['nullable', 'required_if:kind,physical', 'exists:physical_categories,id'],
            'secondary_categories'   => ['nullable', 'array', 'max:5'],
            'secondary_categories.*' => ['exists:physical_categories,id'],
            'condition'              => ['nullable', 'required_if:kind,physical', new Enum(ProductCondition::class)],
            'brand'                  => ['nullable', 'string', 'max:120'],
            'stock_quantity'         => ['nullable', 'integer', 'min:0'],
            'track_inventory'        => ['nullable', 'boolean'],
            'allow_backorder'        => ['nullable', 'boolean'],
            'weight_grams'           => ['nullable', 'integer', 'min:0'],
            'length_mm'              => ['nullable', 'integer', 'min:0'],
            'width_mm'               => ['nullable', 'integer', 'min:0'],
            'height_mm'              => ['nullable', 'integer', 'min:0'],

            // ── Variants (parallel arrays) ────────────────────────────────────
            'variant_names'          => ['nullable', 'array', 'max:50'],
            'variant_names.*'        => ['nullable', 'string', 'max:120'],
            'variant_skus'           => ['nullable', 'array'],
            'variant_skus.*'         => ['nullable', 'string', 'max:80'],
            'variant_prices'         => ['nullable', 'array'],
            'variant_prices.*'       => ['nullable', 'numeric', 'min:0'],
            'variant_stocks'         => ['nullable', 'array'],
            'variant_stocks.*'       => ['nullable', 'integer', 'min:0'],
        ];
    }
}
