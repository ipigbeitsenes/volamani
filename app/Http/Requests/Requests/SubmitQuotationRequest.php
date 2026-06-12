<?php

namespace App\Http\Requests\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->vendor?->isActive();
    }

    public function rules(): array
    {
        return [
            'price'          => ['required', 'numeric', 'min:500'],
            'delivery_days'  => ['required', 'integer', 'min:1', 'max:365'],
            'message'        => ['required', 'string', 'min:50'],
            'attachments'    => ['nullable', 'array', 'max:3'],
            'attachments.*'  => ['file', 'max:10240'],
        ];
    }
}
