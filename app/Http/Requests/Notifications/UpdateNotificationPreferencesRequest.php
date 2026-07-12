<?php

namespace App\Http\Requests\Notifications;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'preferences' => ['nullable', 'array'],
            'preferences.*' => ['array'],
            'preferences.*.email' => ['nullable', 'boolean'],
            'preferences.*.database' => ['nullable', 'boolean'],
        ];
    }

    public function preferences(): array
    {
        return $this->input('preferences', []);
    }
}
