<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        // Machine-to-machine gateway webhooks authenticate via a signed payload
        // (verifyWebhookSignature), not a session CSRF token. Without this they
        // 419 before reaching the signature check and fulfilment never runs.
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);

        $middleware->alias([
            // Spatie laravel-permission (role:, permission:, role_or_permission:)
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,

            // Application middleware
            'vendor.approved'    => \App\Http\Middleware\EnsureVendorApproved::class,
            'kyc.verified'       => \App\Http\Middleware\EnsureKYCVerified::class,
            'buyer.active'       => \App\Http\Middleware\EnsureBuyerNotSuspended::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
