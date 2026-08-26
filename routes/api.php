<?php

use App\Http\Controllers\Api\VersionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    Route::get('/versions/{slug}/latest', [VersionController::class, 'latest'])->name('api.versions.latest');
    Route::get('/versions/{slug}', [VersionController::class, 'index'])->name('api.versions.index');
});
