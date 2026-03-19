<?php

use App\Http\Controllers\Lab\LabController;
use Illuminate\Support\Facades\Route;

/* Laboratory Routes */

Route::group([
    'prefix' => 'lab',
    'as' => 'lab.',
    'middleware' => [
        'auth:sanctum',
        config('jetstream.auth_session'),
        'is_verify_email',     // optional Jetstream verify
        'is.lab',       // role check LAST
    ],
], function () {

    Route::get('/dashboard', [LabController::class, 'dashboard'])->name('dashboard');
    Route::resource('/labs', LabController::class);

    // Lab Tests
    Route::get('/tests', [LabController::class, 'tests'])->name('tests.index');

    // Lab Reports
    Route::get('/reports', [LabController::class, 'reports'])->name('reports.index');

    // Transactions
    Route::get('/transactions', [LabController::class, 'transactions'])->name('transactions.index');

    // Messages
    Route::get('/messages', [LabController::class, 'messages'])->name('messages.index');
});
