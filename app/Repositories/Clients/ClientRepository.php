<?php

namespace App\Repositories\Clients;

use App\Enums\ClientStatus;
use App\Enums\DocumentType;
use App\Models\Client;
use App\Models\Document;
use App\Models\Order;
use App\Models\ServiceOrder;
use App\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClientRepository
{
    public function forVendor(Vendor $vendor, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Client::where('vendor_id', $vendor->id)
            ->withCount('interactions')
            ->orderByDesc('total_spent')
            ->orderBy('name');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('company', 'like', "%{$term}%"));
        }

        if (! empty($filters['tag'])) {
            $query->whereJsonContains('tags', $filters['tag']);
        }

        return $query->paginate($perPage);
    }

    public function vendorStats(Vendor $vendor): array
    {
        $base = Client::where('vendor_id', $vendor->id);

        return [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('status', ClientStatus::Active)->count(),
            'leads' => (clone $base)->where('status', ClientStatus::Lead)->count(),
            'lifetime' => (int) (clone $base)->sum('total_spent'),
        ];
    }

    /** Recent business records tied to a client, for the profile page. */
    public function recentBusiness(Client $client): array
    {
        if (! $client->user_id) {
            $invoices = Document::where('vendor_id', $client->vendor_id)
                ->where('client_email', $client->email)
                ->where('type', DocumentType::Invoice)
                ->latest()->limit(5)->get();

            return ['orders' => collect(), 'serviceOrders' => collect(), 'invoices' => $invoices];
        }

        return [
            'orders' => Order::where('vendor_id', $client->vendor_id)
                ->where('buyer_id', $client->user_id)
                ->latest()->limit(5)->get(),
            'serviceOrders' => ServiceOrder::where('vendor_id', $client->vendor_id)
                ->where('buyer_id', $client->user_id)
                ->latest()->limit(5)->get(),
            'invoices' => Document::where('vendor_id', $client->vendor_id)
                ->where('client_id', $client->user_id)
                ->where('type', DocumentType::Invoice)
                ->latest()->limit(5)->get(),
        ];
    }
}
