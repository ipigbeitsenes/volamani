<?php

use Illuminate\Support\Facades\Route;

Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::post('/paystack', [\App\Http\Controllers\Webhook\PaystackWebhookController::class, 'handle'])
        ->middleware('throttle:120,1')
        ->name('paystack');
});
