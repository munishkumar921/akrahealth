<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AppointmentPaymentController extends Controller
{
    public function show($id)
    {
        $appointment = Appointment::with(['patient.user', 'doctor.user'])
            ->findOrFail($id);

        // Ensure the authenticated user is the patient
        if (auth()->id() !== $appointment->patient->user_id) {
            abort(403, 'Unauthorized');
        }

        return Inertia::render('Appointment/Payment', [
            'appointment' => $appointment,
        ]);
    }

    public function process(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        // Ensure the authenticated user is the patient
        if (auth()->id() !== $appointment->patient->user_id) {
            abort(403, 'Unauthorized');
        }

        // Here you would integrate with Razorpay
        // For now, we'll simulate payment success

        $appointment->update([
            'payment_status' => 'paid',
            'payment_method' => 'razorpay',
        ]);

        // Send notifications
        $appointment->patient->user->notify(
            new \App\Notifications\AppointmentPaymentSuccess($appointment)
        );

        $appointment->doctor->user->notify(
            new \App\Notifications\AppointmentPaymentReceived($appointment)
        );

        return redirect()->route('appointments.index')
            ->with('success', 'Payment successful! Your appointment is confirmed.');
    }
}
