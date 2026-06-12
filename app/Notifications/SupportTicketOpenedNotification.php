<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\Dispute;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Sent to the vendor and the support/admin team when a buyer opens a support
 * ticket against a purchase. This is how a ticket is "forwarded" — the vendor
 * is alerted to respond and support is alerted to triage. The link points each
 * recipient at the view they're allowed to see (admin console vs. the
 * marketplace thread).
 */
class SupportTicketOpenedNotification extends VolamaniNotification
{
    public function __construct(private Dispute $dispute) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Escrow;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $t = $this->dispute;

        return (new MailMessage)
            ->subject("Support ticket {$t->reference} opened")
            ->greeting("Hello {$notifiable->name},")
            ->line("A buyer has opened a support ticket regarding purchase {$t->escrow->reference}.")
            ->line('Reason: ' . $t->reason->label())
            ->line('The funds for this purchase are now held until the ticket is resolved.')
            ->action('Review Ticket', $this->urlFor($notifiable))
            ->line('Please respond promptly so we can resolve this for the buyer.')
            ->salutation('The Volamani Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => $this->category()->value,
            'icon'     => $this->category()->icon(),
            'title'    => "Support ticket {$this->dispute->reference}",
            'message'  => 'A buyer opened a support ticket regarding purchase '
                . $this->dispute->escrow->reference . '.',
            'url'      => $this->urlFor($notifiable),
        ];
    }

    /** Admins get the back-office view; the vendor gets the marketplace thread. */
    private function urlFor(object $notifiable): string
    {
        if (method_exists($notifiable, 'hasRole') && $notifiable->hasRole('admin')) {
            return route('admin.disputes.show', $this->dispute);
        }

        return route('disputes.show', $this->dispute);
    }
}
