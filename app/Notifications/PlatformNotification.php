<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Data-driven notification used by NotificationService::send() so most events
 * across the platform can raise a notification without a bespoke class. The
 * stored `data` payload is what the in-app notification centre renders.
 */
class PlatformNotification extends VolamaniNotification
{
    public function __construct(
        public NotificationCategory $category,
        public string $title,
        public string $message,
        public ?string $url = null,
        public ?string $actionLabel = null,
        public bool $email = true,
    ) {}

    public function category(): NotificationCategory
    {
        return $this->category;
    }

    protected function sendsEmail(): bool
    {
        return $this->email;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->greeting('Hello '.($notifiable->name ?? 'there').',')
            ->line($this->message);

        if ($this->url) {
            $mail->action($this->actionLabel ?? 'View details', $this->url);
        }

        return $mail->salutation('The Volamani Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => $this->category->value,
            'icon' => $this->category->icon(),
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
        ];
    }
}
