<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\ClientRequest;
use App\Http\Requests\Clients\InteractionRequest;
use App\Models\Client;
use App\Models\ClientInteraction;
use App\Services\Clients\ClientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function __construct(private ClientService $clientService) {}

    public function index(Request $request): View
    {
        $vendor = $request->user()->vendor;
        $filters = $request->only('status', 'search', 'tag');
        $clients = $this->clientService->forVendor($vendor, 15, $filters);
        $stats = $this->clientService->vendorStats($vendor);

        return view('vendor.clients.index', compact('clients', 'stats', 'filters'));
    }

    public function create(): View
    {
        return view('vendor.clients.form', ['client' => new Client]);
    }

    public function store(ClientRequest $request): RedirectResponse
    {
        $client = $this->clientService->create($request->user()->vendor, $request->clientData());

        $this->flashSuccess("Client \"{$client->name}\" added.");

        return redirect()->route('vendor.clients.show', $client);
    }

    public function show(Client $client): View
    {
        $this->authorizeClient($client);

        $client->load(['interactions.author', 'user']);
        $business = $this->clientService->recentBusiness($client);

        return view('vendor.clients.show', compact('client', 'business'));
    }

    public function edit(Client $client): View
    {
        $this->authorizeClient($client);

        return view('vendor.clients.form', compact('client'));
    }

    public function update(ClientRequest $request, Client $client): RedirectResponse
    {
        $this->authorizeClient($client);

        $this->clientService->update($client, $request->clientData());
        $this->flashSuccess('Client updated.');

        return redirect()->route('vendor.clients.show', $client);
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorizeClient($client);

        $this->clientService->delete($client);
        $this->flashSuccess('Client removed.');

        return redirect()->route('vendor.clients.index');
    }

    public function addInteraction(InteractionRequest $request, Client $client): RedirectResponse
    {
        $this->authorizeClient($client);

        $this->clientService->addInteraction($client, $request->user(), $request->interactionData());
        $this->flashSuccess('Logged.');

        return back();
    }

    public function completeTask(Client $client, ClientInteraction $interaction): RedirectResponse
    {
        $this->authorizeClient($client);
        abort_unless($interaction->client_id === $client->id, 404);

        $this->clientService->toggleTask($interaction);

        return back();
    }

    public function sync(Request $request): RedirectResponse
    {
        $result = $this->clientService->sync($request->user()->vendor);

        $this->flashSuccess("Sync complete — {$result['created']} new client(s) imported, {$result['updated']} updated.");

        return redirect()->route('vendor.clients.index');
    }

    private function authorizeClient(Client $client): void
    {
        abort_unless($client->vendor_id === auth()->user()->vendor?->id, 403);
    }
}
