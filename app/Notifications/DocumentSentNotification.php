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
        $doc = $this->document;
        $label = $doc->type->label();
        $issuer = $doc->issuerName();

        $mail = (new MailMessage)
            ->subject("{$label} {$doc->number} from {$issuer}")
            ->greeting("Hello {$doc->client_name},")
            ->line("You have received {$label} {$doc->number} for ".money($doc->total).'.');

        if ($doc->isInvoice() && $doc->due_date) {
            $mail->line('Due by '.$doc->due_date->format('d M Y').'.');
        } elseif ($doc->isQuotation() && $doc->valid_until) {
            $mail->line('Valid until '.$doc->valid_until->format('d M Y').'.');
        }

        if ($doc->isInvoice() && $doc->balanceDue() > 0) {
            $mail->line('You can view and pay this invoice securely online using the button below.');
        } elseif ($doc->isContract()) {
            $mail->line('Please review and sign this contract online using the button below.');
        }

        $action = match (true) {
            $doc->isInvoice() => "View & Pay {$label}",
            $doc->isContract() => "View & Sign {$label}",
            default => "View {$label}",
        };

        return $mail
            ->action($action, $doc->publicUrl())
            ->salutation('Thank you, '.$issuer);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => $this->category()->value,
            'icon' => $this->document->type->icon(),
            'title' => "{$this->document->type->label()} {$this->document->number}",
            'message' => "{$this->document->issuerName()} sent you a ".strtolower($this->document->type->label()).' for '.money($this->document->total).'.',
            'url' => $this->document->client_id ? route('invoices.show', $this->document) : $this->document->publicUrl(),
        ];
    }
}
