<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DocumentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Documents\PlatformDocumentRequest;
use App\Models\Document;
use App\Services\Documents\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Volamani-issued documents: invoices and contracts of sale the platform sends
 * to its own users (vendors / clients). Reuses the shared vendor/documents.*
 * views via the $docLayout param. Issuer is always 'platform' (no vendor).
 */
class PlatformDocumentController extends Controller
{
    public function __construct(private DocumentService $documentService) {}

    public function index(Request $request): View
    {
        $type      = $this->typeFromRequest($request) ?? DocumentType::Invoice;
        $filters   = $request->only('status', 'search');
        $documents = $this->documentService->forPlatform($type, 15, $filters);
        $stats     = $type === DocumentType::Invoice ? $this->documentService->platformStats() : null;

        return view('vendor.documents.index', [
            'documents' => $documents,
            'type'      => $type,
            'platformTabs' => true,   // render Invoices | Contracts tabs
            'routeBase' => 'admin.documents',
            'docLayout' => 'layouts.admin',
            'filters'   => $filters,
            'stats'     => $stats,
        ]);
    }

    public function create(Request $request): View
    {
        $type = $this->typeFromRequest($request) ?? DocumentType::Invoice;

        return view('vendor.documents.form', [
            'document'  => new Document(['type' => $type]),
            'type'      => $type,
            'routeBase' => 'admin.documents',
            'docLayout' => 'layouts.admin',
        ]);
    }

    public function store(PlatformDocumentRequest $request): RedirectResponse
    {
        $type     = $this->typeFromRequest($request) ?? DocumentType::Invoice;
        $document = $this->documentService->createForPlatform($type, $request->documentData(), $request->user());

        $this->flashSuccess($type->label() . " {$document->number} created.");

        return redirect()->route('admin.documents.show', $document);
    }

    public function show(Document $document): View
    {
        $this->authorizePlatform($document);
        $document->load('items', 'client');

        return view('vendor.documents.show', [
            'document'  => $document,
            'type'      => $document->type,
            'routeBase' => 'admin.documents',
            'docLayout' => 'layouts.admin',
        ]);
    }

    public function edit(Document $document): View
    {
        $this->authorizePlatform($document);
        abort_unless($document->isEditable(), 403, 'Only drafts can be edited.');
        $document->load('items');

        return view('admin.documents.form', [
            'document'  => $document,
            'type'      => $document->type,
            'routeBase' => 'admin.documents',
            'docLayout' => 'layouts.admin',
        ]);
    }

    public function update(PlatformDocumentRequest $request, Document $document): RedirectResponse
    {
        $this->authorizePlatform($document);
        abort_unless($document->isEditable(), 403, 'Only drafts can be edited.');

        $this->documentService->update($document, $request->documentData());
        $this->flashSuccess("{$document->number} updated.");

        return redirect()->route('admin.documents.show', $document);
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->authorizePlatform($document);
        $this->documentService->delete($document);
        $this->flashSuccess("{$document->number} deleted.");

        return redirect()->route('admin.documents.index');
    }

    public function send(Document $document): RedirectResponse
    {
        $this->authorizePlatform($document);
        $this->documentService->send($document);
        $this->flashSuccess("{$document->number} sent to {$document->client_name}.");

        return back();
    }

    public function recordPayment(Request $request, Document $document): RedirectResponse
    {
        $this->authorizePlatform($document);
        abort_unless($document->isInvoice(), 422, 'Only invoices accept payments.');

        $data = $request->validate(['amount' => ['required', 'numeric', 'min:0.01']]);
        $this->documentService->recordPayment($document, to_kobo((float) $data['amount']));
        $this->flashSuccess('Payment recorded.');

        return back();
    }

    public function cancel(Document $document): RedirectResponse
    {
        $this->authorizePlatform($document);
        $this->documentService->cancel($document);
        $this->flashSuccess("{$document->number} cancelled.");

        return back();
    }

    public function print(Document $document): View
    {
        $this->authorizePlatform($document);
        $document->load('items', 'client');

        return view('documents.print', compact('document'));
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function authorizePlatform(Document $document): void
    {
        abort_unless($document->isPlatformIssued(), 404);
    }

    private function typeFromRequest(Request $request): ?DocumentType
    {
        return match ($request->input('type')) {
            'invoice'  => DocumentType::Invoice,
            'contract' => DocumentType::Contract,
            default    => null,
        };
    }
}
