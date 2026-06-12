<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\Document;
use Illuminate\Notifications\Messages\MailMessage;

class DocumentSentNotification extends VolamaniNotification
{
    public function __construct(private Document $document) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Invoices;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $doc   = $this->document;
        $label = $doc->type->label();

        $mail = (new MailMessage)
            ->subject("{$label} {$doc->number} from {$doc->vendor->business_name}")
            ->greeting("Hello {$doc->client_name},")
            ->line("You have received {$label} {$doc->number} for " . money($doc->total) . '.');

        if ($doc->isInvoice() && $doc->due_date) {
            $mail->line('Due by ' . $doc->due_date->format('d M Y') . '.');
        } elseif ($doc->isQuotation() && $doc->valid_until) {
            $mail->line('Valid until ' . $doc->valid_until->format('d M Y') . '.');
        }

        return $mail
            ->action("View {$label}", route('invoices.show', $doc))
            ->salutation('Thank you, ' . $doc->vendor->business_name);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => $this->category()->value,
            'icon'     => $this->category()->icon(),
            'title'    => "{$this->document->type->label()} {$this->document->number}",
            'message'  => "{$this->document->vendor->business_name} sent you a {$this->document->type->value} for " . money($this->document->total) . '.',
            'url'      => route('invoices.show', $this->document),
        ];
    }
}
