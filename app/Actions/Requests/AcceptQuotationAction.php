<?php

namespace App\Actions\Requests;

use App\Enums\QuotationStatus;
use App\Enums\RequestStatus;
use App\Models\ProductRequest;
use App\Models\ProductRequestQuotation;
use Illuminate\Support\Facades\DB;

class AcceptQuotationAction
{
    public function execute(ProductRequest $request, ProductRequestQuotation $quotation): ProductRequest
    {
        abort_unless($request->isOpen(), 422, 'This request is no longer open.');
        abort_unless($quotation->request_id === $request->id, 403);
        abort_unless($quotation->isPending(), 422, 'This quotation cannot be accepted.');

        return DB::transaction(function () use ($request, $quotation) {
            $quotation->update([
                'status'      => QuotationStatus::Accepted,
                'accepted_at' => now(),
            ]);

            $request->quotations()
                ->where('id', '!=', $quotation->id)
                ->update([
                    'status'      => QuotationStatus::Rejected,
                    'rejected_at' => now(),
                ]);

            $request->update([
                'status'                 => RequestStatus::Accepted,
                'accepted_quotation_id'  => $quotation->id,
                'closed_at'              => now(),
            ]);

            return $request->fresh(['acceptedQuotation.vendor']);
        });
    }
}
