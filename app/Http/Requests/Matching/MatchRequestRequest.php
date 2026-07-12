<?php

namespace App\Http\Requests\Matching;

use App\Enums\MatchTargetType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class MatchRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'looking_for' => ['required', new Enum(MatchTargetType::class)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:3000'],
            'category' => ['nullable', 'string', 'max:100'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],   // naira
            'budget_max' => ['nullable', 'numeric', 'min:0', 'gte:budget_min'],
            'preferred_location' => ['nullable', 'string', 'max:120'],
            'remote_ok' => ['nullable', 'boolean'],
            'skills' => ['nullable', 'string', 'max:500'],  // comma-separated
            'timeline' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function requestData(): array
    {
        return [
            'looking_for' => $this->input('looking_for'),
            'title' => $this->input('title'),
            'description' => $this->input('description'),
            'category' => $this->input('category'),
            'budget_min' => $this->filled('budget_min') ? to_kobo((float) $this->input('budget_min')) : null,
            'budget_max' => $this->filled('budget_max') ? to_kobo((float) $this->input('budget_max')) : null,
            'preferred_location' => $this->input('preferred_location'),
            'remote_ok' => $this->boolean('remote_ok'),
            'skills' => $this->splitList($this->input('skills')),
            'timeline' => $this->input('timeline'),
        ];
    }

    private function splitList(?string $value): ?array
    {
        if (! $value) {
            return null;
        }

        $items = collect(explode(',', $value))->map(fn ($v) => trim($v))->filter()->values()->all();

        return $items ?: null;
    }
}
