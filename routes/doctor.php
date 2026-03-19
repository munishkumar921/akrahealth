<?php

use App\Http\Controllers\AjaxSearchController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Common\ConfigurationController;
use App\Http\Controllers\Common\FinancialController;
use App\Http\Controllers\Common\ReportsController;
use App\Http\Controllers\Common\ScheduleController;
use App\Http\Controllers\Common\TriageController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Doctor\BillingCodeController;
use App\Http\Controllers\Doctor\CallController;
use App\Http\Controllers\Doctor\DAlertsController;
use App\Http\Controllers\Doctor\DAllergyController;
use App\Http\Controllers\Doctor\DBillingController;
use App\Http\Controllers\Doctor\DCardiopulmonaryController;
use App\Http\Controllers\Doctor\DConditionController;
use App\Http\Controllers\Doctor\DCoordinationController;
use App\Http\Controllers\Doctor\DDocumentsController;
use App\Http\Controllers\Doctor\DEducationController;
use App\Http\Controllers\Doctor\DEncountersController;
use App\Http\Controllers\Doctor\DFamilyHistoryController;
use App\Http\Controllers\Doctor\DFormsController;
use App\Http\Controllers\Doctor\DImmunizationController;
use App\Http\Controllers\Doctor\DInsuranceController;
use App\Http\Controllers\Doctor\DLabController;
use App\Http\Controllers\Doctor\DMedicationController;
use App\Http\Controllers\Doctor\DMessagesController;
use App\Http\Controllers\Doctor\DoctorController;
use App\Http\Controllers\Doctor\DocumentController;
use App\Http\Controllers\Doctor\DOrdersController;
use App\Http\Controllers\Doctor\DPatientController;
use App\Http\Controllers\Doctor\DPatientMessageController;
use App\Http\Controllers\Doctor\DRadiologyController;
use App\Http\Controllers\Doctor\DResultsController;
use App\Http\Controllers\Doctor\DSocialHistoryController;
use App\Http\Controllers\Doctor\DSupplementController;
use App\Http\Controllers\Doctor\EncounterOrderController;
use App\Http\Controllers\Doctor\PatientSupplementController;
use App\Http\Controllers\Doctor\PrescriptionController;
use App\Http\Controllers\Doctor\ProcedureController;
use App\Http\Controllers\Doctor\ReferralController;
use App\Http\Controllers\LivekitController;
use App\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;

/* Doctor Routes */

Route::group(['middleware' => [
    'auth:sanctum',
    'is.doctor',
    config('jetstream.auth_session'),
    'is_verify_email', 'check.subscription',
], 'prefix' => 'doctor', 'as' => 'doctor.'], function () {

    Route::get('todays-call', [CallController::class, 'todaysCall'])->name('todays.call');
    Route::post('call/encounter/', [CallController::class, 'encounterStore'])->name('call.encounter.store');
    Route::post('call/notify', [CallController::class, 'notifyPatient'])->name('call.notify');
    Route::get('/livekit/generateToken/{id}', [LivekitController::class, 'generateToken'])->name('generateToken');
    Route::post('/saveSoapNotes/{id}', [CallController::class, 'saveSoapNotes'])->name('saveSoapNotes');
    Route::resource('/triage', TriageController::class);

    Route::get('/dashboard', [DoctorController::class, 'dashboard'])->name('dashboard');
    Route::get('/search-patient', [DoctorController::class, 'searchPatient'])->name('search.patient');
    Route::get('/search-recipients', [DoctorController::class, 'searchRecipients'])->name('search.recipients');
    Route::get('/select-patient/{id}', [DoctorController::class, 'selectPatient'])->name('select.patient');
    Route::get('/selected-patient', [DoctorController::class, 'getSelectedPatient'])->name('selected.patient');

    Route::get('/profile', [DoctorController::class, 'profile'])->name('profile');
    Route::post('/profile', [DoctorController::class, 'store'])->name('profile.update');

    Route::get('/patient/demographics', [DPatientController::class, 'Demographics'])->name('demographics');
    Route::post('/patient', [DPatientController::class, 'store'])->name('patient.store');
    Route::put('/patient/{id}', [DPatientController::class, 'update'])->name('patient.update');
    Route::get('/patient/history', [DPatientController::class, 'patientHistory'])->name('patient.history');
    Route::post('/patient-list', [DPatientController::class, 'patientList'])->name('patient.list');
    Route::post('/patient/demographics/update', [DPatientController::class, 'updateDemographics'])->name('demographics.update');
    Route::get('/patients', [DPatientController::class, 'index'])->name('patients');
    Route::post('/patient/register/', [DPatientController::class, 'registerPatient'])->name('patient.register');
    Route::get('/summary', [DPatientController::class, 'patientSummary'])->name('patient.summary');

    Route::resource('/conditions', DConditionController::class);
    Route::get('/move-condition/{id}/{type}', [DConditionController::class, 'moveCondition'])->name('move.condition');
    Route::get('/condition-status/{id}/{type}', [DConditionController::class, 'conditionStatus'])->name('condition.status');
    Route::resource('/medications', DMedicationController::class);
    Route::get('/medication-status/{id}/{type}', [DMedicationController::class, 'medicationStatus'])->name('medication.status');
    Route::post('/medication-reconcile/{id}', [DMedicationController::class, 'reconcileMedication'])->name('medication.reconcile');
    Route::resource('/supplements', DSupplementController::class);
    Route::get('/supplement-status/{id}/{type}', [DSupplementController::class, 'supplementStatus'])->name('supplement.status');
    Route::resource('/immunizations', DImmunizationController::class);
    Route::get('/immunizations-status/{id}/{type}', [DImmunizationController::class, 'immunizationStatus'])->name('immunization.status');
    Route::resource('/allergies', DAllergyController::class);
    Route::get('/allergy-status/{id}/{type}', [DAllergyController::class, 'allergyStatus'])->name('allergy.status');
    Route::resource('/alerts', DAlertsController::class);
    Route::post('/alert-status', [DAlertsController::class, 'alertStatus'])->name('alert.status');
    Route::resource('/orders', DOrdersController::class);
    Route::post('/orders/{id}/complete', [DOrdersController::class, 'complete'])->name('orders.complete');

    Route::resource('/encounters', DEncountersController::class);
    Route::get('/documents/generateLetter', [DDocumentsController::class, 'generateLetter'])->name('document.generateLetter');
    Route::post('/documents/storeLetter', [DDocumentsController::class, 'storeLetter'])->name('documents.storeLetter');
    Route::get('/documents/templates', [DDocumentsController::class, 'getLetterTemplates'])->name('documents.templates');
    Route::get('/documents/patient-info', [DDocumentsController::class, 'getPatientInfo'])->name('documents.patientInfo');
    Route::get('/documents/doctor-info', [DDocumentsController::class, 'getDoctorInfo'])->name('documents.doctorInfo');
    Route::get('/documents/encounter-info', [DDocumentsController::class, 'getEncounterInfo'])->name('documents.encounterInfo');
    Route::post('/documents/preview', [DDocumentsController::class, 'previewLetter'])->name('documents.preview');
    Route::post('/documents/download-pdf', [DDocumentsController::class, 'downloadLetterPdf'])->name('documents.downloadPdf');
    Route::get('/upload_ccda_view/{id}/{type}', [DDocumentsController::class, 'uploadCcdaView'])->name('documents.uploadCcdaView');
    Route::post('/set_ccda_data', [DDocumentsController::class, 'setCcdaData'])->name('documents.setCcdaData');
    Route::post('/bulk_import_ccda', [DDocumentsController::class, 'bulkImportCcda'])->name('documents.bulkImportCcda');
    Route::get('/compare_ccda_records/{id}/{type}', [DDocumentsController::class, 'compareCcdaRecords'])->name('documents.compareCcdaRecords');
    Route::get('/documents-by-category/{type?}', [DDocumentsController::class, 'getDocumentsByCategory'])->name('documents.byCategory');
    Route::get('/documents/{filename}', [DDocumentsController::class, 'serveLetter'])->name('documents.url');
    Route::resource('/documents', DDocumentsController::class);

    // Patient Education Routes
    Route::post('/education/search', [DEducationController::class, 'search'])->name('education.search');
    Route::get('/education/types', [DEducationController::class, 'getTypes'])->name('education.types');
    Route::get('/education/popular', [DEducationController::class, 'getPopularTopics'])->name('education.popular');
    Route::post('/education/store', [DEducationController::class, 'store'])->name('education.store');
    Route::get('/education/download', [DEducationController::class, 'download'])->name('education.download');
    Route::post('/education/healthwise_view', [DEducationController::class, 'healthwise_view'])->name('education.healthwise_view');

    Route::resource('/coordination', DCoordinationController::class);
    Route::resource('/results', DResultsController::class);
    Route::post('/results-reply', [DResultsController::class, 'Resultreply'])->name('results.reply');
    Route::get('/doctor/results-chart/{result}', [DResultsController::class, 'resultsChart'])->name('resultsChart');
    Route::get('/vitals/history/{patient}/{vital}', [DResultsController::class, 'getVitalsHistory'])->name('vitals.history');
    Route::get('/doctor/encounterVitalChat', [DResultsController::class, 'encounterVitalChat'])->name('encounterVitalChat');
    Route::resource('/messages', DMessagesController::class);
    Route::resource('/t_messages', DPatientMessageController::class);
    Route::resource('/forms', DFormsController::class);
    Route::get('/form/complete/{id}', [DFormsController::class, 'complete'])->name('form.completeform');
    Route::get('/form/{id}/{type?}', [DFormsController::class, 'show'])->name('form.show');
    Route::get('/form/edit/{id}/{type?}', [DFormsController::class, 'formEdit'])->name('form.edit');
    Route::post('/form/submit', [DFormsController::class, 'formSubmit'])->name('form.submit');

    Route::resource('/labs', DLabController::class);
    Route::resource('/radiologies', DRadiologyController::class);
    Route::resource('/cardiopulmonary', DCardiopulmonaryController::class);
    Route::resource('/socialHistory', DSocialHistoryController::class);
    // Route::post('/patient/social-history/store', [DSocialHistoryController::class, 'store'])->name('social-history');
    Route::resource('/family-history', DFamilyHistoryController::class);

    Route::resource('/billing', DBillingController::class);
    Route::get('billing_payment_history/{id}', [DBillingController::class, 'billing_payment_history'])->name('billing_payment_history');
    Route::delete('billing_payment_delete/{id}', [DBillingController::class, 'delete'])->name('billing_payment_delete');
    Route::post('billing_make_payment', [DBillingController::class, 'store'])->name('billing_make_payment.store');
    Route::post('billing.notes.update', [DBillingController::class, 'billing_notes_update'])->name('billing.notes.update');
    Route::resource('/insurance', DInsuranceController::class);
    Route::get('/office/reports', [ReportsController::class, 'index'])->name('reports');
    Route::resource('/schedule', ScheduleController::class);
    Route::post('/schedule/encounter', [ScheduleController::class, 'encounter'])->name('schedule.encounter.create');
    Route::get('printimage_single/{id}', [Controller::class, 'printimage_single'])->name('printimage_single');
    Route::get('generate_hcfa/{type}/{id}', [Controller::class, 'generate_hcfa'])->name('generate_hcfa');

    // Finance
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

    Route::prefix('configure')->name('configure.')->group(function () {
        Route::get('/address_book', [ConfigurationController::class, 'address_book'])->name('address_book');
        Route::post('/configure/address_book', [ConfigurationController::class, 'addressStore'])->name('addressBook.store');
        Route::get('/my_forms', [ConfigurationController::class, 'my_forms'])->name('my_forms');
        Route::get('/provider_exceptions', [ConfigurationController::class, 'provider_exceptions'])->name('provider_exceptions');
        Route::get('/schedule_setup', [ConfigurationController::class, 'schedule_setup'])->name('schedule_setup');
        Route::get('/visit_type', [ConfigurationController::class, 'visit_type'])->name('visit_type');
    });

    Route::post('/get-templates', [TemplateController::class, 'getTemplate'])->name('get.templates');
    Route::post('/update-template', [TemplateController::class, 'updateTemplate'])->name('update.template');
    Route::post('/delete-template', [TemplateController::class, 'deleteTemplate'])->name('delete.template');
    Route::post('/template-data', [TemplateController::class, 'templateData'])->name('template.data');

    Route::post('/icd-search/{assessment?}', [TemplateController::class, 'ICDSearch'])->name('icd.search');
    Route::post('/search_icd_specific', [TemplateController::class, 'searchICDSpecific'])->name('search.icd.specific');
    Route::get('/add-encounter-assessment/{type}/{id}/{encounter_id?}', [DEncountersController::class, 'addEncounterAssessment'])
        ->name('add.encounter.assessment');
    Route::get('/delete-encounter-assessment/{id}/{encounter_id?}', [DEncountersController::class, 'deleteEncounterAssessment'])
        ->name('delete.encounter.assessment');
    Route::post('/update-encounter-assessment/{id}', [DEncountersController::class, 'updateEncounterAssessment'])
        ->name('update.encounter.assessment');

    Route::any('/search-cpt', [AjaxSearchController::class, 'searchCPT'])->name('search.cpt');
    Route::any('/search-supplement/{order?}', [AjaxSearchController::class, 'searchSupplement'])->name('search.supplement');
    Route::post('/search-loinc', [AjaxSearchController::class, 'searchLoinc'])->name('search.loinc');
    Route::post('/search-imaging', [AjaxSearchController::class, 'searchImaging'])->name('search.imaging');
    Route::post('/search-rx', [AjaxSearchController::class, 'searchRx'])->name('search.rx');

    /* prescriptions */
    Route::post('/upsert-prescription', [PrescriptionController::class, 'upsert'])->name('upsert.prescription');
    Route::get('/get-prescriptions/{encounter_id}', [PrescriptionController::class, 'getPrescription'])->name('get.prescriptions');
    Route::post('/delete-prescription/{id}', [PrescriptionController::class, 'delete'])->name('delete.prescription');
    Route::get('/prescription/show/{id}', [PrescriptionController::class, 'show'])->name('prescription.show');
    // routes/web.php
    Route::get('/prescription/{id}', [PrescriptionController::class, 'downloadPrescriptionPdf'])
        ->name('downloadPrescriptionPdf');

    /* supplements */
    Route::post('/upsert-supplement', [PatientSupplementController::class, 'upsert'])->name('upsert.supplement');
    Route::get('/get-supplement/{id}', [PatientSupplementController::class, 'getSupplements'])->name('get.supplement');
    Route::post('/delete-supplement/{id}', [PatientSupplementController::class, 'delete'])->name('delete.supplement');

    /* encounter order */
    Route::post('/upsert-encounter-order', [EncounterOrderController::class, 'upsert'])->name('upsert.encounter.order');
    Route::get('/get-encounter-order/{id}/{field}', [EncounterOrderController::class, 'getOrders'])->name('get.encounter.order');
    Route::post('/delete-encounter-order/{id}/{field}', [EncounterOrderController::class, 'delete'])->name('delete.encounter.order');
    Route::get('/download-encounter-order/{id}', [EncounterOrderController::class, 'downloadOrderPdf'])->name('download.encounter.order');

    /* encounter sig */
    Route::post('/signature', [DEncountersController::class, 'encounter_sign'])->name('encounter.signature');

    /* encounter documents */
    Route::post('/updated-annotation', [DEncountersController::class, 'updatedAnnotation'])->name('updated.annotation');
    Route::post('/upload-document', [DocumentController::class, 'uploadDocument'])->name('upload.document');
    Route::post('/delete-document/{id}/{type}', [DocumentController::class, 'delete'])->name('delete.document');
    Route::get('/get-documents/{id}/{type}', [DocumentController::class, 'getDocuments'])->name('get.documents');
    Route::post('/upload-file', [DocumentController::class, 'uploadFile'])->name('upload.file');

    /* encounter procedure */
    Route::post('/upsert-procedure', [ProcedureController::class, 'upsert'])->name('upsert.procedure');
    Route::get('/get-procedures/{id}', [ProcedureController::class, 'getProcedures'])->name('get.procedures');
    Route::post('/delete-procedure/{id}', [ProcedureController::class, 'delete'])->name('delete.procedure');

    /* Billing code */
    Route::post('/upsert-billing-code', [BillingCodeController::class, 'upsert'])->name('upsert.billing.code');
    Route::get('/get-billing-code/{id}', [BillingCodeController::class, 'get'])->name('get.billing.code');

    /* referral */
    Route::post('/upsert-referral', [ReferralController::class, 'upsert'])->name('upsert.referral');
    Route::get('/get-referral/{id}', [ReferralController::class, 'getReferral'])->name('get.referral');
    Route::get('/get-doctors-by-specialty/{specialty}', [ReferralController::class, 'getDoctorsBySpecialty'])->name('get.doctors.by.specialty');

    /* appointment status update */
    Route::resource('/appointments', AppointmentController::class);
    Route::post('/update-appointment-status/{id}', [AppointmentController::class, 'updateStatus'])->name('appointment.updateStatus');
    Route::get('/doctor-visit-types/{doctorId?}', [AppointmentController::class, 'getDoctorVisitTypes'])->name('appointments.visit.types');

});
