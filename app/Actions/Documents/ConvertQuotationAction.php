<?php

namespace App\Actions\Documents;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use Illuminate\Support\Facades\DB;

class ConvertQuotationAction
{
    public function __construct(private CreateDocumentAction $createAction) {}

    /**
     * Turn an accepted quotation into a fresh draft invoice, copying the client
     * details and line items. The quotation is marked Converted and linked.
     */
    public function execute(Document $quotation): Document
    {
        abort_unless($quotation->isQuotation(), 422, 'Only quotations can be converted.');

        return DB::transaction(function () use ($quotation) {
            $data = [
                'client_id'       => $quotation->client_id,
                'client_name'     => $quotation->client_name,
                'client_email'    => $quotation->client_email,
                'client_phone'    => $quotation->client_phone,
                'client_address'  => $quotation->client_address,
                'title'           => $quotation->title,
                'discount_amount' => $quotation->discount_amount,
                'tax_rate'        => $quotation->tax_rate,
                'notes'           => $quotation->notes,
                'terms'           => $quotation->terms,
                'issue_date'      => now()->toDateString(),
                'due_date'        => now()->addDays(14)->toDateString(),
                'items'           => $quotation->items->map(fn ($item) => [
                    'description' => $item->description,
                    'quantity'    => $item->quantity,
                    'unit_price'  => $item->unit_price,
                ])->all(),
            ];

            $invoice = $this->createAction->execute(
                $quotation->vendor,
                DocumentType::Invoice,
                $data,
                $quotation->creator ?? $quotation->vendor->user,
            );

            $quotation->update([
                'status'          => DocumentStatus::Converted,
                'converted_to_id' => $invoice->id,
            ]);

            return $invoice;
        });
    }
}
