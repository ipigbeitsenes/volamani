<?php

namespace App\Repositories\Notifications;

use App\Enums\NotificationCategory;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

class NotificationRepository
{
    /** Paginated notification feed for the full-page centre. */
    public function forUser(User $user, int $perPage = 20, ?string $filter = null): LengthAwarePaginator
    {
        $query = $user->notifications();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        }

        return $query->paginate($perPage);
    }

    /** Latest few notifications for the navbar bell dropdown. */
    public function recent(User $user, int $limit = 6): Collection
    {
        return $user->notifications()->limit($limit)->get();
    }

    public function unreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    public function find(User $user, string $id): ?DatabaseNotification
    {
        return $user->notifications()->whereKey($id)->first();
    }

    /**
     * The full category → channel matrix for the preferences page, merging the
     * user's saved rows over each category's defaults so every category renders.
     */
    public function preferenceMatrix(User $user): array
    {
        $saved = $user->notificationPreferences()->get()->keyBy(fn ($p) => $p->category->value);

        $matrix = [];

        foreach (NotificationCategory::cases() as $category) {
            $pref = $saved->get($category->value);

            $matrix[$category->value] = [
                'category' => $category,
                'email'    => $pref ? (bool) $pref->email : $category->defaultEmail(),
                'database' => $pref ? (bool) $pref->database : $category->defaultDatabase(),
            ];
        }

        return $matrix;
    }
}
