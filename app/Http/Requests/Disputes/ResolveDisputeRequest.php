<?php

namespace App\Http\Requests\Disputes;

use App\Enums\DisputeResolution;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ResolveDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'resolution'   => ['required', new Enum(DisputeResolution::class)],
            'vendor_share' => ['nullable', 'required_if:resolution,split', 'numeric', 'min:0'],
            'note'         => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'vendor_share.required_if' => 'Enter the vendor share for a split settlement.',
        ];
    }
}
