<?php

namespace App\Http\Controllers\Invoices;

use App\Enums\DocumentStatus;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\Documents\DocumentService;
use App\Services\Payment\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        private DocumentService $documentService,
        private PaymentService $paymentService,
    ) {}

    public function index(): View
    {
        $documents = $this->documentService->forClient(auth()->user());

        return view('marketplace.invoices.index', compact('documents'));
    }

    public function show(Document $invoice): View
    {
        $this->authorizeClient($invoice);

        // First open marks it viewed.
        if ($invoice->status === DocumentStatus::Sent) {
            $invoice->update(['status' => DocumentStatus::Viewed, 'viewed_at' => now()]);
        }

        $invoice->load('items', 'vendor');

        return view('marketplace.invoices.show', ['document' => $invoice]);
    }

    public function download(Document $invoice): View
    {
        $this->authorizeClient($invoice);
        $invoice->load('items', 'vendor', 'client');

        return view('documents.print', ['document' => $invoice]);
    }

    public function accept(Document $invoice): RedirectResponse
    {
        $this->authorizeClient($invoice);
        abort_unless($invoice->isQuotation(), 422);

        $this->documentService->accept($invoice);
        $this->flashSuccess('Quotation accepted. The vendor has been notified.');

        return back();
    }

    public function decline(Document $invoice): RedirectResponse
    {
        $this->authorizeClient($invoice);
        abort_unless($invoice->isQuotation(), 422);

        $this->documentService->decline($invoice);
        $this->flashSuccess('Quotation declined.');

        return back();
    }

    public function pay(Document $invoice): RedirectResponse
    {
        $this->authorizeClient($invoice);
        abort_unless($invoice->isInvoice(), 422);
        abort_if($invoice->balanceDue() <= 0, 422, 'Nothing left to pay.');

        $result = $this->paymentService->initiatePaystackPayment(
            auth()->user(),
            $invoice->balanceDue(),
            $invoice,
            ['payable_type' => 'document'],
        );

        return redirect()->away($result['authorization_url']);
    }

    private function authorizeClient(Document $document): void
    {
        abort_unless($document->client_id === auth()->id(), 403);
    }
}
