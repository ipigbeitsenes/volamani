<?php

use App\Http\Middleware\EnsureBuyerNotSuspended;
use App\Http\Middleware\EnsureFeatureEnabled;
use App\Http\Middleware\EnsureKYCVerified;
use App\Http\Middleware\EnsureTermsAccepted;
use App\Http\Middleware\EnsureVendorApproved;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SecurityHeaders::class,
            EnsureTermsAccepted::class,
        ]);

        // Machine-to-machine gateway webhooks authenticate via a signed payload
        // (verifyWebhookSignature), not a session CSRF token. Without this they
        // 419 before reaching the signature check and fulfilment never runs.
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);

        $middleware->alias([
            // Spatie laravel-permission (role:, permission:, role_or_permission:)
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,

            // Application middleware
            'vendor.approved' => EnsureVendorApproved::class,
            'kyc.verified' => EnsureKYCVerified::class,
            'buyer.active' => EnsureBuyerNotSuspended::class,
            'feature' => EnsureFeatureEnabled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Attach request context to every logged exception so production logs are
        // actually actionable (who, where) without leaking bodies. This is the
        // dependency-free baseline; set SENTRY_LARAVEL_DSN + install
        // sentry/sentry-laravel to ship these to a real error tracker.
        $exceptions->context(fn (): array => array_filter([
            'url' => request()?->fullUrl(),
            'method' => request()?->method(),
            'user_id' => auth()->id(),
            'ip' => request()?->ip(),
        ]));
    })->create();
