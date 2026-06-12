<?php

namespace App\Actions\Clients;

use App\Models\Client;

class UpdateClientAction
{
    public function execute(Client $client, array $data): Client
    {
        $client->update([
            'name'    => $data['name'],
            'email'   => $data['email'] ?? null,
            'phone'   => $data['phone'] ?? null,
            'company' => $data['company'] ?? null,
            'address' => $data['address'] ?? null,
            'status'  => $data['status'] ?? $client->status->value,
            'tags'    => $data['tags'] ?? null,
            'about'   => $data['about'] ?? null,
        ]);

        return $client->fresh();
    }
}
