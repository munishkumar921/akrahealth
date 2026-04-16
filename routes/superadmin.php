<?php

use App\Http\Controllers\SuperAdmin\SAdminController;
use App\Http\Controllers\SuperAdmin\SAdminEmailNotificationController;
use App\Http\Controllers\SuperAdmin\SAdminFinanceController;
use App\Http\Controllers\SuperAdmin\SAdminGeneralSettingController;
use App\Http\Controllers\SuperAdmin\SAdminPaymentPlatformController;
use App\Http\Controllers\SuperAdmin\SAdminServiceController;
use App\Http\Controllers\SuperAdmin\SAdminSubscriptionPlanController;
use App\Http\Controllers\SuperAdmin\SAdminUserController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum', 'is.superAdmin', config('jetstream.auth_session'),
    'is_verify_email',
], 'prefix' => 'superAdmin', 'as' => 'superAdmin.'], function () {

    // Super Admin Dashboard
    Route::get('/dashboard', [SAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/logs', [SAdminController::class, 'logViewer'])->name('logs');

    Route::resource('/usermanagement', SAdminUserController::class);
    Route::get('/user/dashboard', [SAdminUserController::class, 'userdashboard'])->name('userdashboard');
    Route::get('/user/list', [SAdminUserController::class, 'userlist'])->name('userlist');
    Route::get('/user/list/{id}', [SAdminUserController::class, 'show'])->name('userlist.show');
    Route::put('/user/list/{id}', [SAdminUserController::class, 'update'])->name('userlist.update');
    Route::delete('/user/list/{id}', [SAdminUserController::class, 'destroy'])->name('userlist.destroy');
    Route::post('/user/list/{id}/toggle-status', [SAdminUserController::class, 'toggleStatus'])->name('userlist.toggle-status');
    Route::post('/user/list/{id}/toggle-verification', [SAdminUserController::class, 'toggleVerification'])->name('userlist.toggle-verification');
    Route::get('/user/activitymonitoring', [SAdminUserController::class, 'useractivitymonitoring'])->name('activitymonitoring');

    Route::resource('/financemanagement', SAdminFinanceController::class);
    Route::resource('/subscription-plans', SAdminFinanceController::class);
    Route::get('/finance/dashboard', [SAdminFinanceController::class, 'financedashboard'])->name('financedashboard');
    Route::get('/finance/transaction', [SAdminFinanceController::class, 'transaction'])->name('transaction');
    Route::get('/finance/subscriptionplan', [SAdminFinanceController::class, 'subscriptionplan'])->name('subscriptionplan');
    Route::patch('/finance/subscription-plans/{id}/toggle-featured', [SAdminFinanceController::class, 'toggleFeatured'])->name('subscription-plans.toggle-featured');
    Route::get('/finance/subscriber', [SAdminFinanceController::class, 'subscribers'])->name('subscribers');
    Route::get('/finance/subcriptionPlan', [SAdminSubscriptionPlanController::class, 'index'])->name('subcriptionPlan');
    Route::Post('subcriptionPlan/status/{id}', [SAdminSubscriptionPlanController::class, 'statusUpdate'])->name('toggle-active');
    Route::resource('/subcriptionPlan', SAdminSubscriptionPlanController::class);
    Route::get('/finance/payment', [SAdminFinanceController::class, 'payment'])->name('payment');
    Route::get('/finance/invoice', [SAdminFinanceController::class, 'invoice'])->name('invoice');
    Route::resource('/services', SAdminServiceController::class);
    Route::post('/services/{service}/toggle-status', [SAdminServiceController::class, 'toggleStatus'])->name('services.toggle-status');

    Route::resource('/emailnotification', SAdminEmailNotificationController::class);
    Route::get('/email/mass/notification', [SAdminEmailNotificationController::class, 'massnotification'])->name('massnotification');
    Route::get('/email/system/notification', [SAdminEmailNotificationController::class, 'systemnotification'])->name('systemnotification');
    Route::get('/email/mass/mail/notification', [SAdminEmailNotificationController::class, 'massmailnotification'])->name('massmailnotification');
    Route::delete('/email/notification/{id}', [SAdminEmailNotificationController::class, 'destroyNotification'])->name('notifications.destroy');
    Route::post('/email/notifications/mark-all-read', [SAdminEmailNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/email/notifications/delete-all', [SAdminEmailNotificationController::class, 'deleteAll'])->name('notifications.delete-all');

    Route::resource('/generalsetting', SAdminGeneralSettingController::class);
    Route::get('/global/setting', [SAdminGeneralSettingController::class, 'globalsetting'])->name('globalsetting');
    Route::post('/global/setting', [SAdminGeneralSettingController::class, 'updateGlobalSettings'])->name('globalsetting.update');
    Route::get('/SMTP/setting', [SAdminGeneralSettingController::class, 'SMTPsetting'])->name('smtpsetting');
    Route::post('/SMTP/setting', [SAdminGeneralSettingController::class, 'updateSMTPSettings'])->name('smtpsetting.update');
    Route::post('/SMTP/setting/test', [SAdminGeneralSettingController::class, 'testSMTPSettings'])->name('smtpsetting.test');
    Route::get('/language/setting', [SAdminGeneralSettingController::class, 'languagesetting'])->name('languagesetting');
    Route::post('/language/setting', [SAdminGeneralSettingController::class, 'updateLanguageSettings'])->name('languagesetting.update');

    // Roles & Permissions Management
    Route::get('/settings/roles-permissions', [SAdminController::class, 'rolespermission'])->name('rolesandpermission');

    // API routes for Roles & Permissions management
    Route::prefix('api/roles')->group(function () {
        Route::get('/', [SAdminController::class, 'apiRolesList'])->name('api.roles.list');
        Route::post('/', [SAdminController::class, 'apiRolesStore'])->name('api.roles.store');
        Route::delete('/{id}', [SAdminController::class, 'apiRolesDestroy'])->name('api.roles.destroy');
        Route::post('/toggle/{id}', [SAdminController::class, 'apiRolesToggle'])->name('api.roles.toggle');
    });

    // Payment Platforms Management
    Route::resource('/payment-platforms', SAdminPaymentPlatformController::class);
    Route::post('/payment-platforms/{id}/toggle-status', [SAdminPaymentPlatformController::class, 'toggleStatus'])->name('payment-platforms.toggle-status');
    Route::post('/payment-platforms/{id}/set-default', [SAdminPaymentPlatformController::class, 'setDefault'])->name('payment-platforms.set-default');

});
