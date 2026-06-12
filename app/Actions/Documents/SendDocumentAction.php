<?php

namespace App\Actions\Documents;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Notifications\DocumentSentNotification;

class SendDocumentAction
{
    /**
     * Mark a document as sent and notify the client if they have an account.
     */
    public function execute(Document $document): Document
    {
        $document->update([
            'status'  => DocumentStatus::Sent,
            'sent_at' => $document->sent_at ?? now(),
        ]);

        if ($document->client) {
            $document->client->notify(new DocumentSentNotification($document));
        }

        return $document->fresh();
    }
}
