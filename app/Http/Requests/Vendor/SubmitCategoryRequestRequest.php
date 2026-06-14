<?php

namespace App\Http\Requests\Vendor;

use App\Enums\CategoryDomain;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class SubmitCategoryRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->vendor !== null;
    }

    public function rules(): array
    {
        return [
            'domain'    => ['required', new Enum(CategoryDomain::class)],
            'name'      => ['required', 'string', 'max:120'],
            'parent_id' => ['nullable', 'integer'],
            'reason'    => ['nullable', 'string', 'max:500'],
        ];
    }
}
