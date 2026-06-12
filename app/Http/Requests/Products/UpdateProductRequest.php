<?php

namespace App\Http\Requests\Products;

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
            'category_id'            => ['sometimes', 'required', 'exists:product_categories,id'],
            'type'                   => ['sometimes', 'required', new Enum(ProductType::class)],
            'short_description'      => ['nullable', 'string', 'max:500'],
            'description'            => ['sometimes', 'required', 'string', 'min:50'],
            'price'                  => ['sometimes', 'required', 'numeric', 'min:0'],
            'compare_price'          => ['nullable', 'numeric'],
            'thumbnail'              => ['nullable', 'image', 'max:2048'],
            'preview_url'            => ['nullable', 'url', 'max:500'],
            'download_limit'         => ['nullable', 'integer', 'min:1'],
            'download_expiry_hours'  => ['nullable', 'integer', 'min:1', 'max:8760'],
            'tags'                   => ['nullable', 'array'],
            'tags.*'                 => ['exists:product_tags,id'],
            'gallery'                => ['nullable', 'array', 'max:10'],
            'gallery.*'              => ['image', 'max:4096'],
            'files'                  => ['nullable', 'array'],
            'files.*'                => ['file', 'max:102400'],
            'file_labels'            => ['nullable', 'array'],
            'file_labels.*'          => ['string', 'max:255'],
            'seo_title'              => ['nullable', 'string', 'max:255'],
            'seo_description'        => ['nullable', 'string', 'max:500'],
        ];
    }
}
