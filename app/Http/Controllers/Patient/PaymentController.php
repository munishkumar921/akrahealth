<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function showPaymentPage($id)
    {
        $appointment = Appointment::with(['patient.user', 'doctor.user'])
            ->findOrFail($id);
        // Ensure the authenticated user is the patient
        if (Auth::id() !== $appointment->patient->user_id) {
            abort(403, 'Unauthorized');
        }

        // Check if payment is already completed
        if ($appointment->payment_status === 'paid') {
            return redirect()->route('patient.dashboard')
                ->with('info', 'Payment has already been completed for this appointment.');
        }

        return Inertia::render('Patients/Payment', [
            'appointment' => $appointment,
            'razorpayKey' => config('services.razorpay.key'),
        ]);
    }

    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'appointment_id' => 'required|string',
        ]);

        $appointment = Appointment::with(['patient.user', 'doctor.user'])->findOrFail($request->appointment_id);

        // Ensure the authenticated user is the patient
        if (Auth::id() !== $appointment->patient->user_id) {
            Log::warning('Payment verification unauthorized', [
                'appointment_id' => $request->appointment_id,
                'auth_user_id' => Auth::id(),
                'patient_user_id' => $appointment->patient->user_id ?? null,
            ]);
            abort(403, 'Unauthorized');
        }

        try {
            // Verify payment signature
            $api = new \Razorpay\Api\Api(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ];

            $api->utility->verifyPaymentSignature($attributes);

            // Update appointment payment status
            $appointment->update([
                'payment_status' => 'paid',
                'payment_method' => 'razorpay',
                'payment_id' => $request->razorpay_payment_id,
            ]);

            // Send notifications
            $appointment->patient->user->notify(
                new \App\Notifications\AppointmentPaymentSuccess($appointment)
            );

            $appointment->doctor->user->notify(
                new \App\Notifications\AppointmentPaymentReceived($appointment)
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully!',
            ]);

        } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
            Log::error('Razorpay signature verification failed', [
                'appointment_id' => $request->appointment_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_order_id' => $request->razorpay_order_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed: Invalid signature.',
            ], 400);
        } catch (\Exception $e) {
            Log::error('Payment verification failed', [
                'appointment_id' => $request->appointment_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed: '.$e->getMessage(),
            ], 400);
        }
    }

    public function createRazorpayOrder(Request $request)
    {

        $request->validate([
            'appointment_id' => 'required|string',
        ]);

        $appointment = Appointment::findOrFail($request->appointment_id);
        // Ensure the authenticated user is the patient
        if (Auth::id() !== $appointment->patient->user_id) {
            abort(403, 'Unauthorized');
        }

        // Check if payment is already completed
        if ($appointment->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Payment has already been completed for this appointment.',
            ], 400);
        }

        // Determine currency (default INR) and correct subunit multiplier
        $currency = strtoupper($appointment->currency ?? 'INR');
        $multiplier = $this->getSubunitMultiplier($currency);
        $amount = (int) round(($appointment->fee_amount ?? 0) * $multiplier);
        if ($amount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid appointment amount.',
            ], 422);
        }

        // Validate Razorpay credentials
        $razorpayKey = config('services.razorpay.key');
        $razorpaySecret = config('services.razorpay.secret');

        if (empty($razorpayKey) || empty($razorpaySecret)) {
            Log::error('Razorpay credentials not configured', [
                'key_set' => ! empty($razorpayKey),
                'secret_set' => ! empty($razorpaySecret),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment gateway configuration error. Please contact support.',
            ], 500);
        }

        try {
            $api = new \Razorpay\Api\Api($razorpayKey, $razorpaySecret);

            // Razorpay receipt field has max 40 characters
            $receipt = 'apt_'.$appointment->id;
            if (strlen($receipt) > 40) {
                // If still too long, use hash or truncate
                $receipt = 'apt_'.substr(hash('sha256', $appointment->id), 0, 36);
            }

            $orderData = [
                'receipt' => $receipt,
                'amount' => $amount,
                'currency' => $currency,
                'payment_capture' => 1,
            ];

            $razorpayOrder = $api->order->create($orderData);

            $appointment->update([
                'razorpay_order_id' => $razorpayOrder['id'],
                'payment_status' => 'Pending',
            ]);

            if (! $razorpayOrder || ! isset($razorpayOrder['id'])) {
                Log::error('Razorpay order creation returned invalid response', [
                    'appointment_id' => $appointment->id,
                    'response' => $razorpayOrder,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create Razorpay order. Invalid response from payment gateway.',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'id' => $razorpayOrder['id'],
                'amount' => $razorpayOrder['amount'],
                'currency' => $razorpayOrder['currency'],
            ]);

        } catch (\Razorpay\Api\Errors\BadRequestError $e) {
            Log::error('Razorpay BadRequestError', [
                'appointment_id' => $appointment->id,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid payment request: '.$e->getMessage(),
            ], 400);
        } catch (\Razorpay\Api\Errors\ServerError $e) {
            Log::error('Razorpay ServerError', [
                'appointment_id' => $appointment->id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment gateway server error. Please try again later.',
            ], 500);
        } catch (\Exception $e) {
            Log::error('Razorpay order creation failed', [
                'appointment_id' => $appointment->id,
                'amount' => $amount,
                'currency' => $currency,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment order: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Razorpay uses currency subunits; default is 100 for most currencies.
     * Zero-decimal currencies should use multiplier 1.
     */
    private function getSubunitMultiplier(string $currency): int
    {
        $zeroDecimal = ['JPY', 'KRW', 'VND'];

        return in_array($currency, $zeroDecimal, true) ? 1 : 100;
    }
}
