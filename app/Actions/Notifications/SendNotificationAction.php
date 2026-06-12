<?php

namespace App\Actions\Notifications;

use App\Enums\NotificationCategory;
use App\Models\User;
use App\Notifications\PlatformNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class SendNotificationAction
{
    /**
     * Raise a data-driven notification to one or many users. Channel selection
     * (in-app / email) is resolved per-recipient from their preferences inside
     * the notification's via(). Honours the global notifications kill-switch.
     */
    public function execute(
        User|Collection $recipients,
        NotificationCategory $category,
        string $title,
        string $message,
        ?string $url = null,
        ?string $actionLabel = null,
        bool $email = true,
    ): void {
        if (! settings('notifications_enabled', true)) {
            return;
        }

        if ($recipients instanceof Collection && $recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients,
            new PlatformNotification($category, $title, $message, $url, $actionLabel, $email),
        );
    }
}
