<?php

use App\Http\Controllers\Webhook\PaystackWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::post('/paystack', [PaystackWebhookController::class, 'handle'])
        ->middleware('throttle:120,1')
        ->name('paystack');
});
