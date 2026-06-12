<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Base class for every platform notification. Subclasses declare their
 * {@see category()}; this class resolves the delivery channels from the
 * notifiable's per-category preferences so opt-outs are honoured everywhere
 * without each notification re-implementing the logic.
 */
abstract class VolamaniNotification extends Notification implements ShouldQueue
{
    use Queueable;

    abstract public function category(): NotificationCategory;

    /** Override to disable the email channel for a specific notification. */
    protected function sendsEmail(): bool
    {
        return true;
    }

    public function via(object $notifiable): array
    {
        $wants = fn (string $channel): bool => ! method_exists($notifiable, 'wantsNotification')
            || $notifiable->wantsNotification($this->category(), $channel);

        $channels = [];

        if ($wants('database')) {
            $channels[] = 'database';
        }

        if ($this->sendsEmail() && $wants('email')) {
            $channels[] = 'mail';
        }

        return $channels;
    }
}
