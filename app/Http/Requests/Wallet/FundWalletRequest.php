<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class FundWalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:500', 'max:10000000'],
            'method' => ['nullable', 'in:paystack,bank_transfer'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'Minimum funding amount is '.currency_symbol().'500.',
            'amount.max' => 'Maximum single funding is '.currency_symbol().'10,000,000.',
        ];
    }
}
