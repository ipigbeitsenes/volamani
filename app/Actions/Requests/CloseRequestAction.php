<?php

namespace App\Actions\Requests;

use App\Enums\QuotationStatus;
use App\Enums\RequestStatus;
use App\Models\ProductRequest;
use Illuminate\Support\Facades\DB;

class CloseRequestAction
{
    public function execute(ProductRequest $request): ProductRequest
    {
        abort_unless($request->isOpen(), 422, 'Request is already closed.');

        return DB::transaction(function () use ($request) {
            $request->quotations()
                ->where('status', QuotationStatus::Pending)
                ->update([
                    'status' => QuotationStatus::Rejected,
                    'rejected_at' => now(),
                ]);

            $request->update([
                'status' => RequestStatus::Cancelled,
                'closed_at' => now(),
            ]);

            return $request->fresh();
        });
    }
}
