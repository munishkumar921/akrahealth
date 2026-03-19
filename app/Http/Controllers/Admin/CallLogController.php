<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CallLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $appointments = [];
        $keyword = $request->get('search', '');
        if (isset(auth()->user()->doctor->hospital_id)) {

            $hospitalId = auth()->user()->doctor->hospital_id;

            $query = Appointment::with([
                'patient.user',
                'doctor.user',
                'doctor.hospital',
            ])->whereHas('doctor', function ($query) use ($hospitalId) {
                $query->where('hospital_id', $hospitalId);
            });

            $statusFilters = collect(['cancelled', 'completed', 'pending'])
                ->filter(fn ($status) => $request->boolean($status))
                ->values()
                ->toArray();

            $query->when(
                $statusFilters,
                fn ($q) => $q->whereIn('status', $statusFilters)
            );

            if ($request->has('search') && ! empty($keyword)) {
                $query->where(function ($query) use ($keyword) {
                    $query->whereHas('patient.user', function ($query) use ($keyword) {
                        $query->where('name', 'like', '%'.$keyword.'%');
                    })->orWhereHas('doctor.user', function ($query) use ($keyword) {
                        $query->where('name', 'like', '%'.$keyword.'%');
                    })->orWhere('appointment_mode', 'like', '%'.$keyword.'%')
                        ->orWhere('appointment_date', 'like', '%'.$keyword.'%')
                        ->orWhere('appointment_time', 'like', '%'.$keyword.'%')
                        ->orWhere('duration_minutes', 'like', '%'.$keyword.'%')
                        ->orWhere('reason', 'like', '%'.$keyword.'%')
                        ->orWhere('status', 'like', '%'.$keyword.'%');
                });
            }

            $sortable = [
                'patient.user.name',
                'doctor.user.name',
                'appointment_mode',
                'appointment_date',
                'appointment_time',
                'duration_minutes',
                'reason',
                'status',
                'id',
            ];

            $sort = $request->get('sort', 'id');
            $direction = $request->get('direction', 'desc') === 'asc' ? 'asc' : 'desc';

            if (in_array($sort, $sortable)) {

                switch ($sort) {

                    case 'patient.user.name':
                        $query->join('patients', 'appointments.patient_id', '=', 'patients.id')
                            ->join('users as patient_users', 'patients.user_id', '=', 'patient_users.id')
                            ->orderBy('patient_users.name', $direction)
                            ->select('appointments.*');
                        break;

                    case 'doctor.user.name':
                        $query->join('doctors', 'appointments.doctor_id', '=', 'doctors.id')
                            ->join('users as doctor_users', 'doctors.user_id', '=', 'doctor_users.id')
                            ->orderBy('doctor_users.name', $direction)
                            ->select('appointments.*');
                        break;

                    default:
                        $query->orderBy($sort, $direction);
                }
            } else {
                $query->orderBy('id', 'desc');
            }

            $appointments = $query
                ->paginate($request->get('per_page', 20))
                ->withQueryString();
        }

        return Inertia::render('Admin/Logs/CallLogsList', [
            'appointments' => $appointments,
            'keyword' => $keyword,
        ]);
    }
}
