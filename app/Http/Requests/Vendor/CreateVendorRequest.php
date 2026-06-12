<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

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
            'whatsapp'      => ['nullable', 'string', 'max:20'],
            'city'          => ['nullable', 'string', 'max:80'],
            'state'         => ['nullable', 'string', 'max:80'],
        ];
    }
}
