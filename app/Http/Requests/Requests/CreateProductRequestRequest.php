<?php

namespace App\Http\Requests\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title'         => ['required', 'string', 'max:255'],
            'category_id'   => ['nullable', 'exists:product_categories,id'],
            'description'   => ['required', 'string', 'min:50'],
            'budget_min'    => ['nullable', 'numeric', 'min:0'],
            'budget_max'    => ['nullable', 'numeric', 'min:0', 'gte:budget_min'],
            'deadline_at'   => ['nullable', 'date', 'after:today'],
            'attachments'   => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:20480'],
            'is_public'     => ['boolean'],
            'location'      => ['nullable', 'string', 'max:255'],
        ];
    }
}
