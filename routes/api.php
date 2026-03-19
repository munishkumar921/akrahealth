<?php

use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LivekitController;
use App\Http\Controllers\RazorpayWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| LiveKit Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('livekit')->group(function () {
    Route::post('/record/start', [LivekitController::class, 'startRecording']);
    Route::post('/record/stop', [LivekitController::class, 'stopRecording']);
});

/*
|--------------------------------------------------------------------------
| Contact Routes
|--------------------------------------------------------------------------
*/
Route::post('/contact', [App\Http\Controllers\ContactController::class, 'submit']);

/*
|--------------------------------------------------------------------------
| Razorpay Webhook Routes (no auth middleware - Razorpay will call these)
|--------------------------------------------------------------------------
*/
Route::post('/razorpay/invoice-webhook', [RazorpayWebhookController::class, 'handleInvoiceWebhook'])->name('razorpay.invoice.webhook');

/*
|--------------------------------------------------------------------------
| Razorpay Webhook Debug Route
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Invoice API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('invoices')->group(function () {
    // Get invoice summary for dashboard
    Route::get('/summary', [InvoiceController::class, 'getSummary'])->name('api.invoices.summary');

    // Patient invoice routes
    Route::get('/my-invoices', [InvoiceController::class, 'patientInvoices'])->name('api.invoices.my-invoices');
    Route::get('/my-invoices/{id}', [InvoiceController::class, 'patientInvoiceShow'])->name('api.invoices.my-invoices.show');
});

/*
|--------------------------------------------------------------------------
| Signed Route for Invoice Payment
|--------------------------------------------------------------------------
*/
Route::get('/invoices/{invoice}/pay', [InvoiceController::class, 'show'])->name('invoice.pay');
