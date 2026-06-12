<?php

namespace App\Actions\Clients;

use App\Enums\ClientStatus;
use App\Models\Client;
use App\Models\ClientInteraction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AddInteractionAction
{
    public function execute(Client $client, User $author, array $data): ClientInteraction
    {
        return DB::transaction(function () use ($client, $author, $data) {
            $occurredAt = $data['occurred_at'] ?? now();

            $interaction = $client->interactions()->create([
                'user_id'     => $author->id,
                'type'        => $data['type'],
                'title'       => $data['title'] ?? null,
                'body'        => $data['body'] ?? null,
                'pinned'      => $data['pinned'] ?? false,
                'due_at'      => $data['due_at'] ?? null,
                'occurred_at' => $occurredAt,
            ]);

            $update = ['last_interaction_at' => now()];

            // First touch promotes a lead to an active client.
            if ($client->status === ClientStatus::Lead) {
                $update['status'] = ClientStatus::Active;
            }

            $client->update($update);

            return $interaction;
        });
    }
}
