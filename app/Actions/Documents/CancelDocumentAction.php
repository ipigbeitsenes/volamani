<?php

namespace App\Actions\Documents;

use App\Enums\DocumentStatus;
use App\Models\Document;

class CancelDocumentAction
{
    public function execute(Document $document): Document
    {
        $document->update(['status' => DocumentStatus::Cancelled]);

        return $document->fresh();
    }
}
