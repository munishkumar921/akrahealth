<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\AppointmentCreated;

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
        // Set user tracking
        if (isset($data['id']) && $data['id'] > 0) {
            $data['updated_by'] = auth()->id();
        } else {
            $data['created_by'] = auth()->id();
        }

        // Calculate total amount safely
        $data['total_amount'] = ($data['fee_amount'] ?? 0) - ($data['discount'] ?? 0);

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

        return $appointment;
    }

    private function sendAppointmentNotifications(Appointment $appointment)
    {
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

        // // ----------------------------------------------
        // // ADMIN NOTIFICATIONS
        // // ----------------------------------------------
        // if ($appointment->wasRecentlyCreated) {
        //     $adminUsers = User::whereHas('roles', function ($query) {
        //         $query->whereIn('name', ['Admin', 'Super Admin']);
        //     })->get();

        //     foreach ($adminUsers as $admin) {
        //         $admin->notify(
        //             new AppointmentCreated($appointment, 'Admin')
        //         );
        //     }
        // }
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

        return $appointment;
    }

    private function sendStatusUpdateNotifications(Appointment $appointment)
    {
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

        // // Notify Admins
        // $admins = User::whereHas(
        //     'roles',
        //     fn($q) =>
        //     $q->whereIn('name', ['Admin', 'Super Admin'])
        // )->get();

        // foreach ($admins as $admin) {
        //     $admin->notify(
        //         new $notificationClass($appointment, 'Admin')
        //     );
        // }
    }
}
