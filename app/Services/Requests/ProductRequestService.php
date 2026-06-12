<?php

namespace App\Services\Requests;

use App\Actions\Requests\AcceptQuotationAction;
use App\Actions\Requests\CloseRequestAction;
use App\Actions\Requests\CreateRequestAction;
use App\Actions\Requests\SubmitQuotationAction;
use App\Models\ProductRequest;
use App\Models\ProductRequestQuotation;
use App\Models\User;
use App\Models\Vendor;
use App\Services\BaseService;

class ProductRequestService extends BaseService
{
    public function __construct(
        private CreateRequestAction   $createAction,
        private SubmitQuotationAction $submitAction,
        private AcceptQuotationAction $acceptAction,
        private CloseRequestAction    $closeAction,
    ) {}

    public function createRequest(User $buyer, array $data): ProductRequest
    {
        return $this->createAction->execute($buyer, $data);
    }

    public function submitQuotation(ProductRequest $request, Vendor $vendor, array $data): ProductRequestQuotation
    {
        return $this->submitAction->execute($request, $vendor, $data);
    }

    public function acceptQuotation(ProductRequest $request, ProductRequestQuotation $quotation): ProductRequest
    {
        return $this->acceptAction->execute($request, $quotation);
    }

    public function closeRequest(ProductRequest $request): ProductRequest
    {
        return $this->closeAction->execute($request);
    }

    public function withdrawQuotation(ProductRequestQuotation $quotation, Vendor $vendor): void
    {
        abort_unless($quotation->vendor_id === $vendor->id, 403);
        abort_unless($quotation->canBeWithdrawn(), 422, 'This quotation cannot be withdrawn.');

        $quotation->update([
            'status'        => \App\Enums\QuotationStatus::Withdrawn,
            'withdrawn_at'  => now(),
        ]);

        $quotation->request->decrement('quotations_count');
    }
}
