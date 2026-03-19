<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TriageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $appointments = Appointment::with('patient.user', 'doctor.user', 'doctor.hospital')
            ->where('doctor_id', Auth::user()?->doctor?->id)
            ->whereDate('appointment_date', '>=', Carbon::today())
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        return Inertia::render('Common/Triage/Index', [
            'appointments' => $appointments,
        ]);
    }
}
