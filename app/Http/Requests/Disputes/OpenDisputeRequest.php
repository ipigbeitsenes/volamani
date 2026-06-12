<?php

namespace App\Http\Requests\Disputes;

use App\Enums\DisputeReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class OpenDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'reason'      => ['required', new Enum(DisputeReason::class)],
            'description' => ['required', 'string', 'min:20', 'max:2000'],
            'attachment'  => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,zip', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'description.min' => 'Please describe the issue in at least 20 characters.',
        ];
    }
}
