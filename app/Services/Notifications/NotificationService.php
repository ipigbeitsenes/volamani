<?php

namespace App\Services\Notifications;

use App\Actions\Notifications\SendNotificationAction;
use App\Actions\Notifications\UpdatePreferencesAction;
use App\Enums\NotificationCategory;
use App\Models\User;
use App\Repositories\Notifications\NotificationRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class NotificationService
{
    public function __construct(
        private SendNotificationAction    $sendAction,
        private UpdatePreferencesAction   $updatePreferencesAction,
        private NotificationRepository    $repo,
    ) {}

    /**
     * Raise a notification to one or many users. The single entry point every
     * other module should call to notify someone of an event.
     */
    public function send(
        User|Collection $recipients,
        NotificationCategory $category,
        string $title,
        string $message,
        ?string $url = null,
        ?string $actionLabel = null,
        bool $email = true,
    ): void {
        $this->sendAction->execute($recipients, $category, $title, $message, $url, $actionLabel, $email);
    }

    // ─── Reads ───────────────────────────────────────────────────────────────

    public function forUser(User $user, int $perPage = 20, ?string $filter = null): LengthAwarePaginator
    {
        return $this->repo->forUser($user, $perPage, $filter);
    }

    public function recent(User $user, int $limit = 6): Collection
    {
        return $this->repo->recent($user, $limit);
    }

    public function unreadCount(User $user): int
    {
        return $this->repo->unreadCount($user);
    }

    public function find(User $user, string $id): ?\Illuminate\Notifications\DatabaseNotification
    {
        return $this->repo->find($user, $id);
    }

    // ─── State changes ────────────────────────────────────────────────────────

    public function markAsRead(User $user, string $id): void
    {
        $this->repo->find($user, $id)?->markAsRead();
    }

    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }

    public function delete(User $user, string $id): void
    {
        $this->repo->find($user, $id)?->delete();
    }

    public function clearAll(User $user): void
    {
        $user->notifications()->delete();
    }

    // ─── Preferences ──────────────────────────────────────────────────────────

    public function preferenceMatrix(User $user): array
    {
        return $this->repo->preferenceMatrix($user);
    }

    public function updatePreferences(User $user, array $input): void
    {
        $this->updatePreferencesAction->execute($user, $input);
    }
}
