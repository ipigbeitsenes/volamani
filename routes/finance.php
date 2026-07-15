<?php

use App\Http\Controllers\Finance\FinanceCommissionController;
use App\Http\Controllers\Finance\FinanceDashboardController;
use App\Http\Controllers\Finance\FinanceEscrowController;
use App\Http\Controllers\Finance\FinancePaymentController;
use App\Http\Controllers\Finance\FinanceWithdrawalController;
use Illuminate\Support\Facades\Route;

// Finance team console. Reachable by the 'finance' role (super-admin too).
Route::middleware(['auth', 'role:finance|super-admin'])->prefix('finance')->name('finance.')->group(function () {

    Route::get('/dashboard', [FinanceDashboardController::class, 'index'])->name('dashboard');

    // Payments
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [FinancePaymentController::class, 'index'])->name('index');
        Route::post('/{payment}/approve', [FinancePaymentController::class, 'approveOffline'])->name('approve-offline');
    });

    // Withdrawals / payouts (hidden with the wallet feature)
    Route::middleware('feature:wallet')->prefix('withdrawals')->name('withdrawals.')->group(function () {
        Route::get('/', [FinanceWithdrawalController::class, 'index'])->name('index');
        Route::post('/{withdrawal}/approve', [FinanceWithdrawalController::class, 'approve'])->name('approve');
        Route::post('/{withdrawal}/reject', [FinanceWithdrawalController::class, 'reject'])->name('reject');
    });

    // Escrow (hidden with the escrow feature)
    Route::middleware('feature:escrow')->prefix('escrows')->name('escrows.')->group(function () {
        Route::get('/', [FinanceEscrowController::class, 'index'])->name('index');
        Route::post('/{escrow}/release', [FinanceEscrowController::class, 'release'])->name('release');
        Route::post('/{escrow}/refund', [FinanceEscrowController::class, 'refund'])->name('refund');
    });

    // Commission & fee settings
    Route::prefix('commissions')->name('commissions.')->group(function () {
        Route::get('/', [FinanceCommissionController::class, 'index'])->name('index');
        Route::put('/', [FinanceCommissionController::class, 'update'])->name('update');
    });
});
