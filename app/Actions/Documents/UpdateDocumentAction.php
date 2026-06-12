<?php

namespace App\Actions\Documents;

use App\Models\Document;
use Illuminate\Support\Facades\DB;

class UpdateDocumentAction
{
    /**
     * Update a draft document and replace its line items. Only drafts are editable.
     */
    public function execute(Document $document, array $data): Document
    {
        return DB::transaction(function () use ($document, $data) {
            $document->update([
                'client_id'       => $data['client_id'] ?? null,
                'client_name'     => $data['client_name'],
                'client_email'    => $data['client_email'] ?? null,
                'client_phone'    => $data['client_phone'] ?? null,
                'client_address'  => $data['client_address'] ?? null,
                'title'           => $data['title'] ?? null,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'tax_rate'        => $data['tax_rate'] ?? 0,
                'notes'           => $data['notes'] ?? null,
                'terms'           => $data['terms'] ?? null,
                'issue_date'      => $data['issue_date'] ?? $document->issue_date,
                'due_date'        => $document->isInvoice() ? ($data['due_date'] ?? null) : null,
                'valid_until'     => $document->isQuotation() ? ($data['valid_until'] ?? null) : null,
            ]);

            // Rebuild items from scratch — drafts only, so no payment history at risk.
            $document->items()->delete();

            foreach (array_values($data['items'] ?? []) as $i => $item) {
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

            return $document->recalcTotals()->load('items');
        });
    }
}
