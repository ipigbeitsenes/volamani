<?php

namespace App\Http\Requests\Consultations;

use Illuminate\Foundation\Http\FormRequest;

class CreatePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user    = auth()->user();
        $profile = $user?->vendor?->consultantProfile;
        return $profile !== null && $profile->vendor->user_id === $user->id;
    }

    public function rules(): array
    {
        return [
            'name'                   => ['required', 'string', 'max:100'],
            'description'            => ['required', 'string', 'max:1000'],
            'type'                   => ['required', 'in:one_time,retainer'],
            'duration_minutes'       => ['required', 'integer', 'min:15', 'max:480'],
            'price'                  => ['required', 'numeric', 'min:0.01'],
            'max_sessions_per_month' => ['nullable', 'required_if:type,retainer', 'integer', 'min:1', 'max:31'],
        ];
    }
}
