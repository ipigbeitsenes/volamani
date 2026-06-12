<?php

namespace App\Actions\Documents;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

class CreateDocumentAction
{
    /**
     * Create a draft invoice or quotation with its line items and computed totals.
     *
     * @param array $data  normalised attributes (money already in kobo) incl. 'items'
     */
    public function execute(Vendor $vendor, DocumentType $type, array $data, User $creator): Document
    {
        return DB::transaction(function () use ($vendor, $type, $data, $creator) {
            $document = $vendor->documents()->create([
                'type'            => $type,
                'number'          => $this->nextNumber($vendor, $type),
                'client_id'       => $data['client_id'] ?? null,
                'client_name'     => $data['client_name'],
                'client_email'    => $data['client_email'] ?? null,
                'client_phone'    => $data['client_phone'] ?? null,
                'client_address'  => $data['client_address'] ?? null,
                'title'           => $data['title'] ?? null,
                'status'          => DocumentStatus::Draft,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'tax_rate'        => $data['tax_rate'] ?? 0,
                'notes'           => $data['notes'] ?? null,
                'terms'           => $data['terms'] ?? null,
                'issue_date'      => $data['issue_date'] ?? now()->toDateString(),
                'due_date'        => $type === DocumentType::Invoice ? ($data['due_date'] ?? null) : null,
                'valid_until'     => $type === DocumentType::Quotation ? ($data['valid_until'] ?? null) : null,
                'created_by'      => $creator->id,
            ]);

            $this->syncItems($document, $data['items'] ?? []);

            return $document->recalcTotals()->load('items');
        });
    }

    private function syncItems(Document $document, array $items): void
    {
        foreach (array_values($items) as $i => $item) {
            $qty  = (float) ($item['quantity'] ?? 1);
            $unit = (int) ($item['unit_price'] ?? 0);

            $document->items()->create([
                'description' => $item['description'],
                'quantity'    => $qty,
                'unit_price'  => $unit,
                'amount'      => (int) round($qty * $unit),
                'sort_order'  => $i,
            ]);
        }
    }

    private function nextNumber(Vendor $vendor, DocumentType $type): string
    {
        $seq = Document::withTrashed()
            ->where('vendor_id', $vendor->id)
            ->where('type', $type->value)
            ->count() + 1;

        return sprintf('%s-%s-%04d', $type->prefix(), date('Y'), $seq);
    }
}
