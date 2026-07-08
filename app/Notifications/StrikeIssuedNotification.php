<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\VendorStrike;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Sent to a vendor (and the admin team) when a strike is recorded against the
 * store — typically after a lost dispute or chargeback. Repeated strikes lead
 * to automatic suspension.
 */
class StrikeIssuedNotification extends VolamaniNotification
{
    public function __construct(private VendorStrike $strike) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Verification;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $s = $this->strike;

        return (new MailMessage)
            ->subject('A strike was recorded on your store')
            ->greeting("Hello {$notifiable->name},")
            ->line('A strike has been recorded against your store: ' . $s->reason->label() . '.')
            ->when($s->note, fn ($m) => $m->line("Note: {$s->note}"))
            ->line('Accumulating strikes can lead to your store being suspended. Please review our buyer-protection policy.')
            ->action('View Buyer Protection', route('buyer-protection'))
            ->salutation('The Volamani Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => $this->category()->value,
            'icon'     => $this->category()->icon(),
            'title'    => 'Store strike recorded',
            'message'  => 'A strike was recorded: ' . $this->strike->reason->label() . '.',
            'url'      => $this->urlFor($notifiable),
        ];
    }

    private function urlFor(object $notifiable): string
    {
        if (method_exists($notifiable, 'hasRole') && $notifiable->hasRole('admin')) {
            return route('admin.vendors.show', $this->strike->vendor_id);
        }

        return route('vendor.dashboard');
    }
}
