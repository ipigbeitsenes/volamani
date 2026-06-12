<?php

namespace App\Services\Clients;

use App\Actions\Clients\AddInteractionAction;
use App\Actions\Clients\CreateClientAction;
use App\Actions\Clients\SyncClientsAction;
use App\Actions\Clients\UpdateClientAction;
use App\Models\Client;
use App\Models\ClientInteraction;
use App\Models\User;
use App\Models\Vendor;
use App\Repositories\Clients\ClientRepository;

class ClientService
{
    public function __construct(
        private CreateClientAction  $createAction,
        private UpdateClientAction  $updateAction,
        private AddInteractionAction $interactionAction,
        private SyncClientsAction   $syncAction,
        private ClientRepository    $repo,
    ) {}

    public function create(Vendor $vendor, array $data): Client
    {
        return $this->createAction->execute($vendor, $data);
    }

    public function update(Client $client, array $data): Client
    {
        return $this->updateAction->execute($client, $data);
    }

    public function addInteraction(Client $client, User $author, array $data): ClientInteraction
    {
        return $this->interactionAction->execute($client, $author, $data);
    }

    public function sync(Vendor $vendor): array
    {
        return $this->syncAction->execute($vendor);
    }

    public function toggleTask(ClientInteraction $interaction): ClientInteraction
    {
        $interaction->update([
            'completed_at' => $interaction->isComplete() ? null : now(),
        ]);

        return $interaction->fresh();
    }

    public function delete(Client $client): void
    {
        $client->delete();
    }

    // ─── Query passthroughs ──────────────────────────────────────────────────────

    public function forVendor(Vendor $vendor, int $perPage = 15, array $filters = [])
    {
        return $this->repo->forVendor($vendor, $perPage, $filters);
    }

    public function vendorStats(Vendor $vendor): array
    {
        return $this->repo->vendorStats($vendor);
    }

    public function recentBusiness(Client $client): array
    {
        return $this->repo->recentBusiness($client);
    }
}
