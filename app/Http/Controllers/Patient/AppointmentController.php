<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorPatient;
use App\Models\Hospital;
use App\Models\ProviderException;
use App\Models\Speciality;
use App\Models\VisitType;
use App\Services\AppointmentService;
use App\Services\DoctorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    /**
     * bookAppointment
     *
     * @return void
     */
    public function bookAppointment()
    {
        $data['specialities'] = Speciality::orderBy('name', 'asc')->select('name', 'id')->get();
        $data['appointmentType'] = VisitType::orderBy('name', 'asc')->select('name', 'id', 'duration')->get();
        $data['doctors'] = Doctor::with('user', 'hospital', 'specialities')->get();

        // Get today's appointments for the logged-in patient
        $patientId = auth()->user()?->patient?->id;
        if ($patientId) {
            $today = Carbon::today()->format('Y-m-d');
            $data['todaysAppointments'] = \App\Models\Appointment::with('doctor.user')
                ->where('patient_id', $patientId)
                ->where('appointment_date', $today)
                ->orderBy('appointment_time', 'asc')
                ->get();
        } else {
            $data['todaysAppointments'] = [];
        }

        return Inertia::render('Patients/BookAppointment', compact('data'));
    }

    /**
     * doctorsList
     *
     * @param  mixed  $specialityId
     * @return void
     */
    public function doctorsList($specialityId = null)
    {
        $doctors = Doctor::query()->with('user:id,name')
            ->select('id', 'user_id')
            ->whereHas('specialities', function ($query) use ($specialityId) {
                $query->where('speciality_id', $specialityId);
            })->get();

        return response()->json($doctors);
    }

    /**
     * getDoctorsSlot
     *
     * @param  mixed  $request
     * @return void
     */
    public function getDoctorsSlot(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date',
        ]);

        $schedules = app(DoctorService::class)->getDoctorSlots($request->doctor_id, $request->date, $request->duration);

        return response()->json($schedules);
    }

    public function getDoctorVisitDuraction($type, $doctorId)
    {
        $data = VisitType::where('doctor_id', $doctorId)->where('name', $type)->get();

        return response()->json($data);
    }

    /**
     * storeAppointment
     *
     * @param  mixed  $request
     * @return void
     */
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
        $slots = (new DoctorService)->getDoctorSlots($doctor->id, $appointment_date);
        $slot = $this->findSlotForTime($slots, $request->appointment_time);

        $data = $request->all();
        $data['doctor_id'] = $doctor->id;
        $data['patient_id'] = auth()->user()->patient->id;
        $data['appointment_date'] = $appointment_date;
        $data['appointment_time'] = $slot;
        $data['fee_amount'] = ! empty($request->fee_amount) ? (float) $request->fee_amount : (float) $doctor->consultation_fee;
        $data['discount'] = 0;
        $data['status'] = 'Pending';
        $data['created_at'] = Carbon::parse($request->post('createdAt'))->format('Y-m-d H:i:s');
        $data['updated_at'] = Carbon::parse($request->post('createdAt'))->format('Y-m-d H:i:s');
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

        return redirect()->back()->with('success', 'Appointment booked successfully and is pending approval.');
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
            [$start, $end] = explode(' - ', $slot['slot']);
            $startTime = Carbon::createFromFormat('H:i', $start);
            $endTime = Carbon::createFromFormat('H:i', $end);

            if ($inputTime->between($startTime, $endTime, false)) {
                return $slot['value'];
            }
        }

        return $time;
    }

    /**
     * bookedAppointments
     *
     * @param  mixed  $request
     * @param  mixed  $appointmentService
     * @return void
     */
    public function bookedAppointments(Request $request, AppointmentService $appointmentService)
    {
        $request->validate([
            'month' => 'required|numeric',
            'year' => 'required|numeric',
        ]);

        $start = Carbon::create($request->year, $request->month, 1)->startOfMonth()->format('Y-m-d');
        $end = Carbon::create($request->year, $request->month, 1)->endOfMonth()->format('Y-m-d');
        $patientId = auth()->user()->patient->id;
        $appointments = $appointmentService->getAppointmentsForPatientInRange($patientId, $start, $end);
        // Eager load relationships to prevent N+1 query issues and ensure all data is available.
        $appointments->load(['doctor.user', 'doctor.specialities', 'patient.user']);

        $events = $appointments->map(function ($appointment) {
            // Create a single Carbon instance for the start time to avoid redundant parsing.
            $startDateTime = Carbon::parse($appointment->appointment_date.' '.$appointment->appointment_time);
            $endDateTime = $startDateTime->copy()->addMinutes($appointment->doctor->appointment_slot_duration);

            return [
                'title' => 'Appointment with Dr. '.$appointment->doctor->user->name,
                'call_link' => route('conference.call', $appointment->id),
                'created_at' => Carbon::parse($appointment->created_at)->format('d-m-Y h:i A'),
                'call_start_at' => Carbon::parse($startDateTime)->format('d-m-Y h:i A'),
                'start_at' => $startDateTime->format('d-m-Y H:i'),
                'end_at' => $endDateTime->format('Y-m-d H:i'),
                // Use local datetime strings without timezone so the browser
                // treats them as local and does not shift them.
                'start' => $startDateTime->format('Y-m-d\TH:i:s'),
                'end' => $endDateTime->format('Y-m-d\TH:i:s'),
                'status' => $appointment->status,
                'id' => $appointment->id,
                'doctor_id' => $appointment->doctor->id,
                'doctor_name' => $appointment->doctor->user->name,
                'patient_id' => $appointment->patient->id,
                'patient_name' => $appointment->patient->user->name,
                'reason' => $appointment->reason,
                'fee_amount' => $appointment->fee_amount ?? null,
                'currency' => $appointment->currency ?? null,
                'payment_status' => $appointment->payment_status,
                'discount' => $appointment->discount ?? null,
                'duration_minutes' => $appointment->duration_minutes ?? null,
                'appointment_date' => $appointment->appointment_date,
                'appointment_time' => $appointment->appointment_time,
                'appointment_type' => $appointment->appointment_type,
                'speciality_id' => $appointment->doctor->specialities->first()?->id,
                'speciality_name' => $appointment->doctor->specialities->first()?->name,
                'color' => $appointment->status == 'confirmed' ? '#28a745' : ($appointment->status == 'Cancelled' ? '#dc3545' : '#ffc107'),
            ];
        });

        return response()->json($events);
    }

    public function getDoctorVisitTypes($doctorId = null)
    {
        // If no doctorId provided, use the authenticated doctor's id
        if (! $doctorId && auth()->user()->doctor) {
            $doctorId = auth()->user()->doctor->id;
        }

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
     * providerExceptions
     *
     * @param  mixed  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function providerExceptions(Request $request)
    {
        $request->validate([
            'month' => 'required|numeric',
            'year' => 'required|numeric',
            'doctor_id' => 'nullable|exists:doctors,id',
        ]);

        // Get all hospitals for the current user's context
        $hospitalIds = Hospital::pluck('id')->toArray();

        // Get the start and end of the requested month
        $startOfMonth = Carbon::create($request->year, $request->month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($request->year, $request->month, 1)->endOfMonth();

        $query = ProviderException::with(['doctor.user'])
            ->whereIn('hospital_id', $hospitalIds)
            ->where('is_active', true)
            ->whereBetween('exception_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')]);

        // Filter by doctor if provided
        if ($request->doctor_id) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $exceptions = $query->get();

        $events = [];

        foreach ($exceptions as $exception) {
            // Get the exception date and times
            $exceptionDate = Carbon::parse($exception->exception_date);
            $startTime = $exception->start_time instanceof \Carbon\Carbon
                ? $exception->start_time
                : Carbon::parse($exception->start_time);
            $endTime = $exception->end_time instanceof \Carbon\Carbon
                ? $exception->end_time
                : Carbon::parse($exception->end_time);

            // Create full datetime for calendar
            $startDateTime = $exceptionDate->copy()->setTime(
                $startTime->hour,
                $startTime->minute,
                $startTime->second
            );
            $endDateTime = $exceptionDate->copy()->setTime(
                $endTime->hour,
                $endTime->minute,
                $endTime->second
            );

            $doctorName = $exception->doctor?->user?->name ?? 'Unknown Provider';

            $events[] = [
                'id' => $exception->id.'_'.$exceptionDate->format('Ymd'),
                'title' => 'Exception: '.$exception->title.' ('.$doctorName.')',
                'start' => $startDateTime->toIso8601String(),
                'end' => $endDateTime->toIso8601String(),
                'extendedProps' => [
                    'exception_date' => $exception->exception_date,
                    'start_time' => $exception->start_time,
                    'end_time' => $exception->end_time,
                    'reason' => $exception->reason,
                    'doctor' => $doctorName,
                    'doctor_id' => $exception->doctor_id,
                    'is_exception' => true,
                ],
                'backgroundColor' => '#f35353',
                'borderColor' => '#f35353',
                'textColor' => '#ffffff',
                'classNames' => ['exception-event'],
            ];
        }

        return response()->json($events);
    }
}
