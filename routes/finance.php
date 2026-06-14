<?php

use Illuminate\Support\Facades\Route;

// Finance team console. Reachable by the 'finance' role (super-admin too).
Route::middleware(['auth', 'role:finance|super-admin'])->prefix('finance')->name('finance.')->group(function () {

    Route::get('/dashboard', [\App\Http\Controllers\Finance\FinanceDashboardController::class, 'index'])->name('dashboard');

    // Payments
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Finance\FinancePaymentController::class, 'index'])->name('index');
        Route::post('/{payment}/approve', [\App\Http\Controllers\Finance\FinancePaymentController::class, 'approveOffline'])->name('approve-offline');
    });

    // Withdrawals / payouts
    Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Finance\FinanceWithdrawalController::class, 'index'])->name('index');
        Route::post('/{withdrawal}/approve', [\App\Http\Controllers\Finance\FinanceWithdrawalController::class, 'approve'])->name('approve');
        Route::post('/{withdrawal}/reject', [\App\Http\Controllers\Finance\FinanceWithdrawalController::class, 'reject'])->name('reject');
    });

    // Escrow
    Route::prefix('escrows')->name('escrows.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Finance\FinanceEscrowController::class, 'index'])->name('index');
        Route::post('/{escrow}/release', [\App\Http\Controllers\Finance\FinanceEscrowController::class, 'release'])->name('release');
        Route::post('/{escrow}/refund', [\App\Http\Controllers\Finance\FinanceEscrowController::class, 'refund'])->name('refund');
    });

    // Commission & fee settings
    Route::prefix('commissions')->name('commissions.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Finance\FinanceCommissionController::class, 'index'])->name('index');
        Route::put('/', [\App\Http\Controllers\Finance\FinanceCommissionController::class, 'update'])->name('update');
    });
});
