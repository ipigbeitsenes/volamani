<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('vendor')->name('vendor.')->group(function () {

    // Onboarding (before approval)
    Route::get('/onboarding', [\App\Http\Controllers\Vendor\OnboardingController::class, 'index'])->name('onboarding');
    Route::post('/onboarding', [\App\Http\Controllers\Vendor\OnboardingController::class, 'store'])->name('onboarding.store');

    // All routes below require an approved vendor account
    Route::middleware('vendor.approved')->group(function () {

        Route::get('/dashboard', [\App\Http\Controllers\Vendor\VendorDashboardController::class, 'index'])->name('dashboard');

        // Products
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Vendor\ProductManagementController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Vendor\ProductManagementController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Vendor\ProductManagementController::class, 'store'])->name('store');
            Route::get('/{product}/edit', [\App\Http\Controllers\Vendor\ProductManagementController::class, 'edit'])->name('edit');
            Route::put('/{product}', [\App\Http\Controllers\Vendor\ProductManagementController::class, 'update'])->name('update');
            Route::delete('/{product}', [\App\Http\Controllers\Vendor\ProductManagementController::class, 'destroy'])->name('destroy');
            Route::delete('/gallery/{image}', [\App\Http\Controllers\Vendor\ProductManagementController::class, 'deleteGalleryImage'])->name('gallery.delete');
            Route::delete('/files/{file}', [\App\Http\Controllers\Vendor\ProductManagementController::class, 'deleteFile'])->name('files.delete');
        });

        // Service orders received
        Route::prefix('service-orders')->name('service-orders.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Vendor\ServiceOrderManagementController::class, 'index'])->name('index');
            Route::get('/{serviceOrder}', [\App\Http\Controllers\ServiceOrderController::class, 'show'])->name('show');
            Route::post('/{serviceOrder}/deliver', [\App\Http\Controllers\Vendor\ServiceOrderManagementController::class, 'deliver'])->name('deliver');
            Route::post('/{serviceOrder}/message', [\App\Http\Controllers\Vendor\ServiceOrderManagementController::class, 'sendMessage'])->name('message');
        });

        // Services
        Route::prefix('services')->name('services.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Vendor\ServiceManagementController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Vendor\ServiceManagementController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Vendor\ServiceManagementController::class, 'store'])->name('store');
            Route::get('/{service}/edit', [\App\Http\Controllers\Vendor\ServiceManagementController::class, 'edit'])->name('edit');
            Route::put('/{service}', [\App\Http\Controllers\Vendor\ServiceManagementController::class, 'update'])->name('update');
            Route::delete('/{service}', [\App\Http\Controllers\Vendor\ServiceManagementController::class, 'destroy'])->name('destroy');
        });

        // Orders received
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Vendor\VendorOrderController::class, 'index'])->name('index');
            Route::get('/{order}', [\App\Http\Controllers\Vendor\VendorOrderController::class, 'show'])->name('show');
            Route::post('/{order}/deliver', [\App\Http\Controllers\Vendor\VendorOrderController::class, 'markDelivered'])->name('deliver');
            Route::post('/{order}/upload', [\App\Http\Controllers\Vendor\VendorOrderController::class, 'uploadDeliverable'])->name('upload');
        });

        // Quotations for product requests
        Route::prefix('quotations')->name('quotations.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Vendor\QuotationController::class, 'index'])->name('index');
            Route::post('/requests/{request}', [\App\Http\Controllers\Vendor\QuotationController::class, 'store'])->name('store');
            Route::get('/{quotation}', [\App\Http\Controllers\Vendor\QuotationController::class, 'show'])->name('show');
            Route::delete('/{quotation}', [\App\Http\Controllers\Vendor\QuotationController::class, 'destroy'])->name('withdraw');
        });

        // Consultations (if vendor is also a consultant)
        Route::prefix('consultations')->name('consultations.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Vendor\ConsultationManagementController::class, 'index'])->name('index');
            Route::get('/setup', [\App\Http\Controllers\Vendor\ConsultationManagementController::class, 'setup'])->name('setup');
            Route::post('/setup', [\App\Http\Controllers\Vendor\ConsultationManagementController::class, 'storeProfile'])->name('setup.store');
            Route::get('/profile', [\App\Http\Controllers\Vendor\ConsultationManagementController::class, 'editProfile'])->name('profile');
            Route::put('/profile/{profile}', [\App\Http\Controllers\Vendor\ConsultationManagementController::class, 'updateProfile'])->name('profile.update');
            Route::get('/packages', [\App\Http\Controllers\Vendor\ConsultationManagementController::class, 'packages'])->name('packages');
            Route::post('/packages', [\App\Http\Controllers\Vendor\ConsultationManagementController::class, 'storePackage'])->name('packages.store');
            Route::post('/packages/{package}/toggle', [\App\Http\Controllers\Vendor\ConsultationManagementController::class, 'togglePackage'])->name('packages.toggle');
            Route::delete('/packages/{package}', [\App\Http\Controllers\Vendor\ConsultationManagementController::class, 'deletePackage'])->name('packages.delete');
            Route::get('/schedule', [\App\Http\Controllers\Vendor\ConsultationManagementController::class, 'schedule'])->name('schedule');
            Route::put('/schedule/{profile}', [\App\Http\Controllers\Vendor\ConsultationManagementController::class, 'updateSchedule'])->name('schedule.update');
            Route::get('/sessions', [\App\Http\Controllers\Vendor\ConsultationManagementController::class, 'sessions'])->name('sessions');
            Route::get('/sessions/{session}', [\App\Http\Controllers\Vendor\ConsultationManagementController::class, 'showSession'])->name('sessions.show');
            Route::post('/sessions/{session}/confirm', [\App\Http\Controllers\Vendor\ConsultationManagementController::class, 'confirmSession'])->name('sessions.confirm');
            Route::post('/sessions/{session}/complete', [\App\Http\Controllers\Vendor\ConsultationManagementController::class, 'completeSession'])->name('sessions.complete');
            Route::post('/sessions/{session}/cancel', [\App\Http\Controllers\Vendor\ConsultationManagementController::class, 'cancelSession'])->name('sessions.cancel');
        });

        // Wallet & withdrawals
        Route::prefix('wallet')->name('wallet.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Vendor\VendorWalletController::class, 'index'])->name('index');
            Route::post('/withdraw', [\App\Http\Controllers\Vendor\VendorWalletController::class, 'requestWithdrawal'])->name('withdraw');
            Route::get('/transactions', [\App\Http\Controllers\Vendor\VendorWalletController::class, 'transactions'])->name('transactions');
        });

        // Escrow (vendor view — pending earnings)
        Route::prefix('escrows')->name('escrows.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Vendor\VendorEscrowController::class, 'index'])->name('index');
            Route::get('/{escrow}', [\App\Http\Controllers\Vendor\VendorEscrowController::class, 'show'])->name('show');
        });

        // Reviews on my listings
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Vendor\VendorReviewController::class, 'index'])->name('index');
            Route::post('/{review}/respond', [\App\Http\Controllers\Vendor\VendorReviewController::class, 'respond'])->name('respond');
        });

        // Storefront settings
        Route::get('/storefront', [\App\Http\Controllers\Vendor\StorefrontSettingsController::class, 'index'])->name('storefront');
        Route::put('/storefront', [\App\Http\Controllers\Vendor\StorefrontSettingsController::class, 'update'])->name('storefront.update');

        // KYC
        Route::prefix('kyc')->name('kyc.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Vendor\VendorKYCController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Vendor\VendorKYCController::class, 'submit'])->name('submit');
        });

        // Clients (CRM)
        Route::prefix('clients')->name('clients.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Vendor\ClientController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Vendor\ClientController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Vendor\ClientController::class, 'store'])->name('store');
            Route::post('/sync', [\App\Http\Controllers\Vendor\ClientController::class, 'sync'])->name('sync');
            Route::get('/{client}', [\App\Http\Controllers\Vendor\ClientController::class, 'show'])->name('show');
            Route::get('/{client}/edit', [\App\Http\Controllers\Vendor\ClientController::class, 'edit'])->name('edit');
            Route::put('/{client}', [\App\Http\Controllers\Vendor\ClientController::class, 'update'])->name('update');
            Route::delete('/{client}', [\App\Http\Controllers\Vendor\ClientController::class, 'destroy'])->name('destroy');
            Route::post('/{client}/interactions', [\App\Http\Controllers\Vendor\ClientController::class, 'addInteraction'])->name('interactions');
            Route::post('/{client}/interactions/{interaction}/complete', [\App\Http\Controllers\Vendor\ClientController::class, 'completeTask'])->name('interactions.complete');
        });

        // Invoices & Quotations (documents) — one controller, type inferred from route name
        $documents = function () {
            Route::get('/', [\App\Http\Controllers\Vendor\DocumentController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Vendor\DocumentController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Vendor\DocumentController::class, 'store'])->name('store');
            Route::get('/{document}', [\App\Http\Controllers\Vendor\DocumentController::class, 'show'])->name('show');
            Route::get('/{document}/edit', [\App\Http\Controllers\Vendor\DocumentController::class, 'edit'])->name('edit');
            Route::put('/{document}', [\App\Http\Controllers\Vendor\DocumentController::class, 'update'])->name('update');
            Route::delete('/{document}', [\App\Http\Controllers\Vendor\DocumentController::class, 'destroy'])->name('destroy');
            Route::get('/{document}/print', [\App\Http\Controllers\Vendor\DocumentController::class, 'print'])->name('print');
            Route::post('/{document}/send', [\App\Http\Controllers\Vendor\DocumentController::class, 'send'])->name('send');
            Route::post('/{document}/payment', [\App\Http\Controllers\Vendor\DocumentController::class, 'recordPayment'])->name('payment');
            Route::post('/{document}/convert', [\App\Http\Controllers\Vendor\DocumentController::class, 'convert'])->name('convert');
            Route::post('/{document}/cancel', [\App\Http\Controllers\Vendor\DocumentController::class, 'cancel'])->name('cancel');
        };

        Route::prefix('invoices')->name('invoices.')->group($documents);
        Route::prefix('estimates')->name('estimates.')->group($documents);

        // Analytics
        Route::get('/analytics', [\App\Http\Controllers\Vendor\AnalyticsController::class, 'index'])->name('analytics');

        // Business matching (leads)
        Route::prefix('matching')->name('matching.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Vendor\VendorMatchController::class, 'index'])->name('index');
            Route::get('/profile', [\App\Http\Controllers\Vendor\VendorMatchController::class, 'profile'])->name('profile');
            Route::put('/profile', [\App\Http\Controllers\Vendor\VendorMatchController::class, 'saveProfile'])->name('profile.save');
            Route::post('/{match}/respond', [\App\Http\Controllers\Vendor\VendorMatchController::class, 'respond'])->name('respond');
        });

        // Affiliates / referrals
        Route::prefix('affiliates')->name('affiliates.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Vendor\AffiliateController::class, 'index'])->name('index');
            Route::post('/enroll', [\App\Http\Controllers\Vendor\AffiliateController::class, 'enroll'])->name('enroll');
            Route::get('/commissions', [\App\Http\Controllers\Vendor\AffiliateController::class, 'commissions'])->name('commissions');
            Route::get('/referrals', [\App\Http\Controllers\Vendor\AffiliateController::class, 'referrals'])->name('referrals');
        });

        // Subscription
        Route::prefix('subscription')->name('subscription.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Vendor\SubscriptionController::class, 'index'])->name('index');
            Route::post('/subscribe/{plan}', [\App\Http\Controllers\Vendor\SubscriptionController::class, 'subscribe'])->name('subscribe');
            Route::post('/cancel', [\App\Http\Controllers\Vendor\SubscriptionController::class, 'cancel'])->name('cancel');
        });
    });
});
