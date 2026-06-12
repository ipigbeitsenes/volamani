<?php

namespace App\Actions\Documents;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class RecordPaymentAction
{
    /**
     * Apply a payment to an invoice (manual entry by the vendor, or an online
     * gateway payment). Advances the status to partial/paid as appropriate.
     */
    public function execute(Document $document, int $amountKobo, ?Payment $payment = null): Document
    {
        return DB::transaction(function () use ($document, $amountKobo, $payment) {
            $locked = Document::where('id', $document->id)->lockForUpdate()->first();

            $newPaid = min($locked->total, $locked->amount_paid + max(0, $amountKobo));
            $settled = $newPaid >= $locked->total;

            $locked->update([
                'amount_paid' => $newPaid,
                'status'      => $settled ? DocumentStatus::Paid : DocumentStatus::Partial,
                'paid_at'     => $settled ? ($locked->paid_at ?? now()) : $locked->paid_at,
                'payment_id'  => $payment?->id ?? $locked->payment_id,
            ]);

            return $locked->fresh();
        });
    }
}
