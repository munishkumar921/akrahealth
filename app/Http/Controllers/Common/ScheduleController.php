<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorPatient;
use App\Models\Schedule;
use App\Services\EncounterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    public function index()
    {
        $events = [];
        $schedules = Schedule::where('doctor_id', auth()->user()->doctor->id)->get();

        foreach ($schedules as $schedule) {
            $startDateTime = Carbon::parse($schedule->start_date);

            $events[] = [
                'title' => $schedule->reason,
                // Use a local ISO-like string without timezone info
                // so the browser treats it as local time and does not
                // apply an extra timezone shift when creating Date objects.
                'start' => $startDateTime->format('Y-m-d\TH:i:s'),
                'duration' => $schedule->duration,
                'description' => $schedule->notes,
                'background' => '#c6d3e6',
            ];
        }
        // Fetch and merge appointments
        $appointments = Appointment::where('doctor_id', auth()->user()->doctor->id)->with(['patient.user', 'doctor.user'])->get();

        foreach ($appointments as $appointment) {
            $startDateTime = Carbon::parse($appointment->appointment_date.' '.$appointment->appointment_time);
            $endDateTime = $startDateTime->copy()->addMinutes($appointment->doctor->appointment_slot_duration);

            $events[] = [
                'title' => 'Appointment with. '.$appointment->patient->name,
                'call_link' => route('conference.call', $appointment->id),
                'start_at' => $appointment->appointment_date,
                'end_at' => $endDateTime->format('M d,Y H:i'),
                // Send local datetime without timezone information so
                // JS Date treats it as local time and FullCalendar
                // shows the same time as stored in the database.
                'start' => $startDateTime->format('Y-m-d\TH:i:s'),
                'end' => $endDateTime->format('Y-m-d\TH:i:s'),
                'status' => $appointment->status,
                'status' => $appointment->status,
                'id' => $appointment->id,
                'doctor_id' => $appointment->doctor->id,
                'doctor_name' => $appointment->doctor->user->name,
                'patient_id' => $appointment->patient->id,
                'patient_name' => $appointment->patient->name,
                'reason' => $appointment->reason,
                'fee_amount' => $appointment->fee_amount,
                'discount' => $appointment->discount,
                'payment_status' => $appointment->payment_status,
                'appointment_date' => $appointment->appointment_date,
                'appointment_time' => $appointment->appointment_time,
                'appointment_type' => $appointment->appointment_type,
                'speciality_id' => $appointment->doctor->specialities->first()->id ?? '',
                'speciality_name' => $appointment->doctor->specialities->first()->name ?? '',
                'color' => $appointment->status === 'completed' ? '#28a745' :
                    ($appointment->status === 'pending' ? '#ffc107' :
                    ($appointment->status === 'confirmed' ? '#4949e2' :
                    ($appointment->status === 'rejected' || $appointment->status === 'cancelled' ? '#dc3545' : '#6c757d'))),
            ];
        }

        $patient = DoctorPatient::with('doctor', 'patient')->where('doctor_id', auth()->user()->doctor->id)->get();
        $doctor = Doctor::with(['user'])->where('id', $patient[0]->doctor->id ?? null)->first();

        return Inertia::render('Common/Schedule/Index', compact('patient', 'doctor', 'events'));
    }

    public function encounter(Request $request, EncounterService $service)
    {
        if ($request->filled('appointment_id')) {

            $appointment = Appointment::find($request->appointment_id);

            if (! $appointment) {
                return back()->with('error', 'Appointment not found');
            }

            $hospital = auth()->user()->doctor->hospital;

            $data = [
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'appointment_id' => $appointment->id,
                'encounter_type' => $appointment->appointment_type,
                'encounter_location' => $hospital->default_pos_id,
                'chief_complaint' => $appointment->reason,
                'encounter_role' => 'Primary Care Provider',
                'encounter_date_of_service' => $appointment->appointment_date,
                'encounter_condition_work' => 'No',
                'encounter_condition_auto' => 'No',
            ];

            // Save or update encounter using service
            $encounter = $service->upsert($data);

            return redirect()->route('doctor.encounters.edit', $encounter['id']);
        }

        return back()->with('error', 'appointment_id is required');
    }

    public function show($id)
    {
        $appointment = Appointment::with(['patient.user', 'doctor.user'])->find($id);

        if (! $appointment) {
            return back()->with('error', 'Appointment not found');
        }

        return Inertia::render('Common/Schedule/Show', compact('appointment'));
    }
}
