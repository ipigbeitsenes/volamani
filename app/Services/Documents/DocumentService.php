<?php

namespace App\Services\Documents;

use App\Actions\Documents\CancelDocumentAction;
use App\Actions\Documents\ConvertQuotationAction;
use App\Actions\Documents\CreateDocumentAction;
use App\Actions\Documents\DecideQuotationAction;
use App\Actions\Documents\RecordPaymentAction;
use App\Actions\Documents\SendDocumentAction;
use App\Actions\Documents\SignContractAction;
use App\Actions\Documents\UpdateDocumentAction;
use App\Enums\DocumentType;
use App\Enums\TransactionType;
use App\Models\Document;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vendor;
use App\Repositories\Documents\DocumentRepository;
use App\Services\Wallet\WalletService;

class DocumentService
{
    public function __construct(
        private CreateDocumentAction $createAction,
        private UpdateDocumentAction $updateAction,
        private SendDocumentAction $sendAction,
        private RecordPaymentAction $recordPaymentAction,
        private ConvertQuotationAction $convertAction,
        private DecideQuotationAction $decideAction,
        private CancelDocumentAction $cancelAction,
        private SignContractAction $signAction,
        private WalletService $walletService,
        private DocumentRepository $repo,
    ) {}

    public function create(Vendor $vendor, DocumentType $type, array $data, User $creator): Document
    {
        return $this->createAction->execute($vendor, $type, $data, $creator);
    }

    /** Create a document issued by Volamani itself (no vendor). */
    public function createForPlatform(DocumentType $type, array $data, User $creator): Document
    {
        return $this->createAction->execute(null, $type, $data, $creator, 'platform');
    }

    public function update(Document $document, array $data): Document
    {
        return $this->updateAction->execute($document, $data);
    }

    public function send(Document $document): Document
    {
        return $this->sendAction->execute($document);
    }

    public function recordPayment(Document $document, int $amountKobo, ?Payment $payment = null): Document
    {
        return $this->recordPaymentAction->execute($document, $amountKobo, $payment);
    }

    public function convert(Document $quotation): Document
    {
        return $this->convertAction->execute($quotation);
    }

    public function accept(Document $quotation): Document
    {
        return $this->decideAction->accept($quotation);
    }

    public function decline(Document $quotation): Document
    {
        return $this->decideAction->decline($quotation);
    }

    public function cancel(Document $document): Document
    {
        return $this->cancelAction->execute($document);
    }

    /** Record a client's e-signature on a contract of sale. */
    public function sign(Document $contract, string $signedName, ?string $ip = null): Document
    {
        return $this->signAction->execute($contract, $signedName, $ip);
    }

    public function delete(Document $document): void
    {
        $document->delete();
    }

    /**
     * Hook: an online gateway payment for an invoice succeeded. Settles the
     * invoice and credits the vendor's wallet — the client paid through the
     * platform gateway, so the platform forwards the funds to the vendor.
     */
    public function settleFromPayment(Payment $payment): ?Document
    {
        $document = $payment->payable;

        if (! $document instanceof Document || ! $document->isInvoice()) {
            return null;
        }

        if ($document->isPaid()) {
            return $document; // idempotent
        }

        $document = $this->recordPayment($document, $payment->amount, $payment);

        // Platform-issued invoices are Volamani revenue — settle and stop; there
        // is no vendor to forward funds to. Vendor invoices credit the vendor.
        if ($document->isPlatformIssued()) {
            return $document;
        }

        if ($vendorUser = $document->vendor?->user) {
            $wallet = $this->walletService->getOrCreate($vendorUser);
            $this->walletService->credit(
                $wallet,
                $payment->amount,
                TransactionType::Credit,
                "Invoice payment — {$document->number}",
                $document,
            );
        }

        return $document;
    }

    // ─── Query passthroughs ──────────────────────────────────────────────────────

    public function forVendor(Vendor $vendor, DocumentType $type, int $perPage = 15, array $filters = [])
    {
        return $this->repo->forVendor($vendor, $type, $perPage, $filters);
    }

    public function forClient(User $user, ?DocumentType $type = null, int $perPage = 15)
    {
        return $this->repo->forClient($user, $type, $perPage);
    }

    public function vendorStats(Vendor $vendor): array
    {
        return $this->repo->vendorStats($vendor);
    }

    public function forPlatform(?DocumentType $type = null, int $perPage = 15, array $filters = [])
    {
        return $this->repo->forPlatform($type, $perPage, $filters);
    }

    public function platformStats(): array
    {
        return $this->repo->platformStats();
    }
}
