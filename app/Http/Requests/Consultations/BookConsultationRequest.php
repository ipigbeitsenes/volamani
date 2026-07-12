<?php

namespace App\Http\Requests\Consultations;

use Illuminate\Foundation\Http\FormRequest;

class BookConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'package_id' => ['required', 'integer', 'exists:consultation_packages,id'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'meeting_platform' => ['nullable', 'string', 'in:google_meet,zoom,teams,phone,other'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
