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

// Offline live-chat fallback: reply to conversations no agent has answered in time.
Schedule::command('chat:auto-respond')->everyMinute();

// Pay out rolling chargeback reserves whose holding window has elapsed.
Schedule::command('reserve:release')->dailyAt('03:00');

// Enforce dispute SLAs: auto-escalate disputes that have gone unanswered.
Schedule::command('disputes:enforce-sla')->hourly();

// Nightly off-box database backup (point BACKUP_DISK at S3 for real DR).
Schedule::command('db:backup')->dailyAt('01:30')->withoutOverlapping();
