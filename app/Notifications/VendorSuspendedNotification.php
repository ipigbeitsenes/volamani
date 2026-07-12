<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\Vendor;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Sent to a vendor when their store is automatically suspended for reaching the
 * strike threshold. Reinstatement is a manual admin decision.
 */
class VendorSuspendedNotification extends VolamaniNotification
{
    public function __construct(private Vendor $vendor) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Account;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your store has been suspended')
            ->greeting("Hello {$notifiable->name},")
            ->line("Your store “{$this->vendor->business_name}” has been suspended after reaching our strike threshold.")
            ->line('Existing orders and escrow are unaffected, but you cannot take on new business while suspended.')
            ->line('Please contact support to discuss reinstatement.')
            ->salutation('The Volamani Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => $this->category()->value,
            'icon' => $this->category()->icon(),
            'title' => 'Store suspended',
            'message' => "Your store “{$this->vendor->business_name}” has been suspended for repeated strikes.",
            'url' => route('vendor.dashboard'),
        ];
    }
}
