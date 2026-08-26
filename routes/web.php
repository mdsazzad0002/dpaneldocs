<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\DocumentationPublicController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [DocumentationPublicController::class, 'landing'])->name('home');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', fn () => response(
    "User-agent: *\nDisallow:\n\nSitemap: ".route('sitemap')."\n",
    200,
    ['Content-Type' => 'text/plain']
))->name('robots');

Route::get('/dashboard', function () {
    return Inertia::render('Backend/Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Documentation: any authenticated user may submit/edit their own posts
    // and versions (ownership enforced in the controller); only users with
    // the manage_documentation permission may approve or reject submissions,
    // or see/edit posts submitted by other people.
    Route::get('/documentation', [DocumentationController::class, 'index'])->name('documentation.index');
    Route::get('/documentation/create', [DocumentationController::class, 'create'])->name('documentation.create');
    Route::post('/documentation', [DocumentationController::class, 'store'])->name('documentation.store');
    Route::get('/documentation/bulk-generate', [DocumentationController::class, 'bulkGenerateCreate'])->name('documentation.bulk-generate');
    Route::post('/documentation/bulk-generate', [DocumentationController::class, 'bulkGenerateStore'])->name('documentation.bulk-generate.store');
    Route::get('/documentation/{id}/edit', [DocumentationController::class, 'edit'])->name('documentation.edit');
    Route::patch('/documentation/{id}', [DocumentationController::class, 'update'])->name('documentation.update');
    Route::delete('/documentation/{id}', [DocumentationController::class, 'destroy'])->name('documentation.destroy');
    Route::post('/documentation/{id}/versions', [DocumentationController::class, 'storeVersion'])->name('documentation.versions.store');
    Route::delete('/documentation/{id}/versions/{versionId}', [DocumentationController::class, 'destroyVersion'])->name('documentation.versions.destroy');
    Route::post('/documentation/{id}/approve', [DocumentationController::class, 'approve'])
        ->middleware('permission:manage_documentation')
        ->name('documentation.approve');
    Route::post('/documentation/{id}/reject', [DocumentationController::class, 'reject'])
        ->middleware('permission:manage_documentation')
        ->name('documentation.reject');

    // Versions: a global, cross-post view for reviewers to monitor every
    // uploaded file in one place.
    Route::middleware('permission:manage_versions')->group(function () {
        Route::get('/documentation-versions', [DocumentationController::class, 'versionsIndex'])->name('documentation.versions.index');
    });

    // Categories: for organizing documentation posts.
    Route::middleware('permission:manage_categories')->group(function () {
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::patch('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    // Users: assign roles. Roles: define what each role can do.
    Route::middleware('permission:manage_users')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::patch('/users/{id}/role', [UserController::class, 'updateRole'])->name('users.update-role');
    });
    Route::middleware('permission:manage_roles')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::patch('/roles/{id}', [RoleController::class, 'updatePermissions'])->name('roles.update');
        Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });
});

// Public documentation: published posts and their version downloads are
// meant to be browsed and pulled by anyone, without logging in. Anything
// not yet approved (pending/rejected) never appears here — every query in
// DocumentationPublicController is scoped to ->published().
Route::get('/docs', [DocumentationPublicController::class, 'index'])->name('docs.public.index');
Route::get('/docs/search', [DocumentationPublicController::class, 'search'])
    ->middleware('throttle:60,1')
    ->name('docs.public.search');
Route::get('/docs/category/{slug}', [DocumentationPublicController::class, 'category'])->name('docs.public.category');
Route::get('/docs/{slug}', [DocumentationPublicController::class, 'show'])->name('docs.public.show');
Route::get('/docs/{slug}/download/{versionId}', [DocumentationPublicController::class, 'download'])
    ->middleware('throttle:30,1')
    ->name('docs.public.download');

require __DIR__.'/auth.php';
