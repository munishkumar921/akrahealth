<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Services\InAppNotificationService;
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
        $notificationService = app(InAppNotificationService::class);

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

        $notificationService->notifyPatient(
            $appointment->patient,
            $notificationService->buildPayload(
                'Payment received',
                'Your appointment payment has been recorded successfully.',
                'payment_received',
                [
                    'recipient_role' => 'Patient',
                    'appointment_id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                    'doctor_id' => $appointment->doctor_id,
                    'action_url' => route('patient.booking.list'),
                    'related_model_type' => Appointment::class,
                    'related_model_id' => $appointment->id,
                ]
            )
        );

        $notificationService->notifyUser(
            $appointment->doctor?->user,
            $notificationService->buildPayload(
                'Patient payment received',
                ($appointment->patient?->name ?? 'A patient').' completed an appointment payment.',
                'payment_received',
                [
                    'recipient_role' => 'Doctor',
                    'appointment_id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                    'doctor_id' => $appointment->doctor_id,
                    'related_model_type' => Appointment::class,
                    'related_model_id' => $appointment->id,
                ]
            )
        );

        $notificationService->notifyAdminsForHospital(
            $appointment->doctor?->hospital_id,
            $notificationService->buildPayload(
                'Patient payment received',
                ($appointment->patient?->name ?? 'A patient').' made a payment for an appointment.',
                'payment_received',
                [
                    'recipient_role' => 'Admin',
                    'appointment_id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                    'doctor_id' => $appointment->doctor_id,
                    'related_model_type' => Appointment::class,
                    'related_model_id' => $appointment->id,
                ]
            )
        );

        return redirect()->route('appointments.index')
            ->with('success', 'Payment successful! Your appointment is confirmed.');
    }
}
