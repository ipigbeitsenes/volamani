<?php

namespace App\Actions\Clients;

use App\Enums\ClientSource;
use App\Enums\ClientStatus;
use App\Enums\DocumentType;
use App\Enums\PaymentStatus;
use App\Models\Client;
use App\Models\Document;
use App\Models\Order;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vendor;

class SyncClientsAction
{
    /**
     * Build / refresh the vendor's client list from their real activity:
     * product orders, service orders and invoices. Returns the number of
     * brand-new client records created.
     *
     * @return array{created:int, updated:int}
     */
    public function execute(Vendor $vendor): array
    {
        $created = 0;
        $updated = 0;

        // ── Registered buyers (orders + service orders) ─────────────────────────
        $buyerIds = Order::where('vendor_id', $vendor->id)->pluck('buyer_id')
            ->merge(ServiceOrder::where('vendor_id', $vendor->id)->pluck('buyer_id'))
            ->merge(Document::where('vendor_id', $vendor->id)->whereNotNull('client_id')->pluck('client_id'))
            ->unique()
            ->filter();

        foreach ($buyerIds as $userId) {
            $user = User::find($userId);
            if (! $user) {
                continue;
            }

            $client = Client::withTrashed()->firstOrNew([
                'vendor_id' => $vendor->id,
                'user_id' => $userId,
            ]);

            if ($client->trashed()) {
                continue; // respect a deliberate deletion
            }

            $isNew = ! $client->exists;

            if ($isNew) {
                $client->fill([
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'source' => ClientSource::Order,
                    'status' => ClientStatus::Active,
                ]);
            }

            $this->applyUserAggregates($client, $vendor, $userId);
            $client->last_synced_at = now();
            $client->save();

            $isNew ? $created++ : $updated++;
        }

        // ── External invoice contacts (no Volamani account) ─────────────────────
        $externalDocs = Document::where('vendor_id', $vendor->id)
            ->whereNull('client_id')
            ->whereNotNull('client_email')
            ->get()
            ->unique('client_email');

        foreach ($externalDocs as $doc) {
            $client = Client::withTrashed()->firstOrNew([
                'vendor_id' => $vendor->id,
                'user_id' => null,
                'email' => $doc->client_email,
            ]);

            if ($client->trashed()) {
                continue; // respect a deliberate deletion
            }

            $isNew = ! $client->exists;

            if ($isNew) {
                $client->fill([
                    'name' => $doc->client_name,
                    'phone' => $doc->client_phone,
                    'source' => ClientSource::Invoice,
                    'status' => ClientStatus::Active,
                ]);
            }

            $client->total_spent = (int) Document::where('vendor_id', $vendor->id)
                ->where('client_email', $doc->client_email)
                ->where('type', DocumentType::Invoice)
                ->sum('amount_paid');
            $client->last_synced_at = now();
            $client->save();

            $isNew ? $created++ : $updated++;
        }

        return ['created' => $created, 'updated' => $updated];
    }

    private function applyUserAggregates(Client $client, Vendor $vendor, int $userId): void
    {
        $orderSpend = (int) Order::where('vendor_id', $vendor->id)
            ->where('buyer_id', $userId)
            ->where('payment_status', PaymentStatus::Success)
            ->sum('total_amount');

        $orderCount = Order::where('vendor_id', $vendor->id)
            ->where('buyer_id', $userId)
            ->where('payment_status', PaymentStatus::Success)
            ->count();

        $serviceSpend = (int) ServiceOrder::where('vendor_id', $vendor->id)
            ->where('buyer_id', $userId)
            ->where('payment_status', PaymentStatus::Success)
            ->sum('total_amount');

        $serviceCount = ServiceOrder::where('vendor_id', $vendor->id)
            ->where('buyer_id', $userId)
            ->where('payment_status', PaymentStatus::Success)
            ->count();

        $invoiceSpend = (int) Document::where('vendor_id', $vendor->id)
            ->where('client_id', $userId)
            ->where('type', DocumentType::Invoice)
            ->sum('amount_paid');

        $client->total_spent = $orderSpend + $serviceSpend + $invoiceSpend;
        $client->orders_count = $orderCount + $serviceCount;
    }
}
