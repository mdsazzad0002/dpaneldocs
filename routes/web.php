<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SupportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public site
|--------------------------------------------------------------------------
|
| Static pages and documentation (Markdown in resources/docs) plus the help
| desk forms. Visitors never need an account: tickets are followed through a
| private link that is emailed to the customer.
|
*/

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');

Route::get('/docs', [DocsController::class, 'index'])->name('docs.index');
Route::get('/docs/search', [DocsController::class, 'search'])->middleware('throttle:60,1')->name('docs.search');
Route::get('/docs/{slug}', [DocsController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('docs.show');
Route::post('/docs/feedback', [FeedbackController::class, 'store'])->middleware('throttle:10,1')->name('docs.feedback');

Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
Route::post('/reviews', [ReviewController::class, 'store'])->middleware('throttle:3,10')->name('reviews.store');

Route::get('/support', [SupportController::class, 'index'])->name('support.index');
Route::post('/support/tickets', [SupportController::class, 'store'])->middleware('throttle:5,10')->name('support.tickets.store');
Route::get('/support/tickets/{reference}', [SupportController::class, 'show'])->middleware('throttle:30,1')->name('support.tickets.show');
Route::post('/support/tickets/{reference}/reply', [SupportController::class, 'reply'])->middleware('throttle:10,1')->name('support.tickets.reply');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

/*
|--------------------------------------------------------------------------
| Help desk (administrators only)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', Admin\DashboardController::class)->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/tickets', [Admin\TicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{ticket}', [Admin\TicketController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{ticket}/reply', [Admin\TicketController::class, 'reply'])->name('tickets.reply');
        Route::patch('/tickets/{ticket}', [Admin\TicketController::class, 'update'])->name('tickets.update');
        Route::delete('/tickets/{ticket}', [Admin\TicketController::class, 'destroy'])->name('tickets.destroy');

        Route::get('/reviews', [Admin\ReviewController::class, 'index'])->name('reviews.index');
        Route::patch('/reviews/{review}', [Admin\ReviewController::class, 'update'])->name('reviews.update');
        Route::delete('/reviews/{review}', [Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');

        Route::get('/feedback', [Admin\FeedbackController::class, 'index'])->name('feedback.index');
        Route::delete('/feedback/{feedback}', [Admin\FeedbackController::class, 'destroy'])->name('feedback.destroy');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
