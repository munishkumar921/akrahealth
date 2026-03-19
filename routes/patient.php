<?php

use App\Http\Controllers\Patient\AppointmentController as PatientAppointmentController;
use App\Http\Controllers\Patient\DocumentController;
use App\Http\Controllers\Patient\FormsController;
use App\Http\Controllers\Patient\HistoryController;
use App\Http\Controllers\Patient\PatientController;
use App\Http\Controllers\Patient\PaymentController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum', 'is.patient', config('jetstream.auth_session'), 'is_verify_email'], 'prefix' => 'patient', 'as' => 'patient.',
], function () {

    Route::get('/dashboard', [PatientController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [PatientController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [PatientController::class, 'updateProfile'])->name('profile.update');
    Route::get('/summary', [PatientController::class, 'summary'])->name('summary');

    Route::get('/book-appointment', [PatientAppointmentController::class, 'bookAppointment'])->name('book.appointment');
    Route::get('/doctorslist/{speciality?}', [PatientAppointmentController::class, 'doctorsList'])->name('doctors.list');
    Route::get('/doctorvisit-types/{doctorId}', [PatientAppointmentController::class, 'getDoctorVisitTypes'])->name('doctor.visit.types');
    Route::get('/doctor-visit-duraction/{type}/{doctorId}', [PatientAppointmentController::class, 'getDoctorVisitDuraction'])->name('doctor.visit.duration');
    Route::post('/doctors-slots', [PatientAppointmentController::class, 'getDoctorsSlot'])->name('doctors.slots');
    Route::post('/store-appointment', [PatientAppointmentController::class, 'storeAppointment'])->name('store.appointment');
    Route::post('/booked-appointments', [PatientAppointmentController::class, 'bookedAppointments'])->name('booked.appointments');
    Route::post('/provider-exceptions', [PatientAppointmentController::class, 'providerExceptions'])->name('provider.exceptions');
    Route::get('/appointment/payment/{id}', [PaymentController::class, 'showPaymentPage'])->name('appointment.payment');
    Route::post('/create-order', [PaymentController::class, 'createRazorpayOrder'])->name('create.order');

    Route::post('/appointment/verify-payment', [PaymentController::class, 'verifyPayment'])->name('verify.payment');
    Route::get('/demographics', [PatientController::class, 'demographics'])->name('demographics');
    Route::post('/demographics/update', [PatientController::class, 'updateDemographics'])->name('demographics.update');

    Route::get('/conditions', [PatientController::class, 'conditions'])->name('conditions');
    Route::get('/move-condition/{id}/{type}', [PatientController::class, 'moveCondition'])->name('move.condition');
    Route::get('/condition-status/{id}/{type}', [PatientController::class, 'conditionStatus'])->name('condition.status');
    Route::get('/medications', [PatientController::class, 'medications'])->name('medications');
    Route::get('/medication-status/{id}/{type}', [PatientController::class, 'medicationStatus'])->name('medication.status');

    Route::get('/supplements', [PatientController::class, 'supplements'])->name('supplements');
    Route::get('/immunizations', [PatientController::class, 'immunizations'])->name('immunizations');
    Route::get('/allergies', [PatientController::class, 'allergies'])->name('allergies');
    Route::get('/orders', [PatientController::class, 'orders'])->name('orders');
    Route::get('/orders/{id}', [PatientController::class, 'orderShow'])->name('orders.show');
    Route::get('/encounters', [PatientController::class, 'encounters'])->name('encounters');
    Route::get('/encounters/{id}', [PatientController::class, 'encounterShow'])->name('encounters.show');

    Route::get('/documents', [DocumentController::class, 'index'])->name('documents');
    Route::get('/results', [PatientController::class, 'results'])->name('results');
    Route::get('/results/{id}', [PatientController::class, 'show'])->name('results.show');
    Route::get('/billing', [PatientController::class, 'billing'])->name('billing');
    Route::get('/print-invoice/{id}', [PatientController::class, 'print'])->name('billing.print');
    Route::get('billing_payment_history/{id}', [PatientController::class, 'billing_payment_history'])->name('billing_payment_history');
    Route::get('/billing_payment/{id}', [PatientController::class, 'billing_print'])->name('billing.print');
    Route::get('/social-history', [PatientController::class, 'SocialHistory'])->name('social-history');
    Route::post('/social-history', [PatientController::class, 'storeSocialHistory'])->name('social-history.store');
    Route::get('/family-history', [PatientController::class, 'FamilyHistory'])->name('family-history');
    Route::get('/history', [HistoryController::class, 'index'])->name('history');
    Route::get('/providers', [PatientController::class, 'providers'])->name('providers');
    Route::get('/messages', [PatientController::class, 'Messages'])->name('messages');
    Route::get('/messages/{id}', [PatientController::class, 'messageShow'])->name('messages.show');
    Route::get('/booking-list', [PatientController::class, 'bookingList'])->name('booking.list');
    Route::post('/patient/share-details', [PatientController::class, 'shareDetails'])->name('share.details');
    Route::get('/live-consultation/{id}', [PatientController::class, 'liveConsultation'])->name('live.consultation');
    Route::delete('/remove-doctor-access/{doctorId}', [PatientController::class, 'removeDoctorAccess'])->name('remove.doctor.access');

    // forms
    Route::get('/forms', [FormsController::class, 'index'])->name('forms');
    Route::get('/form/complete/{id}', [FormsController::class, 'formcomplete'])->name('form.completeform');
    Route::get('/form/edit/{id}/{type?}', [FormsController::class, 'edit'])->name('form.edit');
    Route::get('/form/{id}/{type?}', [FormsController::class, 'show'])->name('form.show');
    Route::post('/form/show', [FormsController::class, 'store'])->name('form.show.store');
});
