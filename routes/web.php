<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\DonateController;
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
Route::post('/docs/comments', [CommentController::class, 'store'])->middleware('throttle:5,10')->name('docs.comments.store');

Route::get('/donate', [DonateController::class, 'index'])->name('donate.index');
Route::post('/donate', [DonateController::class, 'store'])->middleware('throttle:5,10')->name('donate.store');
Route::get('/donate/pc/{slug}', [DonateController::class, 'pc'])->where('slug', '[a-z0-9-]+')->name('donate.pc');

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
| Admin panel (staff only)
|--------------------------------------------------------------------------
|
| Any user with a role can sign in; each section is guarded by a permission
| (see App\Support\Permissions). Administrators have every permission.
|
*/

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', Admin\DashboardController::class)->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::middleware('can:tickets.manage')->group(function () {
            Route::get('/tickets', [Admin\TicketController::class, 'index'])->name('tickets.index');
            Route::get('/tickets/{ticket}', [Admin\TicketController::class, 'show'])->name('tickets.show');
            Route::post('/tickets/{ticket}/reply', [Admin\TicketController::class, 'reply'])->name('tickets.reply');
            Route::patch('/tickets/{ticket}', [Admin\TicketController::class, 'update'])->name('tickets.update');
            Route::delete('/tickets/{ticket}', [Admin\TicketController::class, 'destroy'])->name('tickets.destroy');
        });

        Route::middleware('can:reviews.manage')->group(function () {
            Route::get('/reviews', [Admin\ReviewController::class, 'index'])->name('reviews.index');
            Route::patch('/reviews/{review}', [Admin\ReviewController::class, 'update'])->name('reviews.update');
            Route::post('/reviews/{review}/reply', [Admin\ReviewController::class, 'reply'])->name('reviews.reply');
            Route::delete('/reviews/{review}', [Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');
        });

        Route::middleware('can:comments.manage')->group(function () {
            Route::get('/comments', [Admin\CommentController::class, 'index'])->name('comments.index');
            Route::patch('/comments/{comment}', [Admin\CommentController::class, 'update'])->name('comments.update');
            Route::post('/comments/{comment}/reply', [Admin\CommentController::class, 'reply'])->name('comments.reply');
            Route::delete('/comments/{comment}', [Admin\CommentController::class, 'destroy'])->name('comments.destroy');
        });

        Route::middleware('can:feedback.view')->group(function () {
            Route::get('/feedback', [Admin\FeedbackController::class, 'index'])->name('feedback.index');
            Route::delete('/feedback/{feedback}', [Admin\FeedbackController::class, 'destroy'])->name('feedback.destroy');
        });

        Route::middleware('can:docs.sync')->group(function () {
            Route::get('/docs', [Admin\DocsController::class, 'index'])->name('docs.index');
            Route::post('/docs/sync', [Admin\DocsController::class, 'sync'])->middleware('throttle:6,1')->name('docs.sync');
        });

        Route::middleware('can:mail.send')->group(function () {
            Route::get('/mail', [Admin\MailController::class, 'index'])->name('mail.index');
            Route::post('/mail', [Admin\MailController::class, 'store'])->name('mail.store');
            Route::delete('/mail/{mail}', [Admin\MailController::class, 'destroy'])->name('mail.destroy');
        });

        Route::post('/ai/draft', [Admin\AiController::class, 'draft'])->middleware(['can:ai.use', 'throttle:20,1'])->name('ai.draft');

        Route::middleware('can:donations.manage')->group(function () {
            Route::get('/donations', [Admin\DonationController::class, 'index'])->name('donations.index');
            Route::patch('/donations/{donation}', [Admin\DonationController::class, 'update'])->name('donations.update');
            Route::delete('/donations/{donation}', [Admin\DonationController::class, 'destroy'])->name('donations.destroy');
            Route::put('/donations/settings', [Admin\DonationController::class, 'settings'])->name('donations.settings');
            Route::post('/donation-methods', [Admin\DonationMethodController::class, 'store'])->name('donation-methods.store');
            Route::put('/donation-methods/{method}', [Admin\DonationMethodController::class, 'update'])->name('donation-methods.update');
            Route::delete('/donation-methods/{method}', [Admin\DonationMethodController::class, 'destroy'])->name('donation-methods.destroy');
        });

        Route::middleware('can:users.manage')->group(function () {
            Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
            Route::post('/users', [Admin\UserController::class, 'store'])->name('users.store');
            Route::put('/users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
            Route::delete('/users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');
        });

        Route::middleware('can:roles.manage')->group(function () {
            Route::get('/roles', [Admin\RoleController::class, 'index'])->name('roles.index');
            Route::post('/roles', [Admin\RoleController::class, 'store'])->name('roles.store');
            Route::put('/roles/{role}', [Admin\RoleController::class, 'update'])->name('roles.update');
            Route::delete('/roles/{role}', [Admin\RoleController::class, 'destroy'])->name('roles.destroy');
        });

        Route::middleware('can:settings.manage')->group(function () {
            Route::get('/settings', [Admin\SettingsController::class, 'edit'])->name('settings.edit');
            Route::put('/settings/mail', [Admin\SettingsController::class, 'updateMail'])->name('settings.mail');
            Route::post('/settings/mail/test', [Admin\SettingsController::class, 'testMail'])->middleware('throttle:5,1')->name('settings.mail.test');
            Route::put('/settings/ai', [Admin\SettingsController::class, 'updateAi'])->name('settings.ai');
        });
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
