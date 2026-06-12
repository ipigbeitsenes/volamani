<?php

namespace App\Http\Requests\Documents;

use Illuminate\Foundation\Http\FormRequest;

class DocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->vendor !== null;
    }

    public function rules(): array
    {
        return [
            'client_id'           => ['nullable', 'exists:users,id'],
            'client_name'         => ['required', 'string', 'max:255'],
            'client_email'        => ['nullable', 'email', 'max:255'],
            'client_phone'        => ['nullable', 'string', 'max:50'],
            'client_address'      => ['nullable', 'string', 'max:1000'],
            'title'               => ['nullable', 'string', 'max:255'],
            'discount'            => ['nullable', 'numeric', 'min:0'],         // naira
            'tax_rate'            => ['nullable', 'numeric', 'min:0', 'max:100'],
            'issue_date'          => ['nullable', 'date'],
            'due_date'            => ['nullable', 'date'],
            'valid_until'         => ['nullable', 'date'],
            'notes'               => ['nullable', 'string', 'max:2000'],
            'terms'               => ['nullable', 'string', 'max:2000'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.quantity'    => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'  => ['required', 'numeric', 'min:0'],         // naira
        ];
    }

    /** Normalised, model-ready data (money in kobo, items mapped). */
    public function documentData(): array
    {
        $items = collect($this->input('items', []))
            ->map(fn ($item) => [
                'description' => $item['description'],
                'quantity'    => (float) $item['quantity'],
                'unit_price'  => to_kobo((float) $item['unit_price']),
            ])
            ->all();

        return [
            'client_id'       => $this->input('client_id'),
            'client_name'     => $this->input('client_name'),
            'client_email'    => $this->input('client_email'),
            'client_phone'    => $this->input('client_phone'),
            'client_address'  => $this->input('client_address'),
            'title'           => $this->input('title'),
            'discount_amount' => to_kobo((float) $this->input('discount', 0)),
            'tax_rate'        => (float) $this->input('tax_rate', 0),
            'issue_date'      => $this->input('issue_date'),
            'due_date'        => $this->input('due_date'),
            'valid_until'     => $this->input('valid_until'),
            'notes'           => $this->input('notes'),
            'terms'           => $this->input('terms'),
            'items'           => $items,
        ];
    }
}
