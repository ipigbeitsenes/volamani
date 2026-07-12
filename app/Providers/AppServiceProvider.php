<?php

namespace App\Providers;

use App\Listeners\AuthEventSubscriber;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::subscribe(AuthEventSubscriber::class);

        // Super-admin bypasses every authorization check (@can / Gate / policies).
        // Returning null lets normal checks run for everyone else.
        Gate::before(fn ($user) => $user->hasRole('super-admin') ? true : null);

        // Render all paginators with Bootstrap 5 markup (the app is Bootstrap,
        // not Tailwind — the framework default).
        Paginator::useBootstrapFive();

        // @feature('wallet') … @endfeature — hide UI for admin-disabled features.
        Blade::if('feature', fn (string $key) => feature($key));

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        $this->configureStorage();
    }

    /**
     * Point the app's public/private disks at Amazon S3 when the admin has
     * selected the S3 driver in Settings and provided credentials. Falls back
     * silently to local storage otherwise (or if the settings table is absent,
     * e.g. during a fresh install / migration).
     */
    private function configureStorage(): void
    {
        try {
            if (! Schema::hasTable('settings')) {
                return;
            }

            if (settings('storage_driver') !== 's3') {
                return;
            }

            $key = settings('s3_key');
            $secret = settings('s3_secret');
            $region = settings('s3_region');
            $bucket = settings('s3_bucket');

            if (! $key || ! $secret || ! $region || ! $bucket) {
                return; // incomplete config — stay on local
            }

            $base = [
                'driver' => 's3',
                'key' => $key,
                'secret' => $secret,
                'region' => $region,
                'bucket' => $bucket,
                'url' => settings('s3_url') ?: null,
                'endpoint' => settings('s3_endpoint') ?: null,
                'use_path_style_endpoint' => (bool) settings('s3_path_style'),
                'throw' => false,
                'report' => false,
            ];

            config([
                'filesystems.disks.s3' => $base,
                'filesystems.disks.public' => array_merge($base, ['visibility' => 'public']),
                'filesystems.disks.private' => array_merge($base, ['visibility' => 'private']),
            ]);
        } catch (\Throwable $e) {
            // Never let storage config break the app boot — stay on local.
        }
    }
}
