<?php

namespace App\Http\Requests\Documents;

/**
 * Same shape as a vendor DocumentRequest, but issued by Volamani staff — so it
 * authorises on the admin role instead of vendor ownership.
 */
class PlatformDocumentRequest extends DocumentRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }
}
