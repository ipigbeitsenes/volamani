<?php

namespace App\Actions\Documents;

use App\Enums\DocumentStatus;
use App\Models\Document;

class DecideQuotationAction
{
    public function accept(Document $quotation): Document
    {
        $quotation->update([
            'status' => DocumentStatus::Accepted,
            'accepted_at' => now(),
        ]);

        return $quotation->fresh();
    }

    public function decline(Document $quotation): Document
    {
        $quotation->update([
            'status' => DocumentStatus::Declined,
            'declined_at' => now(),
        ]);

        return $quotation->fresh();
    }
}
