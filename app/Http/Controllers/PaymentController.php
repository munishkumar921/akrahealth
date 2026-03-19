<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function callback(Request $request)
    {
        $paymentLinkId = $request->query('razorpay_payment_link_id');
        $paymentLinkReferenceId = $request->query('razorpay_payment_link_reference_id');
        $paymentLinkStatus = $request->query('razorpay_payment_link_status');
        $paymentId = $request->query('razorpay_payment_id');

        // Find the subscription by payment_link_id
        $subscription = UserSubscription::where('payment_link_id', $paymentLinkId)->first();

        if (! $subscription) {
            Log::error('Subscription not found for payment_link_id', ['payment_link_id' => $paymentLinkId]);

            return Redirect::route('signup')->withErrors(['error' => 'Subscription not found.']);
        }

        $user = $subscription->user;
        $subscriptionPlanId = $subscription->subscription_plan_id; // Store plan ID before deletion

        if ($paymentLinkStatus === 'paid') {
            // Payment successful
            $subscription->update([
                'status' => 'active',
                'payment_status' => 'paid',
                'razorpay_payment_id' => $paymentId,
                'end_date' => \Carbon\Carbon::now()->addDays(14)->toDateString(), // 14-day trial
            ]);

            // Activate user
            $user->update(['is_active' => true]);

            Log::info('Payment successful', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'payment_id' => $paymentId,
            ]);

            return Redirect::route('login')->with('success', 'Payment successful! Your 14-day trial has started.');
        } else {
            // Payment failed or cancelled
            Log::info('Payment failed or cancelled', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'status' => $paymentLinkStatus,
            ]);

            // Delete user and subscription
            $subscription->delete();
            $user->delete();

            return Redirect::route('signup', ['subscription_plan_id' => $subscriptionPlanId])
                ->withErrors(['error' => 'Payment failed. Please try signing up again.']);
        }
    }

    public function bookingPayment($id)
    {
        $appointment = Appointment::with(['doctor', 'doctor.user', 'patient', 'patient.user'])->findOrFail($id);

        return Inertia::render('Patients/Payment', [
            'appointment' => $appointment,
            'razorpayKey' => config('services.razorpay.key'),
        ]);
    }
}
