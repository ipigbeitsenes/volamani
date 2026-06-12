<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStorefrontRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->vendor !== null;
    }

    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:120'],
            'tagline'       => ['nullable', 'string', 'max:160'],
            'description'   => ['nullable', 'string', 'max:2000'],
            'category'      => ['nullable', 'string', 'max:80'],
            'whatsapp'      => ['nullable', 'string', 'max:20'],
            'website'       => ['nullable', 'url', 'max:200'],
            'city'          => ['nullable', 'string', 'max:80'],
            'state'         => ['nullable', 'string', 'max:80'],
            'address'       => ['nullable', 'string', 'max:200'],
            'logo'          => ['nullable', 'image', 'max:2048'],
            'banner'        => ['nullable', 'image', 'max:5120'],
            'social_links'  => ['nullable', 'array'],
            'social_links.facebook'  => ['nullable', 'url'],
            'social_links.twitter'   => ['nullable', 'url'],
            'social_links.instagram' => ['nullable', 'url'],
            'social_links.linkedin'  => ['nullable', 'url'],
            'social_links.youtube'   => ['nullable', 'url'],
        ];
    }
}
