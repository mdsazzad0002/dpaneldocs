<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\DocumentationPublicController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [DocumentationPublicController::class, 'index'])->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Documentation: any authenticated user may submit/edit their own posts
    // and versions (ownership enforced in the controller); only users with
    // the manage_documentation permission may approve or reject submissions.
    Route::get('/documentation', [DocumentationController::class, 'index'])->name('documentation.index');
    Route::get('/documentation/create', [DocumentationController::class, 'create'])->name('documentation.create');
    Route::post('/documentation', [DocumentationController::class, 'store'])->name('documentation.store');
    Route::get('/documentation/{id}/edit', [DocumentationController::class, 'edit'])->name('documentation.edit');
    Route::patch('/documentation/{id}', [DocumentationController::class, 'update'])->name('documentation.update');
    Route::delete('/documentation/{id}', [DocumentationController::class, 'destroy'])->name('documentation.destroy');
    Route::post('/documentation/{id}/versions', [DocumentationController::class, 'storeVersion'])->name('documentation.versions.store');
    Route::delete('/documentation/{id}/versions/{versionId}', [DocumentationController::class, 'destroyVersion'])->name('documentation.versions.destroy');
    Route::post('/documentation/{id}/approve', [DocumentationController::class, 'approve'])
        ->middleware('admin')
        ->name('documentation.approve');
    Route::post('/documentation/{id}/reject', [DocumentationController::class, 'reject'])
        ->middleware('admin')
        ->name('documentation.reject');

    // Categories: admin-only management for organizing documentation posts.
    Route::middleware('admin')->group(function () {
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::patch('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });
});

// Public documentation: published posts and their version downloads are
// meant to be browsed and pulled by anyone, without logging in.
Route::get('/docs', [DocumentationPublicController::class, 'index'])->name('docs.public.index');
Route::get('/docs/search', [DocumentationPublicController::class, 'search'])
    ->middleware('throttle:60,1')
    ->name('docs.public.search');
Route::get('/docs/{slug}', [DocumentationPublicController::class, 'show'])->name('docs.public.show');
Route::get('/docs/{slug}/download/{versionId}', [DocumentationPublicController::class, 'download'])
    ->middleware('throttle:30,1')
    ->name('docs.public.download');

require __DIR__.'/auth.php';
