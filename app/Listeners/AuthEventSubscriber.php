<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\Security\SecurityService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Events\Dispatcher;

/**
 * Bridges framework auth events into the security log. Centralising it here
 * keeps the auth controllers/actions untouched while guaranteeing every
 * sign-in, failure and lockout is recorded.
 *
 * Methods are deliberately named on* (not handle*): Laravel's event
 * auto-discovery would register handle*-named methods a second time on top of
 * the explicit Event::subscribe() in AppServiceProvider, double-counting
 * every auth event (and halving the lockout threshold).
 */
class AuthEventSubscriber
{
    public function __construct(private SecurityService $security) {}

    public function onLogin(Login $event): void
    {
        if ($event->user instanceof User) {
            $this->security->recordLogin($event->user);
        }
    }

    public function onLogout(Logout $event): void
    {
        if ($event->user instanceof User) {
            $this->security->recordLogout($event->user);
        }
    }

    public function onFailed(Failed $event): void
    {
        $this->security->recordFailedLogin($event->credentials['email'] ?? null);
    }

    public function onRegistered(Registered $event): void
    {
        if ($event->user instanceof User) {
            $this->security->recordRegistered($event->user);
        }
    }

    public function onVerified(Verified $event): void
    {
        if ($event->user instanceof User) {
            $this->security->recordEmailVerified($event->user);
        }
    }

    public function onPasswordReset(PasswordReset $event): void
    {
        if ($event->user instanceof User) {
            $this->security->recordPasswordReset($event->user);
        }
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'onLogin',
            Logout::class => 'onLogout',
            Failed::class => 'onFailed',
            Registered::class => 'onRegistered',
            Verified::class => 'onVerified',
            PasswordReset::class => 'onPasswordReset',
        ];
    }
}
