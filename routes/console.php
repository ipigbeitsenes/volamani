<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Release escrows past their buyer-protection window (digital products).
Schedule::command('escrow:auto-release')->hourly();

// Renew due subscriptions from the vendor wallet and expire lapsed ones.
Schedule::command('subscriptions:process')->dailyAt('02:00');

// Un-feature products whose paid promotion has elapsed.
Schedule::command('products:expire-promotions')->hourly();
