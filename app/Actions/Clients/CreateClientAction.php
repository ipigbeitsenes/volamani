<?php

namespace App\Actions\Clients;

use App\Enums\ClientSource;
use App\Enums\ClientStatus;
use App\Models\Client;
use App\Models\User;
use App\Models\Vendor;

class CreateClientAction
{
    public function execute(Vendor $vendor, array $data): Client
    {
        // Auto-link to a registered account when the email matches one.
        $user = ! empty($data['email'])
            ? User::where('email', $data['email'])->first()
            : null;

        return $vendor->clients()->create([
            'user_id' => $user?->id,
            'name'    => $data['name'],
            'email'   => $data['email'] ?? null,
            'phone'   => $data['phone'] ?? null,
            'company' => $data['company'] ?? null,
            'address' => $data['address'] ?? null,
            'status'  => $data['status'] ?? ClientStatus::Lead->value,
            'source'  => ClientSource::Manual->value,
            'tags'    => $data['tags'] ?? null,
            'about'   => $data['about'] ?? null,
        ]);
    }
}
