<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDisputeController;
use App\Http\Controllers\Admin\AffiliateController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BuyerStrikeController;
use App\Http\Controllers\Admin\CategoryRequestController;
use App\Http\Controllers\Admin\ChargebackController;
use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\EscrowController;
use App\Http\Controllers\Admin\KYCManagementController;
use App\Http\Controllers\Admin\LiveChatController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PlatformDocumentController;
use App\Http\Controllers\Admin\ProductModerationController;
use App\Http\Controllers\Admin\ReturnController;
use App\Http\Controllers\Admin\ReviewModerationController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\VendorApprovalController;
use App\Http\Controllers\Admin\VendorStrikeController;
use App\Http\Controllers\Admin\WithdrawalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Users (super-admin only — managing users / other admins)
    Route::middleware('permission:users.manage')->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('/{user}', [UserManagementController::class, 'show'])->name('show');
        Route::put('/{user}/status', [UserManagementController::class, 'updateStatus'])->name('status');
        Route::put('/{user}/roles', [UserManagementController::class, 'updateRoles'])->name('roles');
        Route::put('/{user}/verify', [UserManagementController::class, 'verify'])->name('verify');
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
    });

    // Vendor approvals
    Route::prefix('vendors')->name('vendors.')->group(function () {
        Route::get('/', [VendorApprovalController::class, 'index'])->name('index');
        Route::get('/{vendor}', [VendorApprovalController::class, 'show'])->name('show');
        Route::post('/{vendor}/approve', [VendorApprovalController::class, 'approve'])->name('approve');
        Route::post('/{vendor}/reject', [VendorApprovalController::class, 'reject'])->name('reject');
        Route::post('/{vendor}/suspend', [VendorApprovalController::class, 'suspend'])->name('suspend');

        // Strikes (buyer-protection standing)
        Route::post('/{vendor}/strikes', [VendorStrikeController::class, 'store'])->name('strikes.store');
        Route::post('/strikes/{strike}/clear', [VendorStrikeController::class, 'clear'])->name('strikes.clear');
    });

    // Buyer abuse strikes (serial "fake buyer" protection)
    Route::prefix('buyers')->name('buyers.')->group(function () {
        Route::get('/', [BuyerStrikeController::class, 'index'])->name('index');
        Route::get('/{user}', [BuyerStrikeController::class, 'show'])->name('show');
        Route::post('/{user}/strikes', [BuyerStrikeController::class, 'store'])->name('strikes.store');
        Route::post('/{user}/reinstate', [BuyerStrikeController::class, 'reinstate'])->name('reinstate');
        Route::post('/strikes/{strike}/clear', [BuyerStrikeController::class, 'clear'])->name('strikes.clear');
    });

    // KYC management
    Route::prefix('kyc')->name('kyc.')->group(function () {
        Route::get('/', [KYCManagementController::class, 'index'])->name('index');
        Route::get('/{kyc}', [KYCManagementController::class, 'show'])->name('show');
        Route::get('/{kyc}/document/{field}', [KYCManagementController::class, 'document'])->name('document');
        Route::post('/{kyc}/approve', [KYCManagementController::class, 'approve'])->name('approve');
        Route::post('/{kyc}/reject', [KYCManagementController::class, 'reject'])->name('reject');
    });

    // Withdrawal approvals (super-admin only — payouts)
    Route::middleware('permission:withdrawals.approve')->prefix('withdrawals')->name('withdrawals.')->group(function () {
        Route::get('/', [WithdrawalController::class, 'index'])->name('index');
        Route::get('/{withdrawal}', [WithdrawalController::class, 'show'])->name('show');
        Route::post('/{withdrawal}/approve', [WithdrawalController::class, 'approve'])->name('approve');
        Route::post('/{withdrawal}/reject', [WithdrawalController::class, 'reject'])->name('reject');
    });

    // Escrow management
    Route::prefix('escrows')->name('escrows.')->group(function () {
        Route::get('/', [EscrowController::class, 'index'])->name('index');
        Route::get('/{escrow}', [EscrowController::class, 'show'])->name('show');
        Route::post('/{escrow}/release', [EscrowController::class, 'release'])->name('release');
        Route::post('/{escrow}/refund', [EscrowController::class, 'refund'])->name('refund');
    });

    // Disputes
    Route::prefix('disputes')->name('disputes.')->group(function () {
        Route::get('/', [AdminDisputeController::class, 'index'])->name('index');
        Route::get('/{dispute}', [AdminDisputeController::class, 'show'])->name('show');
        Route::post('/{dispute}/message', [AdminDisputeController::class, 'addMessage'])->name('message');
        Route::post('/{dispute}/resolve', [AdminDisputeController::class, 'resolve'])->name('resolve');
        Route::post('/{dispute}/escalate', [AdminDisputeController::class, 'escalate'])->name('escalate');
    });

    // Returns / RMA oversight
    Route::prefix('returns')->name('returns.')->group(function () {
        Route::get('/', [ReturnController::class, 'index'])->name('index');
        Route::post('/{return}/approve', [ReturnController::class, 'approve'])->name('approve');
        Route::post('/{return}/reject', [ReturnController::class, 'reject'])->name('reject');
        Route::post('/{return}/confirm', [ReturnController::class, 'confirm'])->name('confirm');
    });

    // Chargebacks (payment-gateway disputes)
    Route::prefix('chargebacks')->name('chargebacks.')->group(function () {
        Route::get('/', [ChargebackController::class, 'index'])->name('index');
        Route::get('/{chargeback}', [ChargebackController::class, 'show'])->name('show');
        Route::post('/{chargeback}/resolve', [ChargebackController::class, 'resolve'])->name('resolve');
    });

    // Review moderation
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [ReviewModerationController::class, 'index'])->name('index');
        Route::post('/{review}/approve', [ReviewModerationController::class, 'approve'])->name('approve');
        Route::post('/{review}/hide', [ReviewModerationController::class, 'hide'])->name('hide');
        Route::delete('/{review}', [ReviewModerationController::class, 'destroy'])->name('destroy');
    });

    // Custom category requests
    Route::prefix('category-requests')->name('category-requests.')->group(function () {
        Route::get('/', [CategoryRequestController::class, 'index'])->name('index');
        Route::post('/{categoryRequest}/approve', [CategoryRequestController::class, 'approve'])->name('approve');
        Route::post('/{categoryRequest}/reject', [CategoryRequestController::class, 'reject'])->name('reject');
    });

    // Products moderation
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductModerationController::class, 'index'])->name('index');
        Route::post('/{product}/approve', [ProductModerationController::class, 'approve'])->name('approve');
        Route::post('/{product}/reject', [ProductModerationController::class, 'reject'])->name('reject');
        Route::delete('/{product}', [ProductModerationController::class, 'destroy'])->name('destroy');
    });

    // Payments
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::get('/{payment}', [PaymentController::class, 'show'])->name('show');
        Route::post('/{payment}/approve', [PaymentController::class, 'approveOffline'])->name('approve-offline');
    });

    // Subscriptions & plans
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->name('index');
        Route::get('/plans', [SubscriptionController::class, 'plans'])->name('plans');
        Route::get('/plans/create', [SubscriptionController::class, 'createPlan'])->name('plans.create');
        Route::post('/plans', [SubscriptionController::class, 'storePlan'])->name('plans.store');
        Route::get('/plans/{plan}/edit', [SubscriptionController::class, 'editPlan'])->name('plans.edit');
        Route::put('/plans/{plan}', [SubscriptionController::class, 'updatePlan'])->name('plans.update');
        Route::post('/plans/{plan}/toggle', [SubscriptionController::class, 'togglePlan'])->name('plans.toggle');
    });

    // Platform documents — Volamani-issued invoices & contracts of sale
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [PlatformDocumentController::class, 'index'])->name('index');
        Route::get('/create', [PlatformDocumentController::class, 'create'])->name('create');
        Route::post('/', [PlatformDocumentController::class, 'store'])->name('store');
        Route::get('/{document}', [PlatformDocumentController::class, 'show'])->name('show');
        Route::get('/{document}/edit', [PlatformDocumentController::class, 'edit'])->name('edit');
        Route::put('/{document}', [PlatformDocumentController::class, 'update'])->name('update');
        Route::delete('/{document}', [PlatformDocumentController::class, 'destroy'])->name('destroy');
        Route::get('/{document}/print', [PlatformDocumentController::class, 'print'])->name('print');
        Route::post('/{document}/send', [PlatformDocumentController::class, 'send'])->name('send');
        Route::post('/{document}/payment', [PlatformDocumentController::class, 'recordPayment'])->name('payment');
        Route::post('/{document}/cancel', [PlatformDocumentController::class, 'cancel'])->name('cancel');
    });

    // Affiliate program
    Route::prefix('affiliates')->name('affiliates.')->group(function () {
        Route::get('/', [AffiliateController::class, 'index'])->name('index');
        Route::get('/commissions', [AffiliateController::class, 'commissions'])->name('commissions');
        Route::post('/commissions/{commission}/approve', [AffiliateController::class, 'approve'])->name('commissions.approve');
        Route::post('/commissions/{commission}/cancel', [AffiliateController::class, 'cancel'])->name('commissions.cancel');
        Route::get('/{account}', [AffiliateController::class, 'show'])->name('show');
        Route::post('/{account}/suspend', [AffiliateController::class, 'suspend'])->name('suspend');
        Route::post('/{account}/activate', [AffiliateController::class, 'activate'])->name('activate');
    });

    // Commission settings (super-admin only)
    Route::middleware('permission:commissions.manage')->prefix('commissions')->name('commissions.')->group(function () {
        Route::get('/', [CommissionController::class, 'index'])->name('index');
        Route::put('/', [CommissionController::class, 'update'])->name('update');
    });

    // Live chat console — read/answer visitor conversations + widget settings.
    // NB: static /settings routes are declared before the /{conversation} bind.
    Route::prefix('live-chat')->name('live-chat.')->group(function () {
        Route::get('/', [LiveChatController::class, 'index'])->name('index');
        Route::get('/settings', [LiveChatController::class, 'settings'])->name('settings');
        Route::put('/settings', [LiveChatController::class, 'updateSettings'])->name('settings.update');
        Route::get('/{conversation}', [LiveChatController::class, 'show'])->name('show');
        Route::get('/{conversation}/poll', [LiveChatController::class, 'poll'])->name('poll');
        Route::post('/{conversation}/reply', [LiveChatController::class, 'reply'])->name('reply');
        Route::post('/{conversation}/close', [LiveChatController::class, 'close'])->name('close');
        Route::post('/{conversation}/reopen', [LiveChatController::class, 'reopen'])->name('reopen');
    });

    // Audit logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs');

    // Security
    Route::prefix('security')->name('security.')->group(function () {
        Route::get('/', [SecurityController::class, 'index'])->name('index');
        Route::post('/{user}/unlock', [SecurityController::class, 'unlock'])->name('unlock');
    });

    // Settings (super-admin only)
    Route::middleware('permission:settings.manage')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::put('/', [SettingsController::class, 'update'])->name('update');
    });
});
