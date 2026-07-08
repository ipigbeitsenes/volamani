<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\DocumentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Documents\DocumentRequest;
use App\Models\Document;
use App\Services\Documents\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function __construct(private DocumentService $documentService) {}

    public function index(Request $request): View
    {
        $vendor    = $request->user()->vendor;
        $type      = $this->type();
        $filters   = $request->only('status', 'search');
        $documents = $this->documentService->forVendor($vendor, $type, 15, $filters);
        $stats     = $type === DocumentType::Invoice ? $this->documentService->vendorStats($vendor) : null;

        return view('vendor.documents.index', [
            'documents' => $documents,
            'type'      => $type,
            'routeBase' => $this->routeBase(),
            'filters'   => $filters,
            'stats'     => $stats,
        ]);
    }

    public function create(): View
    {
        return view('vendor.documents.form', [
            'document'  => new Document(['type' => $this->type()]),
            'type'      => $this->type(),
            'routeBase' => $this->routeBase(),
        ]);
    }

    public function store(DocumentRequest $request): RedirectResponse
    {
        $document = $this->documentService->create(
            $request->user()->vendor,
            $this->type(),
            $request->documentData(),
            $request->user(),
        );

        $this->flashSuccess($this->type()->label() . " {$document->number} created.");

        return redirect()->route($this->routeBase() . '.show', $document);
    }

    public function show(Document $document): View
    {
        $this->authorizeDocument($document);
        $document->load('items', 'client', 'convertedTo');

        return view('vendor.documents.show', [
            'document'  => $document,
            'type'      => $document->type,
            'routeBase' => $this->routeBaseFor($document),
        ]);
    }

    public function edit(Document $document): View
    {
        $this->authorizeDocument($document);
        abort_unless($document->isEditable(), 403, 'Only drafts can be edited.');
        $document->load('items');

        return view('vendor.documents.form', [
            'document'  => $document,
            'type'      => $document->type,
            'routeBase' => $this->routeBaseFor($document),
        ]);
    }

    public function update(DocumentRequest $request, Document $document): RedirectResponse
    {
        $this->authorizeDocument($document);
        abort_unless($document->isEditable(), 403, 'Only drafts can be edited.');

        $this->documentService->update($document, $request->documentData());

        $this->flashSuccess("{$document->number} updated.");

        return redirect()->route($this->routeBaseFor($document) . '.show', $document);
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->authorizeDocument($document);
        $base = $this->routeBaseFor($document);

        $this->documentService->delete($document);

        $this->flashSuccess("{$document->number} deleted.");

        return redirect()->route($base . '.index');
    }

    public function send(Document $document): RedirectResponse
    {
        $this->authorizeDocument($document);

        $this->documentService->send($document);

        $this->flashSuccess("{$document->number} sent to {$document->client_name}.");

        return back();
    }

    public function recordPayment(Request $request, Document $document): RedirectResponse
    {
        $this->authorizeDocument($document);
        abort_unless($document->isInvoice(), 422, 'Only invoices accept payments.');

        $data = $request->validate(['amount' => ['required', 'numeric', 'min:0.01']]);

        $this->documentService->recordPayment($document, to_kobo((float) $data['amount']));

        $this->flashSuccess('Payment recorded.');

        return back();
    }

    public function convert(Document $document): RedirectResponse
    {
        $this->authorizeDocument($document);

        $invoice = $this->documentService->convert($document);

        $this->flashSuccess("Quotation converted to invoice {$invoice->number}.");

        return redirect()->route('vendor.invoices.show', $invoice);
    }

    public function cancel(Document $document): RedirectResponse
    {
        $this->authorizeDocument($document);

        $this->documentService->cancel($document);

        $this->flashSuccess("{$document->number} cancelled.");

        return back();
    }

    public function print(Document $document): View
    {
        $this->authorizeDocument($document);
        $document->load('items', 'vendor', 'client');

        return view('documents.print', compact('document'));
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function authorizeDocument(Document $document): void
    {
        abort_unless($document->vendor_id === auth()->user()->vendor?->id, 403);
    }

    private function type(): DocumentType
    {
        return match (true) {
            request()->routeIs('vendor.estimates.*') => DocumentType::Quotation,
            request()->routeIs('vendor.contracts.*') => DocumentType::Contract,
            default                                  => DocumentType::Invoice,
        };
    }

    private function routeBase(): string
    {
        return $this->routeBaseForType($this->type());
    }

    private function routeBaseFor(Document $document): string
    {
        return $this->routeBaseForType($document->type);
    }

    private function routeBaseForType(DocumentType $type): string
    {
        return match ($type) {
            DocumentType::Quotation => 'vendor.estimates',
            DocumentType::Contract  => 'vendor.contracts',
            default                 => 'vendor.invoices',
        };
    }
}
