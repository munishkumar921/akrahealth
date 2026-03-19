<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\EncounterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CallController extends Controller
{
    public $encounter;

    /**
     * __construct
     *
     * @param  mixed  $encounter
     * @return void
     */
    public function __construct(EncounterService $encounter)
    {
        $this->encounter = $encounter;
    }

    /**
     * todaysCall
     *
     * @return void
     */
    public function todaysCall()
    {
        $appointments = Appointment::with('patient.user', 'doctor.user', 'doctor.hospital')
            ->where('doctor_id', auth()->user()?->doctor?->id)
            ->where('status', '!=', 'pending')
            // ->whereDate('appointment_date', Carbon::today())
            ->get();

        return Inertia::render('Doctors/DoctorCalls', compact('appointments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function encounterStore(Request $request)
    {
        $encounter = $this->encounter->upsert($request->all());

        return response()->json($encounter);
    }

    public function notifyPatient(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
        ]);

        $appointment = Appointment::with('patient.user')->find($request->appointment_id);

        if (! $appointment || ! $appointment->patient || ! $appointment->patient->user) {
            \Illuminate\Support\Facades\Log::warning('Call notification failed: Patient not found for appointment ID '.$request->appointment_id);

            return response()->json(['message' => 'Patient not found'], 404);
        }

        // Check if appointment is within valid time window (allow 2 hours before/after)
        $appointmentDateTime = \Carbon\Carbon::parse($appointment->appointment_date.' '.$appointment->appointment_time);
        $now = \Carbon\Carbon::now();
        $timeDifference = $now->diffInMinutes($appointmentDateTime, false);

        // Allow calls within 2 hours before or after appointment time
        // if (abs($timeDifference) > 120) {
        //     \Illuminate\Support\Facades\Log::warning('Call notification failed: Appointment not within valid time window for appointment ID ' . $request->appointment_id . '. Time difference: ' . $timeDifference . ' minutes');
        //     return response()->json(['message' => 'Video calls can only be initiated within 2 hours of the appointment time'], 400);
        // }

        $link = route('patient.live.consultation', ['id' => $appointment->id]);

        try {
            $appointment->patient->user->notify(new \App\Notifications\JoinCallNotification($appointment, $link));
            \Illuminate\Support\Facades\Log::info('Call notification sent successfully for appointment ID '.$request->appointment_id);

            return response()->json(['message' => 'Notification sent successfully']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Call Notification Error for appointment ID '.$request->appointment_id.': '.$e->getMessage());

            return response()->json(['message' => 'Failed to send notification. Please try again.'], 500);
        }
    }
}
