<?php

use App\Http\Controllers\Support\SupportDashboardController;
use App\Http\Controllers\Support\SupportDisputeController;
use App\Http\Controllers\Support\SupportKYCController;
use App\Http\Controllers\Support\SupportReturnController;
use Illuminate\Support\Facades\Route;

// Support team console. Reachable by the 'support' role (super-admin too).
Route::middleware(['auth', 'role:support|super-admin'])->prefix('support')->name('support.')->group(function () {

    Route::get('/dashboard', [SupportDashboardController::class, 'index'])->name('dashboard');

    // Support tickets (escrow-backed disputes)
    Route::prefix('disputes')->name('disputes.')->group(function () {
        Route::get('/', [SupportDisputeController::class, 'index'])->name('index');
        Route::get('/{dispute}', [SupportDisputeController::class, 'show'])->name('show');
        Route::post('/{dispute}/message', [SupportDisputeController::class, 'addMessage'])->name('message');
        Route::post('/{dispute}/resolve', [SupportDisputeController::class, 'resolve'])->name('resolve');
        Route::post('/{dispute}/escalate', [SupportDisputeController::class, 'escalate'])->name('escalate');
    });

    // Returns / RMA
    Route::prefix('returns')->name('returns.')->group(function () {
        Route::get('/', [SupportReturnController::class, 'index'])->name('index');
        Route::post('/{return}/approve', [SupportReturnController::class, 'approve'])->name('approve');
        Route::post('/{return}/reject', [SupportReturnController::class, 'reject'])->name('reject');
        Route::post('/{return}/confirm', [SupportReturnController::class, 'confirm'])->name('confirm');
    });

    // KYC verification
    Route::prefix('kyc')->name('kyc.')->group(function () {
        Route::get('/', [SupportKYCController::class, 'index'])->name('index');
        Route::get('/{kyc}', [SupportKYCController::class, 'show'])->name('show');
        Route::get('/{kyc}/document/{field}', [SupportKYCController::class, 'document'])->name('document');
        Route::post('/{kyc}/approve', [SupportKYCController::class, 'approve'])->name('approve');
        Route::post('/{kyc}/reject', [SupportKYCController::class, 'reject'])->name('reject');
    });
});
