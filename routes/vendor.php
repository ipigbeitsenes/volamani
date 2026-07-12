<?php

use App\Http\Controllers\ServiceOrderController;
use App\Http\Controllers\Vendor\AffiliateController;
use App\Http\Controllers\Vendor\AnalyticsController;
use App\Http\Controllers\Vendor\CategoryRequestController;
use App\Http\Controllers\Vendor\ClientController;
use App\Http\Controllers\Vendor\ConsultationManagementController;
use App\Http\Controllers\Vendor\DocumentController;
use App\Http\Controllers\Vendor\OnboardingController;
use App\Http\Controllers\Vendor\ProductManagementController;
use App\Http\Controllers\Vendor\QuotationController;
use App\Http\Controllers\Vendor\ServiceManagementController;
use App\Http\Controllers\Vendor\ServiceOrderManagementController;
use App\Http\Controllers\Vendor\StorefrontSettingsController;
use App\Http\Controllers\Vendor\SubscriptionController;
use App\Http\Controllers\Vendor\VendorChargebackController;
use App\Http\Controllers\Vendor\VendorDashboardController;
use App\Http\Controllers\Vendor\VendorEscrowController;
use App\Http\Controllers\Vendor\VendorKYCController;
use App\Http\Controllers\Vendor\VendorMatchController;
use App\Http\Controllers\Vendor\VendorOrderController;
use App\Http\Controllers\Vendor\VendorReturnController;
use App\Http\Controllers\Vendor\VendorReviewController;
use App\Http\Controllers\Vendor\VendorWalletController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('vendor')->name('vendor.')->group(function () {

    // Onboarding (before approval)
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

    // All routes below require an approved vendor account
    Route::middleware('vendor.approved')->group(function () {

        Route::get('/dashboard', [VendorDashboardController::class, 'index'])->name('dashboard');

        // Products
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [ProductManagementController::class, 'index'])->name('index');
            Route::get('/create', [ProductManagementController::class, 'create'])->name('create');
            Route::post('/', [ProductManagementController::class, 'store'])->name('store');
            Route::get('/{product}/edit', [ProductManagementController::class, 'edit'])->name('edit');
            Route::put('/{product}', [ProductManagementController::class, 'update'])->name('update');
            Route::delete('/{product}', [ProductManagementController::class, 'destroy'])->name('destroy');
            Route::post('/{product}/promote', [ProductManagementController::class, 'promote'])->middleware('feature:promoted_listings')->name('promote');
            Route::delete('/gallery/{image}', [ProductManagementController::class, 'deleteGalleryImage'])->name('gallery.delete');
            Route::delete('/files/{file}', [ProductManagementController::class, 'deleteFile'])->name('files.delete');
        });

        // Service orders received
        Route::middleware('feature:services')->prefix('service-orders')->name('service-orders.')->group(function () {
            Route::get('/', [ServiceOrderManagementController::class, 'index'])->name('index');
            Route::get('/{serviceOrder}', [ServiceOrderController::class, 'show'])->name('show');
            Route::post('/{serviceOrder}/deliver', [ServiceOrderManagementController::class, 'deliver'])->name('deliver');
            Route::post('/{serviceOrder}/message', [ServiceOrderManagementController::class, 'sendMessage'])->name('message');
        });

        // Services
        Route::middleware('feature:services')->prefix('services')->name('services.')->group(function () {
            Route::get('/', [ServiceManagementController::class, 'index'])->name('index');
            Route::get('/create', [ServiceManagementController::class, 'create'])->name('create');
            Route::post('/', [ServiceManagementController::class, 'store'])->name('store');
            Route::get('/{service}/edit', [ServiceManagementController::class, 'edit'])->name('edit');
            Route::put('/{service}', [ServiceManagementController::class, 'update'])->name('update');
            Route::delete('/{service}', [ServiceManagementController::class, 'destroy'])->name('destroy');
        });

        // Orders received
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [VendorOrderController::class, 'index'])->name('index');
            Route::get('/{order}', [VendorOrderController::class, 'show'])->name('show');
            Route::post('/{order}/ship', [VendorOrderController::class, 'markShipped'])->name('ship');
            Route::post('/{order}/deliver', [VendorOrderController::class, 'markDelivered'])->name('deliver');
            Route::post('/{order}/upload', [VendorOrderController::class, 'uploadDeliverable'])->name('upload');
            Route::post('/{order}/cancel', [VendorOrderController::class, 'cancel'])->name('cancel');
        });

        // Returns / RMA received
        Route::middleware('feature:returns')->prefix('returns')->name('returns.')->group(function () {
            Route::get('/', [VendorReturnController::class, 'index'])->name('index');
            Route::post('/{return}/approve', [VendorReturnController::class, 'approve'])->name('approve');
            Route::post('/{return}/reject', [VendorReturnController::class, 'reject'])->name('reject');
            Route::post('/{return}/confirm', [VendorReturnController::class, 'confirm'])->name('confirm');
        });

        // Chargebacks — view + contest payment-gateway disputes
        Route::prefix('chargebacks')->name('chargebacks.')->group(function () {
            Route::get('/', [VendorChargebackController::class, 'index'])->name('index');
            Route::get('/{chargeback}', [VendorChargebackController::class, 'show'])->name('show');
            Route::post('/{chargeback}/contest', [VendorChargebackController::class, 'contest'])->name('contest');
        });

        // Quotations for product requests
        Route::middleware('feature:requests')->prefix('quotations')->name('quotations.')->group(function () {
            Route::get('/', [QuotationController::class, 'index'])->name('index');
            Route::post('/requests/{request}', [QuotationController::class, 'store'])->name('store');
            Route::get('/{quotation}', [QuotationController::class, 'show'])->name('show');
            Route::delete('/{quotation}', [QuotationController::class, 'destroy'])->name('withdraw');
        });

        // Consultations (if vendor is also a consultant)
        Route::middleware('feature:consultations')->prefix('consultations')->name('consultations.')->group(function () {
            Route::get('/', [ConsultationManagementController::class, 'index'])->name('index');
            Route::get('/setup', [ConsultationManagementController::class, 'setup'])->name('setup');
            Route::post('/setup', [ConsultationManagementController::class, 'storeProfile'])->name('setup.store');
            Route::get('/profile', [ConsultationManagementController::class, 'editProfile'])->name('profile');
            Route::put('/profile/{profile}', [ConsultationManagementController::class, 'updateProfile'])->name('profile.update');
            Route::get('/packages', [ConsultationManagementController::class, 'packages'])->name('packages');
            Route::post('/packages', [ConsultationManagementController::class, 'storePackage'])->name('packages.store');
            Route::post('/packages/{package}/toggle', [ConsultationManagementController::class, 'togglePackage'])->name('packages.toggle');
            Route::delete('/packages/{package}', [ConsultationManagementController::class, 'deletePackage'])->name('packages.delete');
            Route::get('/schedule', [ConsultationManagementController::class, 'schedule'])->name('schedule');
            Route::put('/schedule/{profile}', [ConsultationManagementController::class, 'updateSchedule'])->name('schedule.update');
            Route::get('/sessions', [ConsultationManagementController::class, 'sessions'])->name('sessions');
            Route::get('/sessions/{session}', [ConsultationManagementController::class, 'showSession'])->name('sessions.show');
            Route::post('/sessions/{session}/confirm', [ConsultationManagementController::class, 'confirmSession'])->name('sessions.confirm');
            Route::post('/sessions/{session}/complete', [ConsultationManagementController::class, 'completeSession'])->name('sessions.complete');
            Route::post('/sessions/{session}/cancel', [ConsultationManagementController::class, 'cancelSession'])->name('sessions.cancel');
        });

        // Wallet & withdrawals
        Route::middleware('feature:wallet')->prefix('wallet')->name('wallet.')->group(function () {
            Route::get('/', [VendorWalletController::class, 'index'])->name('index');
            Route::post('/withdraw', [VendorWalletController::class, 'requestWithdrawal'])->name('withdraw');
            Route::get('/transactions', [VendorWalletController::class, 'transactions'])->name('transactions');
        });

        // Escrow (vendor view — pending earnings)
        Route::middleware('feature:escrow')->prefix('escrows')->name('escrows.')->group(function () {
            Route::get('/', [VendorEscrowController::class, 'index'])->name('index');
            Route::get('/{escrow}', [VendorEscrowController::class, 'show'])->name('show');
        });

        // Reviews on my listings
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', [VendorReviewController::class, 'index'])->name('index');
            Route::post('/{review}/respond', [VendorReviewController::class, 'respond'])->name('respond');
        });

        // Storefront settings
        Route::get('/storefront', [StorefrontSettingsController::class, 'index'])->name('storefront');
        Route::put('/storefront', [StorefrontSettingsController::class, 'update'])->name('storefront.update');
        Route::post('/storefront/branding', [StorefrontSettingsController::class, 'updateBranding'])->name('storefront.branding');

        // Custom category requests
        Route::prefix('category-requests')->name('category-requests.')->group(function () {
            Route::get('/', [CategoryRequestController::class, 'index'])->name('index');
            Route::post('/', [CategoryRequestController::class, 'store'])->name('store');
        });

        // KYC
        Route::prefix('kyc')->name('kyc.')->group(function () {
            Route::get('/', [VendorKYCController::class, 'index'])->name('index');
            Route::post('/', [VendorKYCController::class, 'submit'])->name('submit');
        });

        // Clients (CRM)
        Route::prefix('clients')->name('clients.')->group(function () {
            Route::get('/', [ClientController::class, 'index'])->name('index');
            Route::get('/create', [ClientController::class, 'create'])->name('create');
            Route::post('/', [ClientController::class, 'store'])->name('store');
            Route::post('/sync', [ClientController::class, 'sync'])->name('sync');
            Route::get('/{client}', [ClientController::class, 'show'])->name('show');
            Route::get('/{client}/edit', [ClientController::class, 'edit'])->name('edit');
            Route::put('/{client}', [ClientController::class, 'update'])->name('update');
            Route::delete('/{client}', [ClientController::class, 'destroy'])->name('destroy');
            Route::post('/{client}/interactions', [ClientController::class, 'addInteraction'])->name('interactions');
            Route::post('/{client}/interactions/{interaction}/complete', [ClientController::class, 'completeTask'])->name('interactions.complete');
        });

        // Invoices & Quotations (documents) — one controller, type inferred from route name
        $documents = function () {
            Route::get('/', [DocumentController::class, 'index'])->name('index');
            Route::get('/create', [DocumentController::class, 'create'])->name('create');
            Route::post('/', [DocumentController::class, 'store'])->name('store');
            Route::get('/{document}', [DocumentController::class, 'show'])->name('show');
            Route::get('/{document}/edit', [DocumentController::class, 'edit'])->name('edit');
            Route::put('/{document}', [DocumentController::class, 'update'])->name('update');
            Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('destroy');
            Route::get('/{document}/print', [DocumentController::class, 'print'])->name('print');
            Route::post('/{document}/send', [DocumentController::class, 'send'])->name('send');
            Route::post('/{document}/payment', [DocumentController::class, 'recordPayment'])->name('payment');
            Route::post('/{document}/convert', [DocumentController::class, 'convert'])->name('convert');
            Route::post('/{document}/cancel', [DocumentController::class, 'cancel'])->name('cancel');
        };

        Route::middleware('feature:invoices')->prefix('invoices')->name('invoices.')->group($documents);
        Route::middleware('feature:invoices')->prefix('estimates')->name('estimates.')->group($documents);
        Route::middleware('feature:invoices')->prefix('contracts')->name('contracts.')->group($documents);

        // Analytics
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');

        // Business matching (leads)
        Route::middleware('feature:matching')->prefix('matching')->name('matching.')->group(function () {
            Route::get('/', [VendorMatchController::class, 'index'])->name('index');
            Route::get('/profile', [VendorMatchController::class, 'profile'])->name('profile');
            Route::put('/profile', [VendorMatchController::class, 'saveProfile'])->name('profile.save');
            Route::post('/{match}/respond', [VendorMatchController::class, 'respond'])->name('respond');
        });

        // Affiliates / referrals
        Route::middleware('feature:affiliates')->prefix('affiliates')->name('affiliates.')->group(function () {
            Route::get('/', [AffiliateController::class, 'index'])->name('index');
            Route::post('/enroll', [AffiliateController::class, 'enroll'])->name('enroll');
            Route::get('/commissions', [AffiliateController::class, 'commissions'])->name('commissions');
            Route::get('/referrals', [AffiliateController::class, 'referrals'])->name('referrals');
        });

        // Subscription
        Route::middleware('feature:subscriptions')->prefix('subscription')->name('subscription.')->group(function () {
            Route::get('/', [SubscriptionController::class, 'index'])->name('index');
            Route::post('/subscribe/{plan}', [SubscriptionController::class, 'subscribe'])->name('subscribe');
            Route::post('/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
        });
    });
});
