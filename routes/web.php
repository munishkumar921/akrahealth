<?php

use App\Http\Controllers\AgoraController;
use App\Http\Controllers\AjaxSearchController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\ChatsController;
use App\Http\Controllers\Common\MessagesController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DemoRequestController;
use App\Http\Controllers\Doctor\DoctorController;
use App\Http\Controllers\Doctor\PrescriptionController;
use App\Http\Controllers\DrChronoController;
use App\Http\Controllers\InnerPageController;
use App\Http\Controllers\LivekitController;
use App\Http\Controllers\NavigationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Patient\VideoCallController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RazorpayWebhookController;
use App\Http\Controllers\RoleSwitchController;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\VirtualAssistant\VirtualAssistantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/* Open Routes */

/* Razorpay Webhook - Must be before CSRF protection */
Route::post('/razorpay/webhook', [RazorpayWebhookController::class, 'handle'])
    ->name('razorpay.webhook')
    ->withoutMiddleware(['csrf', 'verify_csrf_token', \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::get('upload_file', function () {
    return view('upload');
});

// Handle PUT requests to login by redirecting to GET login
Route::put('/login', function () {
    return redirect()->route('login');
});

/* Agora */
Route::post('/agora/token/{doctor_id}/{appointment_id?}', [AgoraController::class, 'generateToken']);
Route::get('/video-call/{id}', [VideoCallController::class, 'videoCall'])->name('video.call');
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/user_profile', [PageController::class, 'userProfile'])->name('user.profile');
Route::get('/privacy-policy', [PageController::class, 'privacyPolicy'])->name('policy.show');
Route::get('/terms-of-service', [PageController::class, 'termsConditions'])->name('terms.show');
Route::get('/about-us', [PageController::class, 'aboutUs'])->name('about.us');
Route::get('/cancellation-refund-policy', [PageController::class, 'cancellationRefundPolicy'])->name('cancellation-refund-policy');
Route::post('/appointments/update-status/{id}', [AppointmentController::class, 'updateStatus'])->name('appointments.updateStatus');
Route::post('/appointments/cancel', [AppointmentController::class, 'appointmentCancel'])->name('appointment.cancel');
Route::resource('/appointments', AppointmentController::class)->except(['destroy']);
Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
Route::Post('/hire_us', [VirtualAssistantController::class, 'hir_us'])->name('user.hir_us');
Route::get('/accept/invitation/{token}', [VirtualAssistantController::class, 'is_accepted'])->name('user.is_accepted');
/* Demo Request */
Route::resource('/demo-request', DemoRequestController::class);

/* Password change / Reset */
Route::get('/change-password', [PasswordController::class, 'changePassword'])->name('change.password');
Route::post('/update-password', [PasswordController::class, 'updatePassword'])->name('update.password');

/* Website pages (Open Routes) */
Route::get('/emr', [InnerPageController::class, 'emr'])->name('emr');
Route::get('/ai-receptionist', [InnerPageController::class, 'ai'])->name('ai-receptionist');
Route::get('/ai-patient-intake', [InnerPageController::class, 'aiPatientIntake'])->name('ai-patient-intake');
Route::get('/ai-medical-scribe', [InnerPageController::class, 'aiMedicalScribe'])->name('ai-medical-scribe');
Route::get('/dental', [InnerPageController::class, 'dental'])->name('dental');
Route::get('/emr-integration', [InnerPageController::class, 'EmrIntegration'])->name('emr-integration');
Route::get('/api-integration', [InnerPageController::class, 'ApiIntegration'])->name('api-integration');
Route::get('/standard-integrations', [InnerPageController::class, 'StandardIntegrations'])->name('standard-integrations');
Route::get('/billing-integrations', [InnerPageController::class, 'BillingIntegrations'])->name('billing-integrations');
Route::get('/mental-health', [InnerPageController::class, 'MentalHealth'])->name('mental-health');
Route::get('/practice-management', [InnerPageController::class, 'PracticeManagement'])->name('practice-management');
Route::get('/clinical-management', [InnerPageController::class, 'ClinicalManagement'])->name('clinical-management');
Route::get('/medical-billing', [InnerPageController::class, 'MedicalBilling'])->name('medical-billing');
Route::get('/patient-portal', [InnerPageController::class, 'PatientPortal'])->name('patient-portal');
Route::get('/lab-imaging', [InnerPageController::class, 'PatientPortal'])->name('lab-imaging');
Route::get('/lab-imaging', [InnerPageController::class, 'LabImaging'])->name('lab-imaging');
Route::get('/erx', [InnerPageController::class, 'Erx'])->name('erx');
Route::get('/tele-medicine', [InnerPageController::class, 'Telemedicine'])->name('telemedicine');
Route::get('/hire-us', [InnerPageController::class, 'HireUs'])->name('hire-us');
Route::get('/integration', [InnerPageController::class, 'integration'])->name('integration');
Route::get('/security', [InnerPageController::class, 'security'])->name('security');
Route::get('/pharmacy-app', [InnerPageController::class, 'pharmacyApp'])->name('pharmacy-app');
Route::get('/emr-app', [InnerPageController::class, 'emrApp'])->name('emr-app');
Route::get('/pricing', [InnerPageController::class, 'pricing'])->name('pricing');
Route::get('/enquire-demo', [InnerPageController::class, 'enquireDemo'])->name('enquire-demo');
Route::get('/contact-us', [InnerPageController::class, 'contactUs'])->name('contact-us');
Route::post('/contact-us/store', [ContactController::class, 'store'])->name('contact-us.store');
Route::get('/search', [AjaxSearchController::class, 'search'])->name('search');
Route::get('/outreach-program', [InnerPageController::class, 'outreachProgram'])->name('outreach-program');
Route::get('/doctor-on-call', [InnerPageController::class, 'DoctorOnCall'])->name('doctor-on-call');
Route::get('/clinic-management', [InnerPageController::class, 'ClinicManagement'])->name('clinic-management');
Route::get('/patient-management', [InnerPageController::class, 'PatientManagement'])->name('patient-management');
Route::get('/medication-administration-records', [InnerPageController::class, 'MedicationAdministrationRecords'])->name('medication-administration-records');
Route::get('/mobile-iv', [InnerPageController::class, 'MobileIV'])->name('mobile-iv');
Route::get('/nurses-on-call', [InnerPageController::class, 'NursesOnCall'])->name('nurses-on-call');
Route::get('/training', [InnerPageController::class, 'Training'])->name('training');
Route::get('/ai-use-cases', [InnerPageController::class, 'UseCases'])->name('usecases');
Route::get('/prescription', [InnerPageController::class, 'Prescription'])->name('prescription');
Route::get('/prescription-show/{id}', [PrescriptionController::class, 'show'])->name('prescription.show');

/* SignUp Pages (Open Routes) */
Route::get('/signup/patient', [SignupController::class, 'RegisterPatient'])->name('signup.patient');
Route::post('/signup/patient', [SignupController::class, 'storePatient'])->name('signup.patient.store');
Route::get('/signup', [SignupController::class, 'index'])->name('signup');
Route::post('/signup', [SignupController::class, 'store'])->name('signup.store');
Route::post('/signup/payment/verify', [SignupController::class, 'verifyPayment'])->name('signup.payment.verify');
Route::post('/signup/payment/failed', [SignupController::class, 'paymentFailed'])->name('signup.payment.failed');
Route::post('/signup/resend-activation', [SignupController::class, 'resendActivation'])->name('signup.resend-activation');

Route::get('/account/verify/{token}', [SignupController::class, 'verifyAccount'])->name('user.verify');
Route::get('auth/drchrono', [DrChronoController::class, 'redirectToDrChrono'])->name('drchrono.login');
Route::get('auth/drchrono/callback', [DrChronoController::class, 'handleDrChronoCallback'])->name('drchrono.callback');
Route::get('/messages/{id}', [MessagesController::class, 'index'])->name('messages');

Route::get('test', [PageController::class, 'test'])->name('test');

/* Virtual Assistant Routes */
Route::group(['middleware' => ['auth:sanctum', 'is.virtual.assistant', config('jetstream.auth_session'), 'is_verify_email'], 'prefix' => 'assistant', 'as' => 'assistant.'], function () {
    Route::get('/dashboard', [VirtualAssistantController::class, 'dashboard'])->name('dashboard');
});

/* Laboratory Routes */
Route::group(['middleware' => ['auth:sanctum', 'is.laboratory', config('jetstream.auth_session'), 'is_verify_email'], 'prefix' => 'laboratory', 'as' => 'laboratory.'], function () {
    Route::get('/dashboard', [DoctorController::class, 'dashboard'])->name('dashboard');
});

/* Login uma auth */
Route::get('/uma_auth', [SignupController::class, 'umaAuth'])->name('uma.auth');

/* Doctor serach */
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/doctors/search', [DoctorController::class, 'search'])->name('doctors.search');
    Route::get('/doctors/list', [DoctorController::class, 'list'])->name('doctors.lists');
});

/* Secure routes */
Route::group(['middleware' => ['auth:sanctum', config('jetstream.auth_session'), 'is_verify_email']], function () {
    // Handle GET requests gracefully by redirecting to dashboard
    Route::get('/switch-role', function () {
        return redirect()->route('admin.dashboard')->with('info', 'Please use the button to switch roles.');
    });

    // POST method for actual role switching
    Route::post('/switch-role', [RoleSwitchController::class, 'toggle'])->name('switch.role');

    Route::get('/navigation-counts', [NavigationController::class, 'getNavigationCounts'])->name('navigation.counts');

    /* Live Kit */
    Route::get('/conference-call/{appointmentId}', [LivekitController::class, 'conferenceCall'])->name('conference.call');

    /* Notifications */
    Route::get('/notifications/index', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}', [NotificationController::class, 'show'])->name('notifications.show');
    Route::get('/notifications', [NotificationController::class, 'getNotifications'])->name('getNotifications');
    Route::get('/notifications/unread', [NotificationController::class, 'unreadCount'])->name('notifications.unread');
    Route::post('/notifications/read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    /* Subscriptions */
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('/subscriptions/{planId}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::post('/subscriptions/subscribe/{planId}', [SubscriptionController::class, 'subscribe'])->name('subscriptions.subscribe');
    Route::post('/subscriptions/verify-payment/{subscriptionId}', [SubscriptionController::class, 'verifyPayment'])->name('subscriptions.verify-payment');
    Route::post('/subscriptions/cancel/{subscriptionId}', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    Route::get('/subscriptions/renew/{subscriptionId}', [SubscriptionController::class, 'showRenewal'])->name('subscriptions.renew');
    Route::post('/subscriptions/renew/{subscriptionId}/payment', [SubscriptionController::class, 'verifyRenewalPayment'])->name('subscriptions.renew.payment');
    Route::post('/subscriptions/renew/{subscriptionId}/order', [SubscriptionController::class, 'createRenewalOrder'])->name('subscriptions.renew.order');

    /* Subscription Upgrades */
    Route::get('/subscriptions/upgrade/{planId}', [SubscriptionController::class, 'showUpgrade'])->name('subscriptions.upgrade.show');
    Route::post('/subscriptions/upgrade/{planId}', [SubscriptionController::class, 'upgrade'])->name('subscriptions.upgrade');
    Route::post('/subscriptions/upgrade/{subscriptionId}/verify-payment', [SubscriptionController::class, 'verifyUpgradePayment'])->name('subscriptions.upgrade.verify-payment');

    /* Active Subscription Upgrades (NEW) - For upgrading while current subscription is still active */
    Route::get('/subscriptions/upgrade/active/{planId}', [SubscriptionController::class, 'showActiveUpgrade'])->name('subscriptions.upgrade.active.show');
    Route::post('/subscriptions/upgrade/active/{planId}', [SubscriptionController::class, 'activeUpgrade'])->name('subscriptions.upgrade.active');
    Route::post('/subscriptions/upgrade/active/{subscriptionId}/verify-payment', [SubscriptionController::class, 'verifyActiveUpgradePayment'])->name('subscriptions.upgrade.active.verify-payment');

    /* Payment Callback */
    Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
    Route::get('/patient/booking/{id}/payment', [PaymentController::class, 'bookingPayment'])->name('patient.booking.payment');

    /* Language Preference */
    Route::post('/language/update', [App\Http\Controllers\LanguageController::class, 'updateLanguage'])->name('language.update');

    /* Chats */
    Route::get('/chats', [ChatsController::class, 'index'])->name('chats.index');
    Route::post('/chats', [ChatsController::class, 'store'])->name('chats.store');
    Route::patch('/chats/{chat}/read', [ChatsController::class, 'markRead'])->name('chats.read');

});

Route::get('/speed-test', fn () => 'ok');
