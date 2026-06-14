<?php

namespace App\Http\Requests\Vendor;

use App\Enums\StoreFocus;
use App\Enums\StoreType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CreateVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && ! auth()->user()->vendor;
    }

    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:120'],
            'tagline'       => ['nullable', 'string', 'max:160'],
            'description'   => ['nullable', 'string', 'max:2000'],
            'category'      => ['nullable', 'string', 'max:80'],
            'store_type'    => ['required', new Enum(StoreType::class)],
            'store_focus'   => ['required', new Enum(StoreFocus::class)],
            'whatsapp'      => ['nullable', 'string', 'max:20'],
            'city'          => ['nullable', 'string', 'max:80'],
            'state'         => ['nullable', 'string', 'max:80'],
        ];
    }
}
