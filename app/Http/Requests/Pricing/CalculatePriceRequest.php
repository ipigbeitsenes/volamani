<?php

namespace App\Http\Requests\Pricing;

use Illuminate\Foundation\Http\FormRequest;

class CalculatePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // public tool
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string'],
            'service_name' => ['required', 'string', 'max:200'],
            'pricing_type' => ['required', 'in:fixed,hourly,milestone'],
            'urgency' => ['nullable', 'in:normal,soon,urgent,rush'],
            'template_id' => ['nullable', 'integer', 'exists:pricing_templates,id'],
            'add_on_ids' => ['nullable', 'array'],
            'add_on_ids.*' => ['integer', 'exists:pricing_add_ons,id'],

            // Fixed pricing
            'base_price' => ['required_if:pricing_type,fixed', 'nullable', 'numeric', 'min:0'],

            // Hourly pricing
            'hourly_rate' => ['required_if:pricing_type,hourly', 'nullable', 'numeric', 'min:0'],
            'estimated_hours' => ['required_if:pricing_type,hourly', 'nullable', 'numeric', 'min:0.5'],

            // Milestone pricing
            'milestones' => ['required_if:pricing_type,milestone', 'nullable', 'array', 'min:1'],
            'milestones.*.name' => ['required', 'string', 'max:200'],
            'milestones.*.description' => ['nullable', 'string'],
            'milestones.*.amount' => ['required', 'numeric', 'min:0'],

            // Optional quotation fields
            'notes' => ['nullable', 'string', 'max:2000'],
            'client_name' => ['nullable', 'string', 'max:100'],
            'client_email' => ['nullable', 'email'],
        ];
    }
}
