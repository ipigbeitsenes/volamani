<?php

namespace App\Http\Requests\Consultations;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConsultantProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        $profile = $this->route('profile');

        return $user && $profile && $profile->vendor->user_id === $user->id;
    }

    public function rules(): array
    {
        return [
            'display_name' => ['sometimes', 'string', 'max:100'],
            'bio' => ['sometimes', 'string', 'min:50', 'max:2000'],
            'niche' => ['sometimes', 'string', 'max:100'],
            'expertise' => ['sometimes', 'string', 'max:500'],
            'experience_years' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'calendly_url' => ['nullable', 'url', 'max:255'],
            'is_available' => ['sometimes', 'boolean'],
            'availability' => ['nullable', 'array'],
            'availability.*.enabled' => ['nullable', 'boolean'],
            'availability.*.start_time' => ['required_if:availability.*.enabled,true', 'date_format:H:i'],
            'availability.*.end_time' => ['required_if:availability.*.enabled,true', 'date_format:H:i', 'after:availability.*.start_time'],
        ];
    }
}
