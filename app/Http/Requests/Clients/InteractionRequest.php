<?php

namespace App\Http\Requests\Clients;

use App\Enums\InteractionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class InteractionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->vendor !== null;
    }

    public function rules(): array
    {
        return [
            'type'        => ['required', new Enum(InteractionType::class)],
            'title'       => ['nullable', 'string', 'max:255'],
            'body'        => ['required', 'string', 'max:3000'],
            'pinned'      => ['nullable', 'boolean'],
            'due_at'      => ['nullable', 'date'],
            'occurred_at' => ['nullable', 'date'],
        ];
    }

    public function interactionData(): array
    {
        return [
            'type'        => $this->input('type'),
            'title'       => $this->input('title'),
            'body'        => $this->input('body'),
            'pinned'      => $this->boolean('pinned'),
            'due_at'      => $this->input('type') === InteractionType::Task->value ? $this->input('due_at') : null,
            'occurred_at' => $this->input('occurred_at') ?: now(),
        ];
    }
}
