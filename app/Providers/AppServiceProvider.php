<?php

namespace App\Providers;

use App\Listeners\AuthEventSubscriber;
use Illuminate\Support\Facades\Event;
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

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
