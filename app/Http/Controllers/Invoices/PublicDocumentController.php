<?php

namespace App\Http\Controllers\Invoices;

use App\Enums\DocumentStatus;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\Documents\DocumentService;
use App\Services\Payment\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Client-facing, NO-LOGIN view of an invoice or quotation. Reached through the
 * unguessable share link the vendor sends. The token is the authorisation —
 * there is no auth middleware here. Clients can view, pay (invoices) or
 * accept/decline (quotations) without a Volamani account.
 */
class PublicDocumentController extends Controller
{
    public function __construct(
        private DocumentService $documentService,
        private PaymentService $paymentService,
    ) {}

    public function show(string $token): View
    {
        $document = $this->resolve($token);

        // First open by the client marks it viewed.
        if ($document->status === DocumentStatus::Sent) {
            $document->update(['status' => DocumentStatus::Viewed, 'viewed_at' => now()]);
        }

        $document->load('items', 'vendor');

        return view('documents.public', compact('document'));
    }

    public function print(string $token): View
    {
        $document = $this->resolve($token);
        $document->load('items', 'vendor', 'client');

        return view('documents.print', compact('document'));
    }

    public function pay(string $token): RedirectResponse
    {
        $document = $this->resolve($token);

        abort_unless($document->isInvoice(), 422, 'Only invoices can be paid.');
        abort_if($document->balanceDue() <= 0, 422, 'This invoice is already settled.');
        abort_if($document->status === DocumentStatus::Cancelled, 422, 'This invoice was cancelled.');

        // The Payment needs a platform user. Attribute to the linked client if
        // there is one, otherwise to the vendor as payee of record. The client's
        // own email is forwarded to Paystack for the receipt regardless.
        $payer = $document->client ?? $document->vendor->user;
        abort_if($payer === null, 422, 'This invoice cannot accept payments yet.');

        $result = $this->paymentService->initiatePaystackPayment(
            $payer,
            $document->balanceDue(),
            $document,
            ['payable_type' => 'document', 'public_invoice' => $document->public_token],
            $document->client_email ?: $payer->email,
        );

        return redirect()->away($result['authorization_url']);
    }

    public function accept(string $token): RedirectResponse
    {
        $document = $this->resolve($token);
        abort_unless($document->isQuotation(), 422);

        $this->documentService->accept($document);
        $this->flashSuccess('Quotation accepted — the vendor has been notified.');

        return redirect()->route('public.documents.show', $token);
    }

    public function decline(string $token): RedirectResponse
    {
        $document = $this->resolve($token);
        abort_unless($document->isQuotation(), 422);

        $this->documentService->decline($document);
        $this->flashSuccess('Quotation declined.');

        return redirect()->route('public.documents.show', $token);
    }

    public function sign(string $token, Request $request): RedirectResponse
    {
        $document = $this->resolve($token);
        abort_unless($document->isContract(), 422, 'Only contracts can be signed.');
        abort_if($document->isSigned(), 422, 'This contract has already been signed.');
        abort_unless(in_array($document->status, [DocumentStatus::Sent, DocumentStatus::Viewed]), 422);

        $data = $request->validate([
            'signed_name' => ['required', 'string', 'max:120'],
            'agree' => ['accepted'],
        ]);

        $this->documentService->sign($document, $data['signed_name'], $request->ip());
        $this->flashSuccess('Contract signed. A copy has been recorded — thank you.');

        return redirect()->route('public.documents.show', $token);
    }

    private function resolve(string $token): Document
    {
        return Document::where('public_token', $token)->firstOrFail();
    }
}
