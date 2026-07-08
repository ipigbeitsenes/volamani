<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');

// XML sitemap for search engines (referenced from robots.txt).
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// Public informational pages (footer links)
Route::controller(\App\Http\Controllers\PageController::class)->name('pages.')->group(function () {
    Route::get('/about',        'about')->name('about');
    Route::get('/help',         'help')->name('help');
    Route::get('/seller-guide', 'sellerGuide')->name('seller-guide');
    Route::get('/contact',      'contact')->name('contact');
    Route::post('/contact',     'contactSubmit')->middleware('throttle:6,1')->name('contact.submit');
    Route::get('/legal/{slug}', 'legal')
        ->whereIn('slug', ['privacy', 'terms', 'cookies', 'refunds', 'disputes'])
        ->name('legal');
});

// Public buyer-protection guarantee page (top-level name for easy linking).
Route::get('/buyer-protection', [\App\Http\Controllers\PageController::class, 'buyerProtection'])->name('buyer-protection');

// Live chat widget — public/guest accessible. Guest threads are guarded by the
// unguessable conversation token; member threads additionally check the auth id.
Route::prefix('chat')->name('chat.')->group(function () {
    Route::get('/config', [\App\Http\Controllers\Chat\ChatWidgetController::class, 'config'])->name('config');
    Route::post('/start', [\App\Http\Controllers\Chat\ChatWidgetController::class, 'start'])->middleware('throttle:40,1')->name('start');
    Route::post('/{token}/message', [\App\Http\Controllers\Chat\ChatWidgetController::class, 'message'])->middleware('throttle:40,1')->name('message');
    Route::get('/{token}/messages', [\App\Http\Controllers\Chat\ChatWidgetController::class, 'messages'])->name('messages');
});

// Affiliate share link (public) — tracks the click then forwards to signup
Route::get('/r/{code}', [\App\Http\Controllers\Affiliate\ReferralLinkController::class, 'track'])->name('referral.track');

// Public invoice / quotation share links — opened by clients with NO account.
// Authorisation is the unguessable token itself; never require login here.
Route::prefix('i')->name('public.documents.')->group(function () {
    Route::get('/{token}',         [\App\Http\Controllers\Invoices\PublicDocumentController::class, 'show'])->name('show');
    Route::get('/{token}/print',   [\App\Http\Controllers\Invoices\PublicDocumentController::class, 'print'])->name('print');
    Route::post('/{token}/pay',    [\App\Http\Controllers\Invoices\PublicDocumentController::class, 'pay'])->middleware('throttle:15,1')->name('pay');
    Route::post('/{token}/accept', [\App\Http\Controllers\Invoices\PublicDocumentController::class, 'accept'])->name('accept');
    Route::post('/{token}/decline',[\App\Http\Controllers\Invoices\PublicDocumentController::class, 'decline'])->name('decline');
    Route::post('/{token}/sign',   [\App\Http\Controllers\Invoices\PublicDocumentController::class, 'sign'])->middleware('throttle:15,1')->name('sign');
});

require __DIR__ . '/auth.php';
require __DIR__ . '/marketplace.php';
require __DIR__ . '/vendor.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/support.php';
require __DIR__ . '/finance.php';
require __DIR__ . '/webhooks.php';
