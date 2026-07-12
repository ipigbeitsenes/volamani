<?php

namespace App\Http\Requests\Matching;

use Illuminate\Foundation\Http\FormRequest;

class MatchingProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->vendor !== null;
    }

    public function rules(): array
    {
        return [
            'headline' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'categories' => ['nullable', 'string', 'max:500'],  // comma-separated
            'skills' => ['nullable', 'string', 'max:500'],
            'min_budget' => ['nullable', 'numeric', 'min:0'],   // naira
            'max_budget' => ['nullable', 'numeric', 'min:0', 'gte:min_budget'],
            'serves_remote' => ['nullable', 'boolean'],
            'locations' => ['nullable', 'string', 'max:500'],
            'is_accepting' => ['nullable', 'boolean'],
        ];
    }

    public function profileData(): array
    {
        return [
            'headline' => $this->input('headline'),
            'bio' => $this->input('bio'),
            'categories' => $this->splitList($this->input('categories')),
            'skills' => $this->splitList($this->input('skills')),
            'min_budget' => $this->filled('min_budget') ? to_kobo((float) $this->input('min_budget')) : null,
            'max_budget' => $this->filled('max_budget') ? to_kobo((float) $this->input('max_budget')) : null,
            'serves_remote' => $this->boolean('serves_remote'),
            'locations' => $this->splitList($this->input('locations')),
            'is_accepting' => $this->boolean('is_accepting'),
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
