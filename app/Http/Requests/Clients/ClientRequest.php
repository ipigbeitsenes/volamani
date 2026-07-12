<?php

namespace App\Http\Requests\Clients;

use App\Enums\ClientStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->vendor !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', new Enum(ClientStatus::class)],
            'tags' => ['nullable', 'string', 'max:500'],   // comma-separated
            'about' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function clientData(): array
    {
        $tags = $this->filled('tags')
            ? collect(explode(',', $this->input('tags')))->map(fn ($t) => trim($t))->filter()->values()->all()
            : null;

        return [
            'name' => $this->input('name'),
            'email' => $this->input('email'),
            'phone' => $this->input('phone'),
            'company' => $this->input('company'),
            'address' => $this->input('address'),
            'status' => $this->input('status', ClientStatus::Lead->value),
            'tags' => $tags ?: null,
            'about' => $this->input('about'),
        ];
    }
}
