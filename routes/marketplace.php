<?php

use App\Http\Controllers\Cart\CartController;
use App\Http\Controllers\Consultations\BookingController;
use App\Http\Controllers\Consultations\ConsultationController;
use App\Http\Controllers\Disputes\DisputeController;
use App\Http\Controllers\Escrow\EscrowController;
use App\Http\Controllers\Freelance\ServiceController;
use App\Http\Controllers\Invoices\InvoiceController;
use App\Http\Controllers\KYC\KYCController;
use App\Http\Controllers\Matching\MatchRequestController;
use App\Http\Controllers\Notifications\NotificationController;
use App\Http\Controllers\Orders\OrderController;
use App\Http\Controllers\Payment\BankTransferController;
use App\Http\Controllers\Payment\CheckoutController;
use App\Http\Controllers\Payment\PhysicalCheckoutController;
use App\Http\Controllers\PricingCalculatorController;
use App\Http\Controllers\Products\DownloadController;
use App\Http\Controllers\Products\ProductController;
use App\Http\Controllers\Requests\ProductRequestController;
use App\Http\Controllers\Returns\ReturnController;
use App\Http\Controllers\Reviews\ReviewController;
use App\Http\Controllers\ServiceOrderController;
use App\Http\Controllers\Social\FollowController;
use App\Http\Controllers\Vendor\StorefrontController;
use App\Http\Controllers\Wallet\WalletController;
use Illuminate\Support\Facades\Route;

// Public marketplace routes
Route::prefix('marketplace')->name('marketplace.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

    Route::middleware('feature:services')->group(function () {
        Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
        Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('services.show');
        Route::post('/services/{slug}/order', [ServiceController::class, 'placeOrder'])
            ->middleware(['auth', 'buyer.active'])->name('services.order');
    });

    Route::middleware('feature:requests')->group(function () {
        Route::get('/requests', [ProductRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/{id}', [ProductRequestController::class, 'show'])->name('requests.show');
    });

    Route::middleware('feature:consultations')->group(function () {
        Route::get('/consultants', [ConsultationController::class, 'index'])->name('consultants.index');
        Route::get('/consultants/{slug}', [ConsultationController::class, 'show'])->name('consultants.show');
    });
});

// Public vendor directory + storefronts
Route::get('/vendors', [StorefrontController::class, 'index'])->name('vendors.index');
Route::get('/store/{username}', [StorefrontController::class, 'show'])->name('storefront.show');

// Shopping cart — buildable by guests; checkout requires auth
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/products/{product}', [CartController::class, 'addProduct'])->name('products.add');
    Route::patch('/products/{product}', [CartController::class, 'updateProduct'])->name('products.update');
    Route::delete('/products/{product}', [CartController::class, 'removeProduct'])->name('products.remove');
    Route::post('/physical/{product}', [CartController::class, 'addPhysical'])->middleware('feature:physical_products')->name('physical.add');
    Route::patch('/physical/{product}', [CartController::class, 'updatePhysical'])->name('physical.update');
    Route::delete('/physical/{product}', [CartController::class, 'removePhysical'])->name('physical.remove');
    Route::post('/services/{package}', [CartController::class, 'addService'])->middleware('feature:services')->name('services.add');
    Route::delete('/services/{package}', [CartController::class, 'removeService'])->name('services.remove');
    Route::delete('/', [CartController::class, 'clear'])->name('clear');

    Route::middleware('auth')->group(function () {
        Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
        Route::post('/checkout', [CartController::class, 'process'])->middleware(['throttle:20,1', 'buyer.active'])->name('process');
    });
});

// Payment-gateway RETURN endpoints — intentionally PUBLIC (no auth).
// The browser returns here from an external gateway (Paystack), where the
// session cookie may not survive the round-trip (e.g. host mismatch like
// localhost vs 127.0.0.1, browser privacy modes, or the session expiring while
// on the gateway page). These verify the payment by its reference, not by the
// session, so they never need to bounce the user to login. Actual fulfilment is
// driven server-side by the webhook + verifyByReference.
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/callback', [CheckoutController::class, 'callback'])->name('callback');
    Route::get('/success', [CheckoutController::class, 'success'])->name('success');
    Route::get('/failed', [CheckoutController::class, 'failed'])->name('failed');
});

// Authenticated marketplace routes
Route::middleware('auth')->group(function () {

    // Orders
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::post('/{order}/complete', [OrderController::class, 'markComplete'])->name('complete');
        Route::post('/{order}/returns', [ReturnController::class, 'store'])->name('returns.store');
    });

    // Returns / RMA (buyer)
    Route::middleware('feature:returns')->prefix('returns')->name('returns.')->group(function () {
        Route::get('/', [ReturnController::class, 'index'])->name('index');
        Route::post('/{return}/shipped', [ReturnController::class, 'markShipped'])->name('shipped');
        Route::post('/{return}/cancel', [ReturnController::class, 'cancel'])->name('cancel');
    });

    // Checkout
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/product/{product}', [CheckoutController::class, 'product'])->name('product');
        Route::get('/physical/{product}', [PhysicalCheckoutController::class, 'show'])->middleware('feature:physical_products')->name('physical');
        Route::post('/physical/{product}', [PhysicalCheckoutController::class, 'process'])->middleware(['throttle:20,1', 'buyer.active', 'feature:physical_products'])->name('physical.process');
        Route::get('/service-order/{serviceOrder}', [CheckoutController::class, 'serviceOrder'])->name('service-order');
        Route::get('/consultation/{session}', [CheckoutController::class, 'consultation'])->name('consultation');
        Route::post('/process', [CheckoutController::class, 'process'])->middleware(['throttle:20,1', 'buyer.active'])->name('process');
        Route::get('/bank-transfer/{payment}', [CheckoutController::class, 'bankTransfer'])->name('bank-transfer');
        Route::post('/bank-transfer/{payment}/proof', [BankTransferController::class, 'uploadProof'])->name('bank-transfer.proof');
        Route::get('/pending/{payment}', [CheckoutController::class, 'pending'])->name('pending');
    });

    // Product requests (reverse marketplace)
    Route::middleware('feature:requests')->prefix('requests')->name('requests.')->group(function () {
        Route::get('/my-requests', [ProductRequestController::class, 'myRequests'])->name('my');
        Route::get('/create', [ProductRequestController::class, 'create'])->name('create');
        Route::post('/', [ProductRequestController::class, 'store'])->name('store');
        Route::post('/{request}/accept/{quotation}', [ProductRequestController::class, 'acceptQuotation'])->name('accept-quotation');
        Route::post('/{request}/close', [ProductRequestController::class, 'close'])->name('close');
    });

    // Wallet
    Route::middleware('feature:wallet')->prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('index');
        Route::post('/fund', [WalletController::class, 'fund'])->middleware('throttle:20,1')->name('fund');
        Route::post('/withdraw', [WalletController::class, 'withdraw'])->name('withdraw');
        Route::get('/transactions', [WalletController::class, 'transactions'])->name('transactions');
    });

    // Escrow (buyer view) — engine keeps running; this only gates the buyer pages.
    Route::middleware('feature:escrow')->prefix('escrows')->name('escrows.')->group(function () {
        Route::get('/', [EscrowController::class, 'index'])->name('index');
        Route::get('/{escrow}', [EscrowController::class, 'show'])->name('show');
    });

    // Disputes
    Route::prefix('disputes')->name('disputes.')->group(function () {
        Route::get('/', [DisputeController::class, 'index'])->name('index');
        Route::get('/create/{escrow}', [DisputeController::class, 'create'])->name('create');
        Route::post('/{escrow}', [DisputeController::class, 'store'])->name('store');
        Route::get('/{dispute}', [DisputeController::class, 'show'])->name('show');
        Route::post('/{dispute}/message', [DisputeController::class, 'addMessage'])->name('message');
    });

    // KYC
    Route::prefix('kyc')->name('kyc.')->group(function () {
        Route::get('/', [KYCController::class, 'index'])->name('index');
        Route::post('/submit', [KYCController::class, 'submit'])->name('submit');
    });

    // Business matching
    Route::middleware('feature:matching')->prefix('matching')->name('matching.')->group(function () {
        Route::get('/', [MatchRequestController::class, 'index'])->name('index');
        Route::get('/create', [MatchRequestController::class, 'create'])->name('create');
        Route::post('/', [MatchRequestController::class, 'store'])->name('store');
        Route::get('/{matchRequest}', [MatchRequestController::class, 'show'])->name('show');
        Route::post('/{matchRequest}/close', [MatchRequestController::class, 'close'])->name('close');
        Route::post('/{matchRequest}/matches/{match}/respond', [MatchRequestController::class, 'respond'])->name('respond');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/feed', [NotificationController::class, 'feed'])->name('feed');
        Route::get('/preferences', [NotificationController::class, 'preferences'])->name('preferences');
        Route::put('/preferences', [NotificationController::class, 'updatePreferences'])->name('preferences.update');
        Route::post('/read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
        Route::delete('/clear', [NotificationController::class, 'clearAll'])->name('clear');
        Route::get('/{id}/open', [NotificationController::class, 'open'])->name('open');
        Route::post('/{id}/read', [NotificationController::class, 'markRead'])->name('read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    // Following (social commerce)
    Route::get('/following', [FollowController::class, 'index'])->name('follow.index');
    Route::post('/follow/{vendor}', [FollowController::class, 'toggle'])->name('follow.toggle');

    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::post('/reviews/{review}/helpful', [ReviewController::class, 'helpful'])->name('reviews.helpful');

    // Invoices & quotations (client view)
    Route::middleware('feature:invoices')->prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
        Route::get('/{invoice}/download', [InvoiceController::class, 'download'])->name('download');
        Route::post('/{invoice}/accept', [InvoiceController::class, 'accept'])->name('accept');
        Route::post('/{invoice}/decline', [InvoiceController::class, 'decline'])->name('decline');
        Route::post('/{invoice}/pay', [InvoiceController::class, 'pay'])->name('pay');
    });

    // Consultations (booking)
    Route::middleware('feature:consultations')->prefix('consultations')->name('consultations.')->group(function () {
        Route::get('/book/{consultant:slug}', [BookingController::class, 'book'])->name('book');
        Route::post('/book/{consultant:slug}', [BookingController::class, 'store'])->name('book.store');
        Route::get('/my-sessions', [BookingController::class, 'mySessions'])->name('sessions');
        Route::get('/sessions/{session}', [BookingController::class, 'show'])->name('sessions.show');
        Route::post('/sessions/{session}/cancel', [BookingController::class, 'cancel'])->name('sessions.cancel');
    });

    // Pricing calculator
    // Service orders (buyer-facing)
    Route::middleware('feature:services')->prefix('service-orders')->name('service-orders.')->group(function () {
        Route::get('/', [ServiceOrderController::class, 'index'])->name('index');
        Route::get('/{serviceOrder}', [ServiceOrderController::class, 'show'])->name('show');
        Route::post('/{serviceOrder}/requirements', [ServiceOrderController::class, 'submitRequirements'])->name('requirements');
        Route::post('/{serviceOrder}/revision', [ServiceOrderController::class, 'requestRevision'])->name('revision');
        Route::post('/{serviceOrder}/complete', [ServiceOrderController::class, 'complete'])->name('complete');
        Route::post('/{serviceOrder}/message', [ServiceOrderController::class, 'sendMessage'])->name('message');
    });

    // Product downloads (signed URL required)
    Route::get('/orders/{order}/download/{productFile}', [DownloadController::class, 'download'])
        ->name('products.download');
    Route::post('/orders/{order}/download/{productFile}/link', [DownloadController::class, 'generateLink'])
        ->name('products.download.link');

    // Pricing calculator
    Route::middleware('feature:pricing_calculator')->prefix('pricing-calculator')->name('pricing-calculator.')->group(function () {
        Route::get('/', [PricingCalculatorController::class, 'index'])->name('index');
        Route::post('/calculate', [PricingCalculatorController::class, 'calculate'])->name('calculate');
        Route::post('/save', [PricingCalculatorController::class, 'save'])->name('save');
        Route::get('/my-estimates', [PricingCalculatorController::class, 'myEstimates'])->name('my-estimates');
        Route::get('/templates', [PricingCalculatorController::class, 'loadTemplates'])->name('templates');
        Route::get('/{reference}', [PricingCalculatorController::class, 'show'])->name('show');
    });
});
