<?php

use App\Http\Controllers\Biller\BillerController;
use App\Http\Controllers\Common\FinancialController;
use App\Http\Controllers\Doctor\DInsuranceController;
use App\Http\Controllers\Patient\PatientController;
use Illuminate\Support\Facades\Route;

/* Biller Routes */

Route::group(['middleware' => ['auth:sanctum', 'is.biller', config('jetstream.auth_session'), 'is_verify_email'], 'prefix' => 'biller', 'as' => 'biller.',
], function () {

    Route::get('/dashboard', [BillerController::class, 'dashboard'])->name('dashboard');

    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/bills_to_submit', [FinancialController::class, 'bills_to_submit'])->name('bills_to_submit');
        Route::get('/processed_bills', [FinancialController::class, 'processed_bills'])->name('processed_bills');
        Route::get('/outstanding_balances', [FinancialController::class, 'outstanding_balances'])->name('outstanding_balances');
        Route::get('/monthly_financial_report', [FinancialController::class, 'monthly_financial_report'])->name('monthly_financial_report');
        Route::get('/yearly_financial_report', [FinancialController::class, 'yearly_financial_report'])->name('yearly_financial_report');
        Route::get('financial_resubmit/{id}', [FinancialController::class, 'financial_resubmit'])->name('financial_resubmit');
        Route::post('financial_queue', [FinancialController::class, 'financial_queue'])->name('financial_queue');
        Route::get('/financial_queue/report/print', [FinancialController::class, 'financial_queue_print'])->name('financial_queue_report_print');
        Route::get('financial_insurance', [FinancialController::class, 'financial_insurance'])->name('financial_insurance');
        Route::get('/financial_cutom_report_by_payment', [FinancialController::class, 'custom_report_payment'])->name('financial_cutom_report_by_payment');
        Route::get('/financial_cutom_report_by_procedure', [FinancialController::class, 'custom_report_procedure'])->name('financial_cutom_report_by_procedure');
        Route::post('/financial_cutom_report_download', [FinancialController::class, 'download'])->name('download');

    });
    Route::resource('/insurance', DInsuranceController::class);

    Route::get('/billing', [PatientController::class, 'billing'])->name('billing');

});
