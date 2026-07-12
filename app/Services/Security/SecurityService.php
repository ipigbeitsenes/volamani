<?php

namespace App\Services\Security;

use App\Enums\SecurityEvent;
use App\Models\SecurityLog;
use App\Models\User;
use App\Repositories\Security\SecurityLogRepository;

class SecurityService
{
    public function __construct(private SecurityLogRepository $repo) {}

    /** Write a security event. IP / user-agent are pulled from the live request. */
    public function log(?User $user, SecurityEvent $event, ?string $description = null, array $metadata = []): SecurityLog
    {
        $request = request();

        return $this->repo->create([
            'user_id' => $user?->id,
            'event' => $event,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'description' => $description,
            'metadata' => $metadata ?: null,
        ]);
    }

    // ─── Auth lifecycle ─────────────────────────────────────────────────────────

    /** Successful sign-in: clear the failure counter / lock, then log. */
    public function recordLogin(User $user): void
    {
        if ($user->failed_login_attempts > 0 || $user->locked_until !== null) {
            $user->forceFill(['failed_login_attempts' => 0, 'locked_until' => null])->save();
        }

        $this->log($user, SecurityEvent::Login);
    }

    /**
     * Failed sign-in: count the attempt against the account and lock it once the
     * threshold is crossed. The email may not match any account (still logged).
     */
    public function recordFailedLogin(?string $email): void
    {
        $user = $email ? User::where('email', $email)->first() : null;

        if ($user) {
            $attempts = $user->failed_login_attempts + 1;
            $max = (int) settings('max_login_attempts', 5);
            $attrs = ['failed_login_attempts' => $attempts];

            if ($attempts >= $max) {
                $attrs['locked_until'] = now()->addMinutes((int) settings('lockout_minutes', 15));
            }

            $user->forceFill($attrs)->save();

            if (isset($attrs['locked_until'])) {
                $this->log($user, SecurityEvent::AccountLocked, "Locked after {$attempts} failed attempts.");
            }
        }

        $this->log($user, SecurityEvent::LoginFailed, null, ['email' => $email]);
    }

    public function isEmailLocked(?string $email): bool
    {
        if (! $email) {
            return false;
        }

        return (bool) User::where('email', $email)->first()?->isLocked();
    }

    public function unlock(User $user): void
    {
        $user->forceFill(['failed_login_attempts' => 0, 'locked_until' => null])->save();
        $this->log($user, SecurityEvent::AccountUnlocked);
    }

    // ─── Other events ─────────────────────────────────────────────────────────────

    public function recordLogout(User $user): void
    {
        $this->log($user, SecurityEvent::Logout);
    }

    public function recordRegistered(User $user): void
    {
        $this->log($user, SecurityEvent::Registered);
    }

    public function recordEmailVerified(User $user): void
    {
        $this->log($user, SecurityEvent::EmailVerified);
    }

    public function recordPasswordReset(User $user): void
    {
        $this->log($user, SecurityEvent::PasswordReset);
    }

    public function recordPasswordChanged(User $user): void
    {
        $this->log($user, SecurityEvent::PasswordChanged);
    }

    public function recordPasswordResetRequested(?string $email): void
    {
        $user = $email ? User::where('email', $email)->first() : null;
        $this->log($user, SecurityEvent::PasswordResetRequested, null, ['email' => $email]);
    }

    // ─── Query passthroughs ─────────────────────────────────────────────────────

    public function recent(array $filters = [], int $perPage = 30)
    {
        return $this->repo->recent($filters, $perPage);
    }

    public function forUser(User $user, int $limit = 20)
    {
        return $this->repo->forUser($user, $limit);
    }

    public function lockedAccounts()
    {
        return $this->repo->lockedAccounts();
    }

    public function stats(): array
    {
        return $this->repo->stats();
    }
}
