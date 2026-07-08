<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\BuyerStrike;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Sent to a buyer (and the admin team) when an abuse strike is recorded — after
 * a dispute they raised is rejected or a chargeback they filed is overturned.
 * Enough strikes flag, then block, the account from purchasing.
 */
class BuyerStrikeIssuedNotification extends VolamaniNotification
{
    public function __construct(
        private BuyerStrike $strike,
        private bool $suspended = false,
    ) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Verification;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $s = $this->strike;
        $isAdmin = method_exists($notifiable, 'hasRole') && $notifiable->hasRole('admin');

        $mail = (new MailMessage)->greeting("Hello {$notifiable->name},");

        if ($isAdmin) {
            return $mail
                ->subject('Buyer abuse strike recorded')
                ->line("A strike was recorded against a buyer account: {$s->reason->label()}.")
                ->when($s->note, fn ($m) => $m->line("Note: {$s->note}"))
                ->when($this->suspended, fn ($m) => $m->line('This buyer has now been suspended from new purchases.'))
                ->action('Review buyer', route('admin.buyers.show', $s->user_id))
                ->salutation('Volamani');
        }

        return $mail
            ->subject($this->suspended ? 'Your account has been restricted' : 'A note about your account')
            ->line('A buyer-protection claim you made was reviewed and not upheld: ' . $s->reason->label() . '.')
            ->when($s->note, fn ($m) => $m->line("Note: {$s->note}"))
            ->line($this->suspended
                ? 'Because of repeated unupheld claims, your account has been temporarily restricted from new purchases. Please contact support if you believe this is a mistake.'
                : 'Repeated unupheld claims can lead to your account being restricted. Please only open disputes for genuine issues.')
            ->action('Buyer Protection Policy', route('buyer-protection'))
            ->salutation('The Volamani Team');
    }

    public function toArray(object $notifiable): array
    {
        $isAdmin = method_exists($notifiable, 'hasRole') && $notifiable->hasRole('admin');

        return [
            'category' => $this->category()->value,
            'icon'     => $this->category()->icon(),
            'title'    => $isAdmin ? 'Buyer strike recorded' : ($this->suspended ? 'Account restricted' : 'Account notice'),
            'message'  => ($isAdmin ? 'A buyer strike was recorded: ' : 'A claim was not upheld: ') . $this->strike->reason->label() . '.',
            'url'      => $isAdmin ? route('admin.buyers.show', $this->strike->user_id) : route('disputes.index'),
        ];
    }
}
