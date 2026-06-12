<?php

namespace App\Http\Requests\Subscription;

use App\Enums\BillingInterval;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class PlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:255'],
            'tagline'          => ['nullable', 'string', 'max:255'],
            'description'      => ['nullable', 'string', 'max:2000'],
            'price'            => ['required', 'numeric', 'min:0'],            // naira
            'billing_interval' => ['required', new Enum(BillingInterval::class)],
            'commission_rate'  => ['nullable', 'numeric', 'min:0', 'max:100'], // %
            'trial_days'       => ['nullable', 'integer', 'min:0', 'max:365'],
            'max_products'     => ['nullable', 'integer', 'min:0'],
            'max_services'     => ['nullable', 'integer', 'min:0'],
            'perks'            => ['nullable', 'string', 'max:2000'],          // one per line
            'featured_listing' => ['nullable', 'boolean'],
            'is_active'        => ['nullable', 'boolean'],
            'is_popular'       => ['nullable', 'boolean'],
            'sort_order'       => ['nullable', 'integer', 'min:0'],
        ];
    }

    /** Build the model-ready attribute array (naira→kobo, perks→array, checkboxes). */
    public function planData(): array
    {
        $perks = collect(preg_split('/\r\n|\r|\n/', (string) $this->input('perks')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        return [
            'name'             => $this->input('name'),
            'tagline'          => $this->input('tagline'),
            'description'      => $this->input('description'),
            'price'            => to_kobo((float) $this->input('price')),
            'billing_interval' => $this->input('billing_interval'),
            'commission_rate'  => $this->filled('commission_rate') ? (float) $this->input('commission_rate') : null,
            'trial_days'       => (int) $this->input('trial_days', 0),
            'max_products'     => $this->filled('max_products') ? (int) $this->input('max_products') : null,
            'max_services'     => $this->filled('max_services') ? (int) $this->input('max_services') : null,
            'perks'            => $perks ?: null,
            'featured_listing' => $this->boolean('featured_listing'),
            'is_active'        => $this->boolean('is_active'),
            'is_popular'       => $this->boolean('is_popular'),
            'sort_order'       => (int) $this->input('sort_order', 0),
        ];
    }
}
