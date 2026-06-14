<?php

use Illuminate\Support\Facades\Route;

// Support team console. Reachable by the 'support' role (super-admin too).
Route::middleware(['auth', 'role:support|super-admin'])->prefix('support')->name('support.')->group(function () {

    Route::get('/dashboard', [\App\Http\Controllers\Support\SupportDashboardController::class, 'index'])->name('dashboard');

    // Support tickets (escrow-backed disputes)
    Route::prefix('disputes')->name('disputes.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Support\SupportDisputeController::class, 'index'])->name('index');
        Route::get('/{dispute}', [\App\Http\Controllers\Support\SupportDisputeController::class, 'show'])->name('show');
        Route::post('/{dispute}/message', [\App\Http\Controllers\Support\SupportDisputeController::class, 'addMessage'])->name('message');
        Route::post('/{dispute}/resolve', [\App\Http\Controllers\Support\SupportDisputeController::class, 'resolve'])->name('resolve');
        Route::post('/{dispute}/escalate', [\App\Http\Controllers\Support\SupportDisputeController::class, 'escalate'])->name('escalate');
    });

    // Returns / RMA
    Route::prefix('returns')->name('returns.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Support\SupportReturnController::class, 'index'])->name('index');
        Route::post('/{return}/approve', [\App\Http\Controllers\Support\SupportReturnController::class, 'approve'])->name('approve');
        Route::post('/{return}/reject', [\App\Http\Controllers\Support\SupportReturnController::class, 'reject'])->name('reject');
        Route::post('/{return}/confirm', [\App\Http\Controllers\Support\SupportReturnController::class, 'confirm'])->name('confirm');
    });

    // KYC verification
    Route::prefix('kyc')->name('kyc.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Support\SupportKYCController::class, 'index'])->name('index');
        Route::get('/{kyc}', [\App\Http\Controllers\Support\SupportKYCController::class, 'show'])->name('show');
        Route::get('/{kyc}/document/{field}', [\App\Http\Controllers\Support\SupportKYCController::class, 'document'])->name('document');
        Route::post('/{kyc}/approve', [\App\Http\Controllers\Support\SupportKYCController::class, 'approve'])->name('approve');
        Route::post('/{kyc}/reject', [\App\Http\Controllers\Support\SupportKYCController::class, 'reject'])->name('reject');
    });
});
