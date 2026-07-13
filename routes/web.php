<?php

use App\Http\Controllers\Affiliate\ReferralLinkController;
use App\Http\Controllers\Chat\ChatWidgetController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Invoices\PublicDocumentController;
use App\Http\Controllers\MessagingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// XML sitemap for search engines (referenced from robots.txt).
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Deep health probe (DB + cache + queue backlog) for load balancers / monitors.
// The framework's /up only proves the app booted; this checks live dependencies.
Route::get('/health', HealthController::class)->name('health');

// Public informational pages (footer links)
Route::controller(PageController::class)->name('pages.')->group(function () {
    Route::get('/about', 'about')->name('about');
    Route::get('/help', 'help')->name('help');
    Route::get('/seller-guide', 'sellerGuide')->name('seller-guide');
    Route::get('/contact', 'contact')->name('contact');
    Route::post('/contact', 'contactSubmit')->middleware('throttle:6,1')->name('contact.submit');
    Route::get('/legal/{slug}', 'legal')
        ->whereIn('slug', ['privacy', 'terms', 'cookies', 'refunds', 'disputes'])
        ->name('legal');
});

// Public buyer-protection guarantee page (top-level name for easy linking).
Route::get('/buyer-protection', [PageController::class, 'buyerProtection'])->name('buyer-protection');

// Live chat widget — public/guest accessible. Guest threads are guarded by the
// unguessable conversation token; member threads additionally check the auth id.
Route::prefix('chat')->name('chat.')->group(function () {
    Route::get('/config', [ChatWidgetController::class, 'config'])->name('config');
    Route::post('/start', [ChatWidgetController::class, 'start'])->middleware('throttle:40,1')->name('start');
    Route::post('/{token}/message', [ChatWidgetController::class, 'message'])->middleware('throttle:40,1')->name('message');
    Route::get('/{token}/messages', [ChatWidgetController::class, 'messages'])->name('messages');
});

// Affiliate share link (public) — tracks the click then forwards to signup
Route::get('/r/{code}', [ReferralLinkController::class, 'track'])->middleware('feature:affiliates')->name('referral.track');

// Public invoice / quotation share links — opened by clients with NO account.
// Authorisation is the unguessable token itself; never require login here.
Route::middleware('feature:invoices')->prefix('i')->name('public.documents.')->group(function () {
    Route::get('/{token}', [PublicDocumentController::class, 'show'])->name('show');
    Route::get('/{token}/print', [PublicDocumentController::class, 'print'])->name('print');
    Route::post('/{token}/pay', [PublicDocumentController::class, 'pay'])->middleware('throttle:15,1')->name('pay');
    Route::post('/{token}/accept', [PublicDocumentController::class, 'accept'])->name('accept');
    Route::post('/{token}/decline', [PublicDocumentController::class, 'decline'])->name('decline');
    Route::post('/{token}/sign', [PublicDocumentController::class, 'sign'])->middleware('throttle:15,1')->name('sign');
});

// In-app buyer ↔ seller messaging (one unified inbox for both roles).
Route::middleware(['auth', 'feature:messaging'])->prefix('messages')->name('messages.')->group(function () {
    Route::get('/', [MessagingController::class, 'index'])->name('index');
    Route::get('/compose', [MessagingController::class, 'compose'])->name('compose');
    Route::post('/start', [MessagingController::class, 'start'])->middleware('throttle:30,1')->name('start');
    Route::get('/{conversation}', [MessagingController::class, 'show'])->name('show');
    Route::post('/{conversation}', [MessagingController::class, 'reply'])->middleware('throttle:60,1')->name('reply');
});

require __DIR__.'/auth.php';
require __DIR__.'/marketplace.php';
require __DIR__.'/vendor.php';
require __DIR__.'/admin.php';
require __DIR__.'/support.php';
require __DIR__.'/finance.php';
require __DIR__.'/webhooks.php';
