<?php

use App\Http\Controllers\Doctor\DFamilyHistoryController;
use App\Http\Controllers\Doctor\DSocialHistoryController;
use App\Http\Controllers\VirtualAssistant\VirtualAssistantController;
use Illuminate\Support\Facades\Route;

/* Virtual Assistant Routes */
Route::group(['middleware' => ['auth:sanctum', 'is.virtual.assistant', config('jetstream.auth_session'), 'is_verify_email'], 'prefix' => 'assistant', 'as' => 'assistant.'], function () {
    Route::get('/dashboard', [VirtualAssistantController::class, 'dashboard'])->name('dashboard');
    Route::get('/search-patient', [VirtualAssistantController::class, 'searchPatient'])->name('search.patient');
    Route::get('/select-patient/{id}', [VirtualAssistantController::class, 'selectPatient'])->name('select.patient');
    Route::get('/patient/summary', [VirtualAssistantController::class, 'patientSummary'])->name('patient.summary');
    Route::get('/patients', [VirtualAssistantController::class, 'patients'])->name('patients');
    Route::post('/patient', [VirtualAssistantController::class, 'storePatient'])->name('patient.store');
    Route::post('/patient/register/', [VirtualAssistantController::class, 'registerPatient'])->name('patient.register');

    // Patient Details routes
    Route::get('/patient/demographics', [VirtualAssistantController::class, 'demographics'])->name('demographics');
    Route::get('/patient/history', [VirtualAssistantController::class, 'patientHistory'])->name('patient.history');
    Route::get('/conditions', [VirtualAssistantController::class, 'conditions'])->name('conditions.index');
    Route::get('/medications', [VirtualAssistantController::class, 'medications'])->name('medications.index');
    Route::get('/supplements', [VirtualAssistantController::class, 'supplements'])->name('supplements.index');
    Route::get('/immunizations', [VirtualAssistantController::class, 'immunizations'])->name('immunizations.index');
    Route::get('/allergies', [VirtualAssistantController::class, 'allergies'])->name('allergies.index');
    Route::get('/documents', [VirtualAssistantController::class, 'documents'])->name('documents.index');
    Route::get('/results', [VirtualAssistantController::class, 'results'])->name('results.index');
    Route::get('/forms', [VirtualAssistantController::class, 'forms'])->name('forms.index');
    Route::get('/orders', [VirtualAssistantController::class, 'orders'])->name('orders.index');
    Route::get('/billing', [VirtualAssistantController::class, 'billing'])->name('billing.index');
    Route::get('/insurance', [VirtualAssistantController::class, 'insurance'])->name('insurance.index');
    Route::get('/coordination', [VirtualAssistantController::class, 'coordination'])->name('coordination.index');
    Route::resource('/schedule', \App\Http\Controllers\Common\ScheduleController::class);
    Route::get('/finance/bills_to_submit', [VirtualAssistantController::class, 'financeBillsToSubmit'])->name('finance.bills_to_submit');
    Route::resource('/encounters', \App\Http\Controllers\Doctor\DEncountersController::class);
    Route::get('/todays-call', [\App\Http\Controllers\Doctor\CallController::class, 'todaysCall'])->name('todays.call');
    Route::resource('/socialHistory', DSocialHistoryController::class);
    Route::resource('/family-history', DFamilyHistoryController::class);

});
