<?php

namespace App\Http\Requests\Consultations;

use Illuminate\Foundation\Http\FormRequest;

class CreateConsultantProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();

        return $user && $user->vendor?->isActive() && ! $user->vendor->consultantProfile()->exists();
    }

    public function rules(): array
    {
        return [
            'display_name' => ['required', 'string', 'max:100'],
            'bio' => ['required', 'string', 'min:50', 'max:2000'],
            'niche' => ['required', 'string', 'max:100'],
            'expertise' => ['required', 'string', 'max:500'],
            'experience_years' => ['required', 'integer', 'min:1', 'max:50'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'calendly_url' => ['nullable', 'url', 'max:255'],
            'availability' => ['nullable', 'array'],
            'availability.*.enabled' => ['nullable', 'boolean'],
            'availability.*.start_time' => ['required_if:availability.*.enabled,true', 'date_format:H:i'],
            'availability.*.end_time' => ['required_if:availability.*.enabled,true', 'date_format:H:i', 'after:availability.*.start_time'],
        ];
    }
}
