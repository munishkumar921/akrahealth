<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Schedule;
use App\Models\User;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    protected AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = [];
        $schedules = Schedule::get();
        foreach ($schedules as $schedule) {
            $events[] = [
                'title' => $schedule->reason,
                'start' => $schedule->start_date,
                'duration' => $schedule->duration,
                'description' => $schedule->notes,
                'background' => '#c6d3e6',
                'id' => $schedule->id,
            ];

        }
        $patient = Patient::with(['user'])->where('preferred_doctor_id', Auth::id())->get();
        $doctor = User::with(['patient'])->where('id', $patient[0]->preferred_doctor_id ?? null)->get();

        return Inertia::render('Inner/Appointment', [
            'patient' => $patient,
            'doctor' => $doctor,
            'schedules' => $events,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        $appointment->load(['patient.user', 'doctor.user', 'hospital']);

        return response()->json([
            'appointment' => $appointment,
            'patient' => $appointment->patient,
            'doctor' => $appointment->doctor,
            'patient_user' => $appointment->patient->user,
            'doctor_user' => $appointment->doctor->user,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, AppointmentService $obj)
    {
        $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => ['required', 'exists:doctors,id'],
            'start_date' => ['required', 'date'],
            'start_time' => ['required', 'string'],
            'end_time' => ['required', 'string'],
            'visit_type_id' => ['required', 'exists:visit_types,id'],
            'reason' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $data = $request->all();
        $data['status'] = 'pending';
        $appointment = $obj->upsert($data);

        return back()->with('success', 'The new Appointments has been saved successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        // Add authorization if needed, e.g., Gate::authorize('cancel', $appointment);
        $appointment->delete();

        return back()->with('success', 'Appointment cancelled successfully.');
    }

    // Note: Accept Request only, get $id from route
    public function updateStatus(Request $request)
    {
        $id = $request->route('id') ?? $request->input('id');
        if (! $id) {
            return back()->withErrors(['id' => 'Appointment id is required']);
        }

        $data = array_merge($request->all(), ['id' => $id]);
        $this->appointmentService->updateStatus($data);

        return back()->with(['success' => 'Appointment status updated successfully.']);
    }

    public function getAppointments()
    {
        $appointments = Appointment::with(['patient.user'])->where('doctor_id', Auth::id())->get();

        return response()->json($appointments);
    }

    public function getDoctorVisitTypes($doctorId = null)
    {
        // If no doctorId provided, use the authenticated doctor's id
        if (! $doctorId && Auth::user()->doctor) {
            $doctorId = Auth::user()->doctor->id;
        }

        if (! $doctorId) {
            return response()->json([]);
        }

        $types = \App\Models\VisitType::where('doctor_id', $doctorId)->get();

        return response()->json($types);
    }
}
