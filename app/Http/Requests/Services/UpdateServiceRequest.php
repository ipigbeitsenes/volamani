<?php

namespace App\Http\Requests\Services;

use App\Enums\PackageTier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $service = $this->route('service');

        return auth()->check()
            && $service
            && $service->vendor_id === auth()->user()->vendor?->id;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'short_description' => ['nullable', 'string', 'max:300'],
            'description' => ['sometimes', 'required', 'string', 'min:100'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],

            'packages' => ['sometimes', 'array', 'min:1'],
            'packages.*.tier' => ['required', new Enum(PackageTier::class)],
            'packages.*.name' => ['required', 'string', 'max:100'],
            'packages.*.description' => ['required', 'string', 'max:500'],
            'packages.*.price' => ['required', 'numeric', 'min:500'],
            'packages.*.delivery_days' => ['required', 'integer', 'min:1', 'max:365'],
            'packages.*.revisions' => ['required', 'integer', 'min:0', 'max:255'],
            'packages.*.features' => ['nullable', 'string'],

            'faqs' => ['nullable', 'array'],
            'faqs.*.question' => ['required_with:faqs.*.answer', 'string', 'max:255'],
            'faqs.*.answer' => ['required_with:faqs.*.question', 'string', 'max:1000'],
        ];
    }
}
