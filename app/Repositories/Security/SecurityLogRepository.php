<?php

namespace App\Repositories\Security;

use App\Enums\SecurityEvent;
use App\Models\SecurityLog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SecurityLogRepository
{
    public function create(array $data): SecurityLog
    {
        return SecurityLog::create($data);
    }

    public function recent(array $filters = [], int $perPage = 30): LengthAwarePaginator
    {
        $query = SecurityLog::with('user')->latest('created_at');

        if (! empty($filters['event'])) {
            $query->where('event', $filters['event']);
        }

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(fn ($q) => $q->where('ip_address', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$term}%")->orWhere('name', 'like', "%{$term}%")));
        }

        return $query->paginate($perPage);
    }

    public function forUser(User $user, int $limit = 20): Collection
    {
        return $user->securityLogs()->limit($limit)->get();
    }

    public function lockedAccounts(): Collection
    {
        return User::whereNotNull('locked_until')
            ->where('locked_until', '>', now())
            ->latest('locked_until')
            ->get();
    }

    public function stats(): array
    {
        return [
            'events_today'   => SecurityLog::whereDate('created_at', today())->count(),
            'failed_24h'     => SecurityLog::where('event', SecurityEvent::LoginFailed)
                                    ->where('created_at', '>=', now()->subDay())->count(),
            'locked'         => User::whereNotNull('locked_until')->where('locked_until', '>', now())->count(),
            'logins_24h'     => SecurityLog::where('event', SecurityEvent::Login)
                                    ->where('created_at', '>=', now()->subDay())->count(),
        ];
    }
}
