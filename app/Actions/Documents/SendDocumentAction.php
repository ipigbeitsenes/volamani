<?php

namespace App\Actions\Documents;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Notifications\DocumentSentNotification;
use Illuminate\Support\Facades\Notification;

class SendDocumentAction
{
    /**
     * Mark a document as sent and deliver it to the client. Clients with an
     * account get an in-app + email notification; clients without one are
     * emailed the public share link directly (on-demand, no account needed).
     */
    public function execute(Document $document): Document
    {
        $document->update([
            'status' => DocumentStatus::Sent,
            'sent_at' => $document->sent_at ?? now(),
        ]);

        if ($document->client) {
            $document->client->notify(new DocumentSentNotification($document));
        } elseif ($document->client_email) {
            Notification::route('mail', $document->client_email)
                ->notify(new DocumentSentNotification($document));
        }

        return $document->fresh();
    }
}
