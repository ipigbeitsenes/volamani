<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');

    // Users (super-admin only — managing users / other admins)
    Route::middleware('permission:users.manage')->prefix('users')->name('users.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\UserManagementController::class, 'index'])->name('index');
        Route::get('/{user}', [\App\Http\Controllers\Admin\UserManagementController::class, 'show'])->name('show');
        Route::put('/{user}/status', [\App\Http\Controllers\Admin\UserManagementController::class, 'updateStatus'])->name('status');
        Route::delete('/{user}', [\App\Http\Controllers\Admin\UserManagementController::class, 'destroy'])->name('destroy');
    });

    // Vendor approvals
    Route::prefix('vendors')->name('vendors.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\VendorApprovalController::class, 'index'])->name('index');
        Route::get('/{vendor}', [\App\Http\Controllers\Admin\VendorApprovalController::class, 'show'])->name('show');
        Route::post('/{vendor}/approve', [\App\Http\Controllers\Admin\VendorApprovalController::class, 'approve'])->name('approve');
        Route::post('/{vendor}/reject', [\App\Http\Controllers\Admin\VendorApprovalController::class, 'reject'])->name('reject');
        Route::post('/{vendor}/suspend', [\App\Http\Controllers\Admin\VendorApprovalController::class, 'suspend'])->name('suspend');

        // Strikes (buyer-protection standing)
        Route::post('/{vendor}/strikes', [\App\Http\Controllers\Admin\VendorStrikeController::class, 'store'])->name('strikes.store');
        Route::post('/strikes/{strike}/clear', [\App\Http\Controllers\Admin\VendorStrikeController::class, 'clear'])->name('strikes.clear');
    });

    // KYC management
    Route::prefix('kyc')->name('kyc.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\KYCManagementController::class, 'index'])->name('index');
        Route::get('/{kyc}', [\App\Http\Controllers\Admin\KYCManagementController::class, 'show'])->name('show');
        Route::get('/{kyc}/document/{field}', [\App\Http\Controllers\Admin\KYCManagementController::class, 'document'])->name('document');
        Route::post('/{kyc}/approve', [\App\Http\Controllers\Admin\KYCManagementController::class, 'approve'])->name('approve');
        Route::post('/{kyc}/reject', [\App\Http\Controllers\Admin\KYCManagementController::class, 'reject'])->name('reject');
    });

    // Withdrawal approvals (super-admin only — payouts)
    Route::middleware('permission:withdrawals.approve')->prefix('withdrawals')->name('withdrawals.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\WithdrawalController::class, 'index'])->name('index');
        Route::get('/{withdrawal}', [\App\Http\Controllers\Admin\WithdrawalController::class, 'show'])->name('show');
        Route::post('/{withdrawal}/approve', [\App\Http\Controllers\Admin\WithdrawalController::class, 'approve'])->name('approve');
        Route::post('/{withdrawal}/reject', [\App\Http\Controllers\Admin\WithdrawalController::class, 'reject'])->name('reject');
    });

    // Escrow management
    Route::prefix('escrows')->name('escrows.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\EscrowController::class, 'index'])->name('index');
        Route::get('/{escrow}', [\App\Http\Controllers\Admin\EscrowController::class, 'show'])->name('show');
        Route::post('/{escrow}/release', [\App\Http\Controllers\Admin\EscrowController::class, 'release'])->name('release');
        Route::post('/{escrow}/refund', [\App\Http\Controllers\Admin\EscrowController::class, 'refund'])->name('refund');
    });

    // Disputes
    Route::prefix('disputes')->name('disputes.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminDisputeController::class, 'index'])->name('index');
        Route::get('/{dispute}', [\App\Http\Controllers\Admin\AdminDisputeController::class, 'show'])->name('show');
        Route::post('/{dispute}/message', [\App\Http\Controllers\Admin\AdminDisputeController::class, 'addMessage'])->name('message');
        Route::post('/{dispute}/resolve', [\App\Http\Controllers\Admin\AdminDisputeController::class, 'resolve'])->name('resolve');
        Route::post('/{dispute}/escalate', [\App\Http\Controllers\Admin\AdminDisputeController::class, 'escalate'])->name('escalate');
    });

    // Returns / RMA oversight
    Route::prefix('returns')->name('returns.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ReturnController::class, 'index'])->name('index');
        Route::post('/{return}/approve', [\App\Http\Controllers\Admin\ReturnController::class, 'approve'])->name('approve');
        Route::post('/{return}/reject', [\App\Http\Controllers\Admin\ReturnController::class, 'reject'])->name('reject');
        Route::post('/{return}/confirm', [\App\Http\Controllers\Admin\ReturnController::class, 'confirm'])->name('confirm');
    });

    // Chargebacks (payment-gateway disputes)
    Route::prefix('chargebacks')->name('chargebacks.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ChargebackController::class, 'index'])->name('index');
        Route::get('/{chargeback}', [\App\Http\Controllers\Admin\ChargebackController::class, 'show'])->name('show');
        Route::post('/{chargeback}/resolve', [\App\Http\Controllers\Admin\ChargebackController::class, 'resolve'])->name('resolve');
    });

    // Review moderation
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ReviewModerationController::class, 'index'])->name('index');
        Route::post('/{review}/approve', [\App\Http\Controllers\Admin\ReviewModerationController::class, 'approve'])->name('approve');
        Route::post('/{review}/hide', [\App\Http\Controllers\Admin\ReviewModerationController::class, 'hide'])->name('hide');
        Route::delete('/{review}', [\App\Http\Controllers\Admin\ReviewModerationController::class, 'destroy'])->name('destroy');
    });

    // Custom category requests
    Route::prefix('category-requests')->name('category-requests.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CategoryRequestController::class, 'index'])->name('index');
        Route::post('/{categoryRequest}/approve', [\App\Http\Controllers\Admin\CategoryRequestController::class, 'approve'])->name('approve');
        Route::post('/{categoryRequest}/reject', [\App\Http\Controllers\Admin\CategoryRequestController::class, 'reject'])->name('reject');
    });

    // Products moderation
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ProductModerationController::class, 'index'])->name('index');
        Route::post('/{product}/approve', [\App\Http\Controllers\Admin\ProductModerationController::class, 'approve'])->name('approve');
        Route::post('/{product}/reject', [\App\Http\Controllers\Admin\ProductModerationController::class, 'reject'])->name('reject');
        Route::delete('/{product}', [\App\Http\Controllers\Admin\ProductModerationController::class, 'destroy'])->name('destroy');
    });

    // Payments
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('index');
        Route::get('/{payment}', [\App\Http\Controllers\Admin\PaymentController::class, 'show'])->name('show');
        Route::post('/{payment}/approve', [\App\Http\Controllers\Admin\PaymentController::class, 'approveOffline'])->name('approve-offline');
    });

    // Subscriptions & plans
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SubscriptionController::class, 'index'])->name('index');
        Route::get('/plans', [\App\Http\Controllers\Admin\SubscriptionController::class, 'plans'])->name('plans');
        Route::get('/plans/create', [\App\Http\Controllers\Admin\SubscriptionController::class, 'createPlan'])->name('plans.create');
        Route::post('/plans', [\App\Http\Controllers\Admin\SubscriptionController::class, 'storePlan'])->name('plans.store');
        Route::get('/plans/{plan}/edit', [\App\Http\Controllers\Admin\SubscriptionController::class, 'editPlan'])->name('plans.edit');
        Route::put('/plans/{plan}', [\App\Http\Controllers\Admin\SubscriptionController::class, 'updatePlan'])->name('plans.update');
        Route::post('/plans/{plan}/toggle', [\App\Http\Controllers\Admin\SubscriptionController::class, 'togglePlan'])->name('plans.toggle');
    });

    // Platform documents — Volamani-issued invoices & contracts of sale
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PlatformDocumentController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\PlatformDocumentController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\PlatformDocumentController::class, 'store'])->name('store');
        Route::get('/{document}', [\App\Http\Controllers\Admin\PlatformDocumentController::class, 'show'])->name('show');
        Route::get('/{document}/edit', [\App\Http\Controllers\Admin\PlatformDocumentController::class, 'edit'])->name('edit');
        Route::put('/{document}', [\App\Http\Controllers\Admin\PlatformDocumentController::class, 'update'])->name('update');
        Route::delete('/{document}', [\App\Http\Controllers\Admin\PlatformDocumentController::class, 'destroy'])->name('destroy');
        Route::get('/{document}/print', [\App\Http\Controllers\Admin\PlatformDocumentController::class, 'print'])->name('print');
        Route::post('/{document}/send', [\App\Http\Controllers\Admin\PlatformDocumentController::class, 'send'])->name('send');
        Route::post('/{document}/payment', [\App\Http\Controllers\Admin\PlatformDocumentController::class, 'recordPayment'])->name('payment');
        Route::post('/{document}/cancel', [\App\Http\Controllers\Admin\PlatformDocumentController::class, 'cancel'])->name('cancel');
    });

    // Affiliate program
    Route::prefix('affiliates')->name('affiliates.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AffiliateController::class, 'index'])->name('index');
        Route::get('/commissions', [\App\Http\Controllers\Admin\AffiliateController::class, 'commissions'])->name('commissions');
        Route::post('/commissions/{commission}/approve', [\App\Http\Controllers\Admin\AffiliateController::class, 'approve'])->name('commissions.approve');
        Route::post('/commissions/{commission}/cancel', [\App\Http\Controllers\Admin\AffiliateController::class, 'cancel'])->name('commissions.cancel');
        Route::get('/{account}', [\App\Http\Controllers\Admin\AffiliateController::class, 'show'])->name('show');
        Route::post('/{account}/suspend', [\App\Http\Controllers\Admin\AffiliateController::class, 'suspend'])->name('suspend');
        Route::post('/{account}/activate', [\App\Http\Controllers\Admin\AffiliateController::class, 'activate'])->name('activate');
    });

    // Commission settings (super-admin only)
    Route::middleware('permission:commissions.manage')->prefix('commissions')->name('commissions.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CommissionController::class, 'index'])->name('index');
        Route::put('/', [\App\Http\Controllers\Admin\CommissionController::class, 'update'])->name('update');
    });

    // Live chat console — read/answer visitor conversations + widget settings.
    // NB: static /settings routes are declared before the /{conversation} bind.
    Route::prefix('live-chat')->name('live-chat.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\LiveChatController::class, 'index'])->name('index');
        Route::get('/settings', [\App\Http\Controllers\Admin\LiveChatController::class, 'settings'])->name('settings');
        Route::put('/settings', [\App\Http\Controllers\Admin\LiveChatController::class, 'updateSettings'])->name('settings.update');
        Route::get('/{conversation}', [\App\Http\Controllers\Admin\LiveChatController::class, 'show'])->name('show');
        Route::get('/{conversation}/poll', [\App\Http\Controllers\Admin\LiveChatController::class, 'poll'])->name('poll');
        Route::post('/{conversation}/reply', [\App\Http\Controllers\Admin\LiveChatController::class, 'reply'])->name('reply');
        Route::post('/{conversation}/close', [\App\Http\Controllers\Admin\LiveChatController::class, 'close'])->name('close');
        Route::post('/{conversation}/reopen', [\App\Http\Controllers\Admin\LiveChatController::class, 'reopen'])->name('reopen');
    });

    // Audit logs
    Route::get('/audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-logs');

    // Security
    Route::prefix('security')->name('security.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SecurityController::class, 'index'])->name('index');
        Route::post('/{user}/unlock', [\App\Http\Controllers\Admin\SecurityController::class, 'unlock'])->name('unlock');
    });

    // Settings (super-admin only)
    Route::middleware('permission:settings.manage')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('index');
        Route::put('/', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('update');
    });
});
