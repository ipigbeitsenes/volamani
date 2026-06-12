<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Affiliate share link (public) — tracks the click then forwards to signup
Route::get('/r/{code}', [\App\Http\Controllers\Affiliate\ReferralLinkController::class, 'track'])->name('referral.track');

require __DIR__ . '/auth.php';
require __DIR__ . '/marketplace.php';
require __DIR__ . '/vendor.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/webhooks.php';
