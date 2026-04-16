<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Notification;
use App\Notifications\AppointmentCreated;
use Carbon\Carbon;

class AppointmentService
{
    /**
     * list
     *
     * @param  mixed  $request
     * @return void
     */
    public function list($request)
    {
        $query = Appointment::with(['patient.user', 'doctor.user', 'lab.user', 'pharmacy.user'])
            ->when($request->mode, function ($query) use ($request) {
                return $query->where('status', $request->mode);
            })
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->whereHas('patient.user', function ($q2) use ($request) {
                        $q2->where('name', 'like', "%{$request->search}%")
                            ->orWhere('email', 'like', "%{$request->search}%")
                            ->orWhere('mobile', 'like', "%{$request->search}%");
                    })->orWhereHas('doctor.user', function ($q3) use ($request) {
                        $q3->where('name', 'like', "%{$request->search}%")
                            ->orWhere('email', 'like', "%{$request->search}%")
                            ->orWhere('mobile', 'like', "%{$request->search}%");
                    })->orWhereHas('lab.user', function ($q3) use ($request) {
                        $q3->where('name', 'like', "%{$request->search}%")
                            ->orWhere('email', 'like', "%{$request->search}%")
                            ->orWhere('mobile', 'like', "%{$request->search}%");
                    })->orWhereHas('pharmacy.user', function ($q3) use ($request) {
                        $q3->where('name', 'like', "%{$request->search}%")
                            ->orWhere('email', 'like', "%{$request->search}%")
                            ->orWhere('mobile', 'like', "%{$request->search}%");
                    });
                });
            })
            ->when($request->status, function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->orderBy('id', 'desc');

        if ($request->has('all')) {
            return $query->get();
        }

        return $query->paginate(request('per_page', paginateLimit()))->withQueryString();
    }

    /*
    * upsert appointment
    */
    public function upsert($data)
    {
        $existingAppointment = ! empty($data['id'])
            ? Appointment::find($data['id'])
            : null;
        $doctorHospitalId = ! empty($data['doctor_id'])
            ? Doctor::whereKey($data['doctor_id'])->value('hospital_id')
            : ($existingAppointment?->doctor?->hospital_id);

        // Set user tracking
        if (isset($data['id']) && $data['id'] > 0) {
            $data['updated_by'] = auth()->id();
        } else {
            $data['created_by'] = auth()->id();
        }

        // Calculate total amount safely
        $data['total_amount'] = ($data['fee_amount'] ?? 0) - ($data['discount'] ?? 0);

        $createdAt = $data['createdAt'] ?? $data['created_at'] ?? now();
        $data['created_at'] = Carbon::parse($createdAt)->format('Y-m-d H:i:s');
        $data['updated_at'] = Carbon::parse($createdAt)->format('Y-m-d H:i:s');

        // Use updateOrCreate for better efficiency
        $appointment = Appointment::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $data
        );

        // Auto-approve if created by doctor
        if (auth()->user()->hasRole('Doctor') && $appointment->wasRecentlyCreated && $appointment->status == 'pending') {
            $appointment->update(['status' => 'confirmed']);
        }

        // Send notifications
        $this->sendAppointmentNotifications($appointment);

        $auditService = app(AuditService::class);
        if ($existingAppointment) {
            $auditService->create([
                'module' => 'Appointment',
                'action' => 'update',
                'hospital_id' => $doctorHospitalId,
                'description' => 'Appointment updated',
                'old_values' => $existingAppointment->toArray(),
                'new_values' => $appointment->fresh()->toArray(),
            ]);
        } else {
            $auditService->create([
                'module' => 'Appointment',
                'action' => 'create',
                'hospital_id' => $doctorHospitalId,
                'description' => 'Appointment created',
                'new_values' => $appointment->fresh()->toArray(),
            ]);
        }

        return $appointment;
    }

    private function sendAppointmentNotifications(Appointment $appointment)
    {
        $notificationService = app(InAppNotificationService::class);

        // ----------------------------------------------
        // PATIENT NOTIFICATION
        // ----------------------------------------------
        if ($appointment->patient?->user) {
            $appointment->patient->user->notify(
                new AppointmentCreated($appointment, 'Patient')
            );
        }

        // ----------------------------------------------
        // DOCTOR NOTIFICATION
        // ----------------------------------------------
        if ($appointment->doctor?->user && ! auth()->user()->hasRole('Doctor')) {
            $appointment->doctor->user->notify(
                new AppointmentCreated($appointment, 'Doctor')
            );
        }

        $notificationService->notifyAdminsForHospital(
            $appointment->doctor?->hospital_id,
            $notificationService->buildPayload(
                'New appointment booked',
                'A new appointment was booked for '.$appointment->patient?->name.'.',
                'appointment_created',
                [
                    'recipient_role' => 'Admin',
                    'action_url' => route('admin.allAppointments'),
                    'appointment_id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                    'doctor_id' => $appointment->doctor_id,
                    'related_model_type' => Appointment::class,
                    'related_model_id' => $appointment->id,
                ]
            )
        );
    }

    /*
    * get for data
    */
    public function getFormData()
    {
        $data['appointment_type'] = [
            ['id' => 'instant_call', 'name' => 'Instant call'],
            ['id' => 'clinic_appointment', 'name' => 'Clinic appointment'],
            ['id' => 'video_appointment', 'name' => 'Video appointment'],
            ['id' => 'consultation', 'name' => 'Consultation'],
            ['id' => 'follow_up', 'name' => 'Follow up'],
            ['id' => 'lab_test', 'name' => 'Lab Test'],
            ['id' => 'pharmacy_consult', 'name' => 'Pharmacy Consult'],
            ['id' => 'home_visit', 'name' => 'Home Visit'],
            ['id' => 'vaccination', 'name' => 'Vaccination'],
            ['id' => 'therapy', 'name' => 'Therapy'],
            ['id' => 'diagnostic', 'name' => 'Diagnostic'],
            ['id' => 'teleconsult', 'name' => 'Teleconsult'],
            ['id' => 'emergency', 'name' => 'Emergency'],
        ];

        $data['appointment_mode'] = [
            ['id' => 'online', 'name' => 'Online'],
            ['id' => 'in_person', 'name' => 'In Person'],
            ['id' => 'home_visit', 'name' => 'Home Visit'],
            ['id' => 'phone_call', 'name' => 'Phone Call'],
        ];

        $data['status'] = [
            ['id' => 'pending', 'name' => 'Pending'],
            ['id' => 'ongoing', 'name' => 'Ongoing'],
            ['id' => 'confirmed', 'name' => 'Confirmed'],
            ['id' => 'cancelled', 'name' => 'Cancelled'],
            ['id' => 'completed', 'name' => 'Completed'],
            ['id' => 'rescheduled', 'name' => 'Rescheduled'],
            ['id' => 'no_show', 'name' => 'No Show'],
            ['id' => 'rejected', 'name' => 'Rejected'],
        ];

        $data['recurring_type'] = [
            ['id' => 'none', 'name' => 'None'],
            ['id' => 'weekly', 'name' => 'Weekly'],
            ['id' => 'monthly', 'name' => 'Monthly'],
            ['id' => 'custom', 'name' => 'Custom'],
        ];

        $data['payment_status'] = [
            ['id' => 'pending', 'name' => 'Pending'],
            ['id' => 'paid', 'name' => 'Paid'],
            ['id' => 'refunded', 'name' => 'Refunded'],
            ['id' => 'failed', 'name' => 'Failed'],
            ['id' => 'partially_paid', 'name' => 'Partially Paid'],
            ['id' => 'cancelled', 'name' => 'Cancelled'],
        ];

        $data['payment_method'] = [
            ['id' => 'razorpay', 'name' => 'Razorpay'],
            ['id' => 'card', 'name' => 'Card'],
            ['id' => 'cash', 'name' => 'Cash'],
        ];

        return $data;
    }

    /**
     * getAppointmentsForPatientInRange
     *
     * @param  mixed  $patientId
     * @param  mixed  $start
     * @param  mixed  $end
     * @return void
     */
    public function getAppointmentsForPatientInRange($patientId, $start, $end)
    {
        return Appointment::with(['doctor.user'])
            ->where('patient_id', $patientId)
            ->whereIn('status', ['pending', 'confirmed', 'ongoing', 'completed', 'cancelled'])
            ->orderBy('appointment_date', 'asc')
            ->get();
    }

    /**
     * sendStatusUpdateNotifications
     *
     * @param  mixed  $appointment
     * @return void
     */
    public function updateStatus($data)
    {
        $oldAppointment = Appointment::find($data['id']);
        $appointment = Appointment::with(['doctor.user', 'patient.user'])->find($data['id']);

        if (! $appointment) {
            return back()->with(['error' => 'Appointment not found']);
        }

        $appointment->status = $data['status'];
        $appointment->save();

        if (isset($data['notification_id']) && $data['notification_id']) {
            $notification = Notification::find($data['notification_id']);
            if ($notification) {
                $notification->delete();
            }
        }
        $this->sendStatusUpdateNotifications($appointment);

        app(AuditService::class)->create([
            'module' => 'Appointment',
            'action' => 'update',
            'hospital_id' => $appointment->doctor?->hospital_id,
            'description' => 'Appointment status updated to '.$appointment->status,
            'old_values' => $oldAppointment?->toArray(),
            'new_values' => $appointment->fresh()->toArray(),
        ]);

        return $appointment;
    }

    private function sendStatusUpdateNotifications(Appointment $appointment)
    {
        $notificationService = app(InAppNotificationService::class);

        // Send payment link if confirmed
        if ($appointment->status === 'confirmed' && $appointment->patient?->user) {
            $appointment->patient->user->notify(
                new \App\Notifications\AppointmentPaymentLink($appointment)
            );
        }

        $notificationClass = $appointment->status === 'cancelled'
            ? \App\Notifications\AppointmentCancelled::class
            : \App\Notifications\AppointmentStatusUpdated::class;

        // Notify Patient
        if ($appointment->patient?->user) {
            $appointment->patient->user->notify(
                new $notificationClass($appointment, 'Patient')
            );
        }

        // Notify Doctor
        if ($appointment->doctor?->user) {
            $appointment->doctor->user->notify(
                new $notificationClass($appointment, 'Doctor')
            );
        }

        $notificationService->notifyAdminsForHospital(
            $appointment->doctor?->hospital_id,
            $notificationService->buildPayload(
                'Appointment updated',
                'Appointment for '.$appointment->patient?->name.' was '.$appointment->status.'.',
                'appointment_updated',
                [
                    'recipient_role' => 'Admin',
                    'action_url' => route('admin.allAppointments'),
                    'appointment_id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                    'doctor_id' => $appointment->doctor_id,
                    'status' => $appointment->status,
                    'related_model_type' => Appointment::class,
                    'related_model_id' => $appointment->id,
                ]
            )
        );
    }
}
