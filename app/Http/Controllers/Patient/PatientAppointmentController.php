<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorPatient;
use App\Models\VisitType;
use App\Services\AppointmentService;
use App\Services\DoctorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PatientAppointmentController extends Controller
{
    public function bookAppointment()
    {
        $doctors = Doctor::with('user', 'hospital')->get();

        return Inertia::render('Patients/BookAppointment', [
            'doctors' => $doctors,
        ]);
    }

    public function doctorsList($speciality = null)
    {
        $doctors = Doctor::with('user', 'hospital', 'specialities')
            ->when($speciality, fn ($q) => $q->whereHas('specialities', fn ($sq) => $sq->where('name', $speciality)))
            ->get();

        return response()->json($doctors);
    }

    public function getDoctorsSlot(Request $request)
    {
        // Implementation for getting doctor slots
        return response()->json([]);
    }

    /**
     * Get doctor visit types
     */
    public function getDoctorVisitTypes($doctorId = null)
    {
        if (! $doctorId) {
            return response()->json([]);
        }

        $types = VisitType::where('doctor_id', $doctorId)->get();
        $data = [];
        foreach ($types as $type) {
            $data[$type->name] = $type->name;
        }

        return response()->json($data);
    }

    /**
     * Get doctor visit duration
     */
    public function getDoctorVisitDuraction($type, $doctorId)
    {
        $data = VisitType::where('doctor_id', $doctorId)->where('name', $type)->get();

        return response()->json($data);
    }

    public function storeAppointment(Request $request, AppointmentService $appointmentService)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'appointment_type' => 'required|string',
            'reason' => 'nullable|string|max:1000',
            'fee_amount' => 'nullable|numeric|min:0',
        ]);

        $doctor = Doctor::with(['user'])->where('id', $request->doctor_id)->first();

        $appointment_date = Carbon::parse($request->appointment_date)->format('Y-m-d');

        // Get slots to find the proper slot value
        $slots = (new DoctorService)->getDoctorSlots($doctor->id, $appointment_date);
        $slot = $this->findSlotForTime($slots, $request->appointment_time);

        $data = $request->all();
        $data['doctor_id'] = $doctor->id;
        $data['patient_id'] = auth()->user()->patient->id;
        $data['appointment_date'] = $appointment_date;
        $data['appointment_time'] = $slot;
        $data['fee_amount'] = ! empty($request->fee_amount) ? (float) $request->fee_amount : (float) $doctor->consultation_fee;
        $data['discount'] = 0;

        // If rescheduling (id exists), keep existing status, otherwise set to pending
        if (! isset($data['id']) || empty($data['id'])) {
            $data['status'] = 'pending';
        }

        $appointment = $appointmentService->upsert($data);

        if ($appointment) {
            $doctorPatient = DoctorPatient::where('doctor_id', $doctor->id)->where('patient_id', auth()->user()->patient->id)->first();
            if (! $doctorPatient && $request->accept_term_condition == true) {
                DoctorPatient::create([
                    'patient_id' => auth()->user()->patient->id,
                    'doctor_id' => $doctor->id,
                    'accept_term_condition' => $request->accept_term_condition,
                ]);
            }
        }

        // Determine message based on whether it's a new booking or reschedule
        $message = (isset($data['id']) && ! empty($data['id']))
            ? 'Appointment rescheduled successfully'
            : 'Appointment booked successfully and is pending approval.';

        return back()->with('success', $message);
    }

    /**
     * findSlotForTime
     *
     * @param  mixed  $slots
     * @param  mixed  $time
     * @return void
     */
    public function findSlotForTime($slots, $time)
    {
        $inputTime = Carbon::createFromFormat('H:i', $time);

        foreach ($slots as $slot) {
            if (isset($slot['slot']) && isset($slot['value'])) {
                [$start, $end] = explode(' - ', $slot['slot']);
                $startTime = Carbon::createFromFormat('H:i', $start);
                $endTime = Carbon::createFromFormat('H:i', $end);

                if ($inputTime->between($startTime, $endTime, false)) {
                    return $slot['value'];
                }
            }
        }

        return $time;
    }

    public function bookedAppointments(Request $request, AppointmentService $appointmentService)
    {
        // Implementation for getting booked appointments
        return response()->json([]);
    }
}
