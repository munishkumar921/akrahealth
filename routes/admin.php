<?php

use App\Http\Controllers\Admin\AAuditController;
use App\Http\Controllers\Admin\AChartsController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminSupplementController;
use App\Http\Controllers\Admin\AdminVaccineController;
use App\Http\Controllers\Admin\ADoctorController;
use App\Http\Controllers\Admin\AHospitalController;
use App\Http\Controllers\Admin\ALabController;
use App\Http\Controllers\Admin\ALabTestCategoryController;
use App\Http\Controllers\Admin\ALabTestController;
use App\Http\Controllers\Admin\AManageController;
use App\Http\Controllers\Admin\AMedicineController;
use App\Http\Controllers\Admin\APatientController;
use App\Http\Controllers\Admin\APharmacyController;
use App\Http\Controllers\Admin\APrescriptionController;
use App\Http\Controllers\Admin\ASettingController;
use App\Http\Controllers\Admin\ASpecialityController;
use App\Http\Controllers\Admin\AUserController;
use App\Http\Controllers\Admin\AVisitTypeController;
use App\Http\Controllers\Admin\CallLogController;
use App\Http\Controllers\Admin\ProviderExceptionController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\AjaxSearchController;
use App\Http\Controllers\Common\ScheduleController;
use App\Http\Controllers\SubscriptionNotificationController;
use Illuminate\Support\Facades\Route;

/* Admin Routes */

Route::group(['middleware' => ['auth:sanctum', 'is.admin', config('jetstream.auth_session'), 'is_verify_email', 'check.subscription'], 'prefix' => 'admin', 'as' => 'admin.',
], function () {

    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
    Route::get('/subscription/notify', [SubscriptionNotificationController::class, 'notify'])->name('subscription.notify');

    Route::resource('/admins', AdminController::class);
    Route::resource('/doctors', ADoctorController::class);
    Route::resource('/patients', APatientController::class);
    Route::resource('/labs', ALabController::class);
    Route::resource('/pharmacies', APharmacyController::class);
    Route::resource('/specialities', ASpecialityController::class);
    Route::resource('/medicines', AMedicineController::class);
    Route::resource('/lab-test-categories', ALabTestCategoryController::class);
    Route::resource('/lab-tests', ALabTestController::class);

    Route::resource('/prescriptions', APrescriptionController::class);
    Route::resource('/visit-types', AVisitTypeController::class);

    // Practice and locations (hospitals)
    Route::resource('/hospitals', AHospitalController::class);
    Route::get('/branches', [AHospitalController::class, 'branchList'])->name('branches');
    Route::get('/branches/get', [AHospitalController::class, 'getBranches'])->name('getBranches');
    Route::get('/settings/practice', [AHospitalController::class, 'index'])->name('settings.practice.list');

    // Schedule Setup (Hospital Timing)
    Route::get('/settings/schedule-setup', [AHospitalController::class, 'hospitalTiming'])->name('hospital-timing');
    Route::post('/settings/schedule-setup', [AHospitalController::class, 'hospitalTimingStore'])->name('hospital-timing.store');
    Route::delete('/settings/schedule-setup/{id}', [AHospitalController::class, 'hospitalTimingDestroy'])->name('hospital-timing.destroy');

    // Specific settings routes MUST be defined BEFORE resource routes
    Route::get('/settings/all-appointments', [AdminController::class, 'allAppointments'])->name('allAppointments');
    Route::get('/settings/roles-permissions', [AdminController::class, 'rolespermission'])->name('rolesandpermission');

    // Schedule resource (after specific routes)
    Route::resource('/schedule', ScheduleController::class);

    // API routes for Roles & Permissions management
    Route::prefix('api/roles')->group(function () {
        Route::get('/', [AdminController::class, 'apiRolesList'])->name('api.roles.list');
        Route::post('/', [AdminController::class, 'apiRolesStore'])->name('api.roles.store');
        Route::delete('/{id}', [AdminController::class, 'apiRolesDestroy'])->name('api.roles.destroy');
        Route::post('/toggle/{id}', [AdminController::class, 'apiRolesToggle'])->name('api.roles.toggle');
    });

    // API route for quick appointments (calendar dropdown)
    Route::get('/api/quick-appointments', [AdminController::class, 'quickAppointments'])->name('api.quickAppointments');

    // Bank Account Management (replaced Razorpay e-payment settings)
    Route::get('/settings/bank-accounts', [AdminController::class, 'bankAccounts'])->name('bank-accounts');
    Route::get('/settings/epayment', [AdminController::class, 'bankAccounts'])->name('epayment');
    Route::post('/settings/bank-accounts', [AdminController::class, 'storeBankAccount'])->name('bank-accounts.store');
    Route::put('/settings/bank-accounts/{id}', [AdminController::class, 'updateBankAccount'])->name('bank-accounts.update');
    Route::delete('/settings/bank-accounts/{id}', [AdminController::class, 'destroyBankAccount'])->name('bank-accounts.destroy');
    Route::put('/settings/bank-accounts/{id}/primary', [AdminController::class, 'setPrimaryBankAccount'])->name('bank-accounts.primary');

    Route::get('/settings/notification', [AdminController::class, 'notification'])->name('notification');

    // Settings resource (after specific routes to avoid conflicts)
    Route::resource('/settings', SettingController::class);
    Route::resource('/subscription-plans', SubscriptionPlanController::class);
    Route::get('/task', [AdminController::class, 'task'])->name('task');
    Route::get('/setup', [AdminController::class, 'setup'])->name('setup');
    Route::get('/products', [AdminController::class, 'products'])->name('products');
    Route::get('/manage/specialities', [AdminController::class, 'specalitiesList'])->name('specialities.list');

    // Inventory Routes - Supplements & Vaccines
    Route::get('/supplements', [AdminSupplementController::class, 'index'])->name('supplements.index');
    Route::post('/supplements', [AdminSupplementController::class, 'store'])->name('supplements.store');
    Route::delete('/supplements/{id}', [AdminSupplementController::class, 'destroy'])->name('supplements.destroy');
    Route::get('/vaccines', [AdminVaccineController::class, 'index'])->name('vaccines.index');
    Route::post('/vaccines', [AdminVaccineController::class, 'store'])->name('vaccines.store');
    Route::delete('/vaccines/{id}', [AdminVaccineController::class, 'destroy'])->name('vaccines.destroy');
    Route::get('/vaccines/temperature', [AdminVaccineController::class, 'temperatureIndex'])->name('vaccines.temperature.index');
    Route::post('/vaccines/temperature', [AdminVaccineController::class, 'temperatureStore'])->name('vaccine.temperatures.store');
    Route::delete('/vaccines/temperature/{id}', [AdminVaccineController::class, 'temperatureDestroy'])->name('vaccines.temperature.destroy');
    Route::post('/charts', [AChartsController::class, 'charts'])->name('generateCharts');
    // Search Routes for Vaccine Modal
    Route::any('/search-cpt', [AjaxSearchController::class, 'searchCPT'])->name('search.cpt');
    Route::any('/search-immunization', [AjaxSearchController::class, 'searchImmunization'])->name('search.immunization');
    Route::any('/search-vaccine', [AdminVaccineController::class, 'search'])->name('search.vaccine');

    // Forms Routes
    Route::get('/forms', fn () => \Inertia\Inertia::render('Admin/Forms/Index'))->name('forms.index');
    Route::get('/forms/create', fn () => \Inertia\Inertia::render('Admin/Forms/Create'))->name('forms.create');

    // My Reports Route
    Route::get('/my-reports', fn () => \Inertia\Inertia::render('Admin/Reports/MyReports'))->name('myReports');

    Route::get('/financial/transactions', [AdminController::class, 'transactionlist'])->name('transaction.list');

    // New Invoice Management Routes
    Route::resource('/invoices', \App\Http\Controllers\InvoiceController::class);
    Route::post('/invoices/{id}/send', [\App\Http\Controllers\InvoiceController::class, 'send'])->name('invoices.send');
    Route::post('/invoices/{id}/reminder', [\App\Http\Controllers\InvoiceController::class, 'sendReminder'])->name('invoices.reminder');
    Route::post('/invoices/{id}/cancel', [\App\Http\Controllers\InvoiceController::class, 'cancel'])->name('invoices.cancel');
    Route::post('/invoices/{id}/payment', [\App\Http\Controllers\InvoiceController::class, 'processPayment'])->name('invoices.payment');
    Route::get('/invoices/{id}/download', [\App\Http\Controllers\InvoiceController::class, 'downloadPdf'])->name('invoices.download');

    // Existing invoice routes (for backward compatibility)
    Route::get('/financial/invoices', [AdminController::class, 'invoicesList'])->name('invoice.list');
    Route::get('/financial/invoices/print/{id}', [AdminController::class, 'print'])->name('invoice.print');
    Route::get('/financial/invoices/download/{id}', [AdminController::class, 'downloadInvoice'])->name('invoice.download');
    Route::get('/order-management/lab-orders', [AdminController::class, 'labOrdersList'])->name('lab-orders.list');
    Route::get('/order-management/pharmacy-orders', [AdminController::class, 'pharmacyOrdersList'])->name('pharmacy-orders.list');

    Route::resource('/provider-exception', ProviderExceptionController::class);

    Route::post('/provider-exception/{id}/toggle', [ProviderExceptionController::class, 'toggleStatus'])->name('provider-exception.toggle');

    Route::get('/reports/pharmacy-reports', [AdminController::class, 'pharmacyReports'])->name('pharmacyReports');
    Route::get('/reports/lab-reports', [AdminController::class, 'labReports'])->name('labReports');
    Route::get('/reports/CCDA-reports', [AdminController::class, 'CCDAReports'])->name('CCDAReports');
    Route::get('/reports/chart-reports', [AdminController::class, 'chartReports'])->name('chartReports');
    Route::get('/log/call-logs', [CallLogController::class, 'index'])->name('callLogs.list');
    Route::get('/messages', [AdminController::class, 'messages'])->name('messages');

    Route::resource('/users', AUserController::class);
    Route::resource('/manage', AManageController::class);
    Route::get('/manage/speciality/create', fn () => \Inertia\Inertia::render('Admin/Manage/SpecialityCreate'))->name('manage.speciality.create');
    Route::get('/appointment/exception/create', fn () => \Inertia\Inertia::render('Admin/Appointment/ExceptionCreate'))->name('appointment.exception.create');
    Route::resource('/setting', ASettingController::class);
    Route::get('/setting/all-appointment/schedule-setup/create', fn () => \Inertia\Inertia::render('Admin/Setting/ScheduleSetupCreate'))->name('setting.ScheduleSetup.create');
    Route::get('/setting/general/create', fn () => \Inertia\Inertia::render('Admin/Setting/GeneralCreate'))->name('setting.general.create');
    Route::get('/setting/appointment/create', fn () => \Inertia\Inertia::render('Admin/Setting/AppointmentCreate'))->name('setting.appointment.create');
    Route::get('/setting/appointment/schedule-setup/create', fn () => \Inertia\Inertia::render('Admin/Setting/SchedulSetupCreate'))->name('setting.appointment.schedulesetup.create');

    // Audit Logs
    Route::get('/audit-logs', [AAuditController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/{id}', [AAuditController::class, 'show'])->name('audit-logs.show');
    Route::get('/audit-logs/export/csv', [AAuditController::class, 'exportCsv'])->name('audit-logs.export.csv');
    Route::get('/audit-logs/export/pdf', [AAuditController::class, 'exportPdf'])->name('audit-logs.export.pdf');

    /* All Subscriptions */
    Route::get('/subscription', [AdminController::class, 'subscription'])->name('subscription');

    Route::get('/subscriptions/history', [AdminController::class, 'SubscriptionsHistory'])->name('subscriptions.history');

});
