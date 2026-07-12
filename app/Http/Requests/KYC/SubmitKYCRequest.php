<?php

namespace App\Http\Requests\KYC;

use App\Enums\KYCDocumentType;
use App\Enums\KYCType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class SubmitKYCRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'type' => ['required', new Enum(KYCType::class)],
            'full_name' => ['required', 'string', 'max:150'],
            'id_type' => ['required', new Enum(KYCDocumentType::class)],
            'id_number' => ['required', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],

            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],

            'business_name' => ['nullable', 'required_if:type,business', 'string', 'max:150'],
            'rc_number' => ['nullable', 'required_if:type,business', 'string', 'max:50'],

            'document_front' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'document_back' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'selfie' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'proof_of_address' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'business_name.required_if' => 'Business name is required for business verification.',
            'rc_number.required_if' => 'RC number is required for business verification.',
        ];
    }

    /** Scalar fields only (no uploaded files). */
    public function kycData(): array
    {
        return $this->safe()->only([
            'type', 'full_name', 'id_type', 'id_number', 'date_of_birth',
            'address', 'city', 'state', 'country', 'business_name', 'rc_number',
        ]);
    }

    /** Uploaded document files keyed by field name. */
    public function kycFiles(): array
    {
        return array_filter([
            'document_front' => $this->file('document_front'),
            'document_back' => $this->file('document_back'),
            'selfie' => $this->file('selfie'),
            'proof_of_address' => $this->file('proof_of_address'),
        ]);
    }
}
