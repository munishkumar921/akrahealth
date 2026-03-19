<?php

use App\Http\Controllers\Pharmacy\PharmacyController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum', 'is.pharmacy', config('jetstream.auth_session'),
    'is_verify_email',
], 'prefix' => 'pharmacy', 'as' => 'pharmacy.'], function () {
    Route::get('/dashboard', [PharmacyController::class, 'dashboard'])->name('dashboard');

    // Medicines
    Route::get('/medicines', [PharmacyController::class, 'medicines'])->name('medicines.index');

    // Orders
    Route::get('/orders', [PharmacyController::class, 'orders'])->name('orders.index');

    // Reports
    Route::get('/reports', [PharmacyController::class, 'reports'])->name('reports.index');

    // Transactions
    Route::get('/transactions', [PharmacyController::class, 'transactions'])->name('transactions.index');
    Route::post('/transactions', [PharmacyController::class, 'storeTransaction'])->name('transactions.store');

    // Prescriptions
    Route::get('/prescriptions', [PharmacyController::class, 'prescriptions'])->name('prescriptions.index');

});
