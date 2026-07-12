<?php

namespace App\Enums;

enum SecurityEvent: string
{
    case Login = 'login';
    case Logout = 'logout';
    case LoginFailed = 'login_failed';
    case AccountLocked = 'account_locked';
    case AccountUnlocked = 'account_unlocked';
    case PasswordChanged = 'password_changed';
    case PasswordResetRequested = 'password_reset_requested';
    case PasswordReset = 'password_reset';
    case EmailVerified = 'email_verified';
    case Registered = 'registered';
    case SuspiciousActivity = 'suspicious_activity';

    public function label(): string
    {
        return match ($this) {
            self::Login => 'Signed in',
            self::Logout => 'Signed out',
            self::LoginFailed => 'Failed sign-in',
            self::AccountLocked => 'Account locked',
            self::AccountUnlocked => 'Account unlocked',
            self::PasswordChanged => 'Password changed',
            self::PasswordResetRequested => 'Password reset requested',
            self::PasswordReset => 'Password reset',
            self::EmailVerified => 'Email verified',
            self::Registered => 'Account created',
            self::SuspiciousActivity => 'Suspicious activity',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Login => 'bi-box-arrow-in-right',
            self::Logout => 'bi-box-arrow-right',
            self::LoginFailed => 'bi-x-circle',
            self::AccountLocked => 'bi-lock',
            self::AccountUnlocked => 'bi-unlock',
            self::PasswordChanged => 'bi-key',
            self::PasswordResetRequested => 'bi-envelope-exclamation',
            self::PasswordReset => 'bi-key',
            self::EmailVerified => 'bi-patch-check',
            self::Registered => 'bi-person-plus',
            self::SuspiciousActivity => 'bi-exclamation-octagon',
        };
    }

    /** Severity drives colour + admin triage: info | warning | danger. */
    public function severity(): string
    {
        return match ($this) {
            self::LoginFailed, self::AccountLocked, self::SuspiciousActivity => 'danger',
            self::PasswordChanged, self::PasswordReset, self::PasswordResetRequested => 'warning',
            default => 'info',
        };
    }

    public function badge(): string
    {
        return match ($this->severity()) {
            'danger' => 'danger',
            'warning' => 'warning',
            default => 'secondary',
        };
    }
}
