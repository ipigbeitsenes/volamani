<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\Chargeback;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Sent to the vendor and admin team when a payment-gateway chargeback lands.
 * The vendor is prompted to submit evidence to contest it; admins are alerted
 * to oversee the outcome.
 */
class ChargebackOpenedNotification extends VolamaniNotification
{
    public function __construct(private Chargeback $chargeback) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Payments;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $c = $this->chargeback;

        return (new MailMessage)
            ->subject("Chargeback {$c->reference} opened")
            ->greeting("Hello {$notifiable->name},")
            ->line('A chargeback of ' . money($c->amount) . ' has been opened on a payment.')
            ->when($c->reason, fn ($m) => $m->line("Reason given: {$c->reason}"))
            ->line('The disputed funds have been held or recovered while this is resolved. If you can prove the order was legitimate, submit your evidence promptly.')
            ->action('Review Chargeback', $this->urlFor($notifiable))
            ->salutation('The Volamani Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => $this->category()->value,
            'icon'     => $this->category()->icon(),
            'title'    => "Chargeback {$this->chargeback->reference}",
            'message'  => 'A chargeback of ' . money($this->chargeback->amount) . ' was opened on a payment.',
            'url'      => $this->urlFor($notifiable),
        ];
    }

    private function urlFor(object $notifiable): string
    {
        if (method_exists($notifiable, 'hasRole') && $notifiable->hasRole('admin')) {
            return route('admin.chargebacks.show', $this->chargeback);
        }

        return route('vendor.chargebacks.show', $this->chargeback);
    }
}
