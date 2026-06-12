<?php

namespace App\Http\Requests\Reviews;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'reviewable_type' => ['required', Rule::in(['product', 'service', 'consultant'])],
            'reviewable_id'   => ['required', 'integer'],
            'rating'          => ['required', 'integer', 'min:1', 'max:5'],
            'title'           => ['nullable', 'string', 'max:120'],
            'body'            => ['nullable', 'string', 'max:2000'],
        ];
    }
}
