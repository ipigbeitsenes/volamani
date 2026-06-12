<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use Illuminate\Notifications\Messages\MailMessage;

class WelcomeNotification extends VolamaniNotification
{
    public function category(): NotificationCategory
    {
        return NotificationCategory::Account;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to Volamani!')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Welcome to Volamani — Africa\'s Digital Business Ecosystem.')
            ->line('You can now explore thousands of digital products and services from verified African vendors.')
            ->action('Explore Marketplace', route('marketplace.products.index'))
            ->line('If you\'re looking to sell, set up your vendor account to start reaching buyers across Africa.')
            ->salutation('The Volamani Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => $this->category()->value,
            'icon'     => $this->category()->icon(),
            'title'    => 'Welcome to Volamani!',
            'message'  => 'Your account has been created. Start exploring the marketplace.',
            'url'      => route('marketplace.products.index'),
        ];
    }
}
