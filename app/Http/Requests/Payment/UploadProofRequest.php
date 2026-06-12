<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class UploadProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        $payment = $this->route('payment');
        return $payment && $payment->user_id === auth()->id();
    }

    public function rules(): array
    {
        return [
            'bank_name'     => ['required', 'string', 'max:100'],
            'account_name'  => ['required', 'string', 'max:200'],
            'amount'        => ['required', 'numeric', 'min:1'],
            'transfer_date' => ['required', 'date', 'before_or_equal:today'],
            'proof_file'    => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'notes'         => ['nullable', 'string', 'max:500'],
        ];
    }
}
