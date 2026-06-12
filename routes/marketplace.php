<?php

use Illuminate\Support\Facades\Route;

// Public marketplace routes
Route::prefix('marketplace')->name('marketplace.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Products\ProductController::class, 'index'])->name('index');
    Route::get('/products', [\App\Http\Controllers\Products\ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{slug}', [\App\Http\Controllers\Products\ProductController::class, 'show'])->name('products.show');

    Route::get('/services', [\App\Http\Controllers\Freelance\ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{slug}', [\App\Http\Controllers\Freelance\ServiceController::class, 'show'])->name('services.show');
    Route::post('/services/{slug}/order', [\App\Http\Controllers\Freelance\ServiceController::class, 'placeOrder'])
        ->middleware('auth')->name('services.order');

    Route::get('/requests', [\App\Http\Controllers\Requests\ProductRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/{id}', [\App\Http\Controllers\Requests\ProductRequestController::class, 'show'])->name('requests.show');

    Route::get('/consultants', [\App\Http\Controllers\Consultations\ConsultationController::class, 'index'])->name('consultants.index');
    Route::get('/consultants/{slug}', [\App\Http\Controllers\Consultations\ConsultationController::class, 'show'])->name('consultants.show');
});

// Public vendor directory + storefronts
Route::get('/vendors', [\App\Http\Controllers\Vendor\StorefrontController::class, 'index'])->name('vendors.index');
Route::get('/store/{username}', [\App\Http\Controllers\Vendor\StorefrontController::class, 'show'])->name('storefront.show');

// Shopping cart — buildable by guests; checkout requires auth
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Cart\CartController::class, 'index'])->name('index');
    Route::post('/products/{product}', [\App\Http\Controllers\Cart\CartController::class, 'addProduct'])->name('products.add');
    Route::patch('/products/{product}', [\App\Http\Controllers\Cart\CartController::class, 'updateProduct'])->name('products.update');
    Route::delete('/products/{product}', [\App\Http\Controllers\Cart\CartController::class, 'removeProduct'])->name('products.remove');
    Route::post('/services/{package}', [\App\Http\Controllers\Cart\CartController::class, 'addService'])->name('services.add');
    Route::delete('/services/{package}', [\App\Http\Controllers\Cart\CartController::class, 'removeService'])->name('services.remove');
    Route::delete('/', [\App\Http\Controllers\Cart\CartController::class, 'clear'])->name('clear');

    Route::middleware('auth')->group(function () {
        Route::get('/checkout', [\App\Http\Controllers\Cart\CartController::class, 'checkout'])->name('checkout');
        Route::post('/checkout', [\App\Http\Controllers\Cart\CartController::class, 'process'])->name('process');
    });
});

// Authenticated marketplace routes
Route::middleware('auth')->group(function () {

    // Orders
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Orders\OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [\App\Http\Controllers\Orders\OrderController::class, 'show'])->name('show');
        Route::post('/{order}/complete', [\App\Http\Controllers\Orders\OrderController::class, 'markComplete'])->name('complete');
    });

    // Checkout
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/product/{product}',          [\App\Http\Controllers\Payment\CheckoutController::class, 'product'])->name('product');
        Route::get('/service-order/{serviceOrder}', [\App\Http\Controllers\Payment\CheckoutController::class, 'serviceOrder'])->name('service-order');
        Route::get('/consultation/{session}',     [\App\Http\Controllers\Payment\CheckoutController::class, 'consultation'])->name('consultation');
        Route::post('/process',                   [\App\Http\Controllers\Payment\CheckoutController::class, 'process'])->name('process');
        Route::get('/callback',                   [\App\Http\Controllers\Payment\CheckoutController::class, 'callback'])->name('callback');
        Route::get('/success',                    [\App\Http\Controllers\Payment\CheckoutController::class, 'success'])->name('success');
        Route::get('/failed',                     [\App\Http\Controllers\Payment\CheckoutController::class, 'failed'])->name('failed');
        Route::get('/bank-transfer/{payment}',    [\App\Http\Controllers\Payment\CheckoutController::class, 'bankTransfer'])->name('bank-transfer');
        Route::post('/bank-transfer/{payment}/proof', [\App\Http\Controllers\Payment\BankTransferController::class, 'uploadProof'])->name('bank-transfer.proof');
        Route::get('/pending/{payment}',          [\App\Http\Controllers\Payment\CheckoutController::class, 'pending'])->name('pending');
    });

    // Product requests (reverse marketplace)
    Route::prefix('requests')->name('requests.')->group(function () {
        Route::get('/my-requests', [\App\Http\Controllers\Requests\ProductRequestController::class, 'myRequests'])->name('my');
        Route::get('/create', [\App\Http\Controllers\Requests\ProductRequestController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Requests\ProductRequestController::class, 'store'])->name('store');
        Route::post('/{request}/accept/{quotation}', [\App\Http\Controllers\Requests\ProductRequestController::class, 'acceptQuotation'])->name('accept-quotation');
        Route::post('/{request}/close', [\App\Http\Controllers\Requests\ProductRequestController::class, 'close'])->name('close');
    });

    // Wallet
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Wallet\WalletController::class, 'index'])->name('index');
        Route::post('/fund', [\App\Http\Controllers\Wallet\WalletController::class, 'fund'])->name('fund');
        Route::post('/withdraw', [\App\Http\Controllers\Wallet\WalletController::class, 'withdraw'])->name('withdraw');
        Route::get('/transactions', [\App\Http\Controllers\Wallet\WalletController::class, 'transactions'])->name('transactions');
    });

    // Escrow (buyer view)
    Route::prefix('escrows')->name('escrows.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Escrow\EscrowController::class, 'index'])->name('index');
        Route::get('/{escrow}', [\App\Http\Controllers\Escrow\EscrowController::class, 'show'])->name('show');
    });

    // Disputes
    Route::prefix('disputes')->name('disputes.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Disputes\DisputeController::class, 'index'])->name('index');
        Route::get('/create/{escrow}', [\App\Http\Controllers\Disputes\DisputeController::class, 'create'])->name('create');
        Route::post('/{escrow}', [\App\Http\Controllers\Disputes\DisputeController::class, 'store'])->name('store');
        Route::get('/{dispute}', [\App\Http\Controllers\Disputes\DisputeController::class, 'show'])->name('show');
        Route::post('/{dispute}/message', [\App\Http\Controllers\Disputes\DisputeController::class, 'addMessage'])->name('message');
    });

    // KYC
    Route::prefix('kyc')->name('kyc.')->group(function () {
        Route::get('/', [\App\Http\Controllers\KYC\KYCController::class, 'index'])->name('index');
        Route::post('/submit', [\App\Http\Controllers\KYC\KYCController::class, 'submit'])->name('submit');
    });

    // Business matching
    Route::prefix('matching')->name('matching.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Matching\MatchRequestController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Matching\MatchRequestController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Matching\MatchRequestController::class, 'store'])->name('store');
        Route::get('/{matchRequest}', [\App\Http\Controllers\Matching\MatchRequestController::class, 'show'])->name('show');
        Route::post('/{matchRequest}/close', [\App\Http\Controllers\Matching\MatchRequestController::class, 'close'])->name('close');
        Route::post('/{matchRequest}/matches/{match}/respond', [\App\Http\Controllers\Matching\MatchRequestController::class, 'respond'])->name('respond');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Notifications\NotificationController::class, 'index'])->name('index');
        Route::get('/feed', [\App\Http\Controllers\Notifications\NotificationController::class, 'feed'])->name('feed');
        Route::get('/preferences', [\App\Http\Controllers\Notifications\NotificationController::class, 'preferences'])->name('preferences');
        Route::put('/preferences', [\App\Http\Controllers\Notifications\NotificationController::class, 'updatePreferences'])->name('preferences.update');
        Route::post('/read-all', [\App\Http\Controllers\Notifications\NotificationController::class, 'markAllRead'])->name('read-all');
        Route::delete('/clear', [\App\Http\Controllers\Notifications\NotificationController::class, 'clearAll'])->name('clear');
        Route::get('/{id}/open', [\App\Http\Controllers\Notifications\NotificationController::class, 'open'])->name('open');
        Route::post('/{id}/read', [\App\Http\Controllers\Notifications\NotificationController::class, 'markRead'])->name('read');
        Route::delete('/{id}', [\App\Http\Controllers\Notifications\NotificationController::class, 'destroy'])->name('destroy');
    });

    // Following (social commerce)
    Route::get('/following', [\App\Http\Controllers\Social\FollowController::class, 'index'])->name('follow.index');
    Route::post('/follow/{vendor}', [\App\Http\Controllers\Social\FollowController::class, 'toggle'])->name('follow.toggle');

    // Reviews
    Route::post('/reviews', [\App\Http\Controllers\Reviews\ReviewController::class, 'store'])->name('reviews.store');
    Route::post('/reviews/{review}/helpful', [\App\Http\Controllers\Reviews\ReviewController::class, 'helpful'])->name('reviews.helpful');

    // Invoices & quotations (client view)
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Invoices\InvoiceController::class, 'index'])->name('index');
        Route::get('/{invoice}', [\App\Http\Controllers\Invoices\InvoiceController::class, 'show'])->name('show');
        Route::get('/{invoice}/download', [\App\Http\Controllers\Invoices\InvoiceController::class, 'download'])->name('download');
        Route::post('/{invoice}/accept', [\App\Http\Controllers\Invoices\InvoiceController::class, 'accept'])->name('accept');
        Route::post('/{invoice}/decline', [\App\Http\Controllers\Invoices\InvoiceController::class, 'decline'])->name('decline');
        Route::post('/{invoice}/pay', [\App\Http\Controllers\Invoices\InvoiceController::class, 'pay'])->name('pay');
    });

    // Consultations (booking)
    Route::prefix('consultations')->name('consultations.')->group(function () {
        Route::get('/book/{consultant}', [\App\Http\Controllers\Consultations\BookingController::class, 'book'])->name('book');
        Route::post('/book/{consultant}', [\App\Http\Controllers\Consultations\BookingController::class, 'store'])->name('book.store');
        Route::get('/my-sessions', [\App\Http\Controllers\Consultations\BookingController::class, 'mySessions'])->name('sessions');
        Route::get('/sessions/{session}', [\App\Http\Controllers\Consultations\BookingController::class, 'show'])->name('sessions.show');
        Route::post('/sessions/{session}/cancel', [\App\Http\Controllers\Consultations\BookingController::class, 'cancel'])->name('sessions.cancel');
    });

    // Pricing calculator
    // Service orders (buyer-facing)
    Route::prefix('service-orders')->name('service-orders.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ServiceOrderController::class, 'index'])->name('index');
        Route::get('/{serviceOrder}', [\App\Http\Controllers\ServiceOrderController::class, 'show'])->name('show');
        Route::post('/{serviceOrder}/requirements', [\App\Http\Controllers\ServiceOrderController::class, 'submitRequirements'])->name('requirements');
        Route::post('/{serviceOrder}/revision', [\App\Http\Controllers\ServiceOrderController::class, 'requestRevision'])->name('revision');
        Route::post('/{serviceOrder}/complete', [\App\Http\Controllers\ServiceOrderController::class, 'complete'])->name('complete');
        Route::post('/{serviceOrder}/message', [\App\Http\Controllers\ServiceOrderController::class, 'sendMessage'])->name('message');
    });

    // Product downloads (signed URL required)
    Route::get('/orders/{order}/download/{productFile}', [\App\Http\Controllers\Products\DownloadController::class, 'download'])
        ->name('products.download');
    Route::post('/orders/{order}/download/{productFile}/link', [\App\Http\Controllers\Products\DownloadController::class, 'generateLink'])
        ->name('products.download.link');

    // Pricing calculator
    Route::prefix('pricing-calculator')->name('pricing-calculator.')->group(function () {
        Route::get('/', [\App\Http\Controllers\PricingCalculatorController::class, 'index'])->name('index');
        Route::post('/calculate', [\App\Http\Controllers\PricingCalculatorController::class, 'calculate'])->name('calculate');
        Route::post('/save', [\App\Http\Controllers\PricingCalculatorController::class, 'save'])->name('save');
        Route::get('/my-estimates', [\App\Http\Controllers\PricingCalculatorController::class, 'myEstimates'])->name('my-estimates');
        Route::get('/templates', [\App\Http\Controllers\PricingCalculatorController::class, 'loadTemplates'])->name('templates');
        Route::get('/{reference}', [\App\Http\Controllers\PricingCalculatorController::class, 'show'])->name('show');
    });
});
