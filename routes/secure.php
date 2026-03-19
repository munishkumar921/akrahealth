<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\StatusController;
use Illuminate\Support\Facades\Route;

/* Common secure Routes */
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'is_verify_email'])->group(function () {
    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
    Route::post('/update-status', [StatusController::class, 'updateStatus'])->name('update.status');
});
