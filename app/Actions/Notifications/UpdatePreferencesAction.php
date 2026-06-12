<?php

namespace App\Actions\Notifications;

use App\Enums\NotificationCategory;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdatePreferencesAction
{
    /**
     * Persist the user's full preference matrix. $input is keyed by category
     * value, each holding ['email' => bool, 'database' => bool]; unchecked
     * boxes simply arrive absent. Essential categories are always forced on.
     */
    public function execute(User $user, array $input): void
    {
        DB::transaction(function () use ($user, $input) {
            foreach (NotificationCategory::cases() as $category) {
                $row = $input[$category->value] ?? [];

                NotificationPreference::updateOrCreate(
                    ['user_id' => $user->id, 'category' => $category->value],
                    [
                        'email'    => $category->isEssential() ? true : (bool) ($row['email'] ?? false),
                        'database' => $category->isEssential() ? true : (bool) ($row['database'] ?? false),
                    ],
                );
            }
        });

        $user->unsetRelation('notificationPreferences');
    }
}
