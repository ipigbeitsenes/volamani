<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'amount'         => ['required', 'numeric', 'min:1000'],
            'bank_name'      => ['required', 'string', 'max:100'],
            'account_name'   => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'digits:10'],
            'bank_code'      => ['nullable', 'string', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min'            => 'Minimum withdrawal amount is ₦1,000.',
            'account_number.digits' => 'Account number must be exactly 10 digits.',
        ];
    }
}
