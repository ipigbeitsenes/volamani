<?php

namespace App\Http\Requests\Products;

use App\Enums\ProductType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->vendor?->isActive();
    }

    public function rules(): array
    {
        return [
            'name'                   => ['required', 'string', 'max:255'],
            'category_id'            => ['required', 'exists:product_categories,id'],
            'type'                   => ['required', new Enum(ProductType::class)],
            'short_description'      => ['nullable', 'string', 'max:500'],
            'description'            => ['required', 'string', 'min:50'],
            'price'                  => ['required', 'numeric', 'min:0'],
            'compare_price'          => ['nullable', 'numeric', 'gt:price'],
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
