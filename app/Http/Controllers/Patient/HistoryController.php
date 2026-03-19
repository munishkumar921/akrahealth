<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Encounter;
use App\Models\Patient;
use App\Services\PatientHistoryService;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class HistoryController extends Controller
{
    protected $timelineService;

    public function __construct(PatientHistoryService $timelineService)
    {
        $this->timelineService = $timelineService;
    }

    /**
     * patient
     */
    public function index()
    {
        $patientId = auth()->user()->patient->id ?? null;

        if (! $patientId) {
            return Redirect::route('doctor.dashboard');
        }

        $patient = Patient::with('user')->findOrFail($patientId);
        $lastVisit = Encounter::where('patient_id', $patientId)
            ->orderByDesc('encounter_date')
            ->first();

        // Get timeline events
        $timeline = $this->timelineService->getTimeline($patientId);

        // Format last visit date
        $lastVisitDate = $lastVisit ?
            ($lastVisit->encounter_date ? \Carbon\Carbon::parse($lastVisit->encounter_date)->format('F jS, Y') : $lastVisit->created_at->format('F jS, Y'))
            : 'No previous visits';

        return Inertia::render('Patients/History', [
            'patient' => $patient,
            'lastVisit' => $lastVisitDate,
            'timeline' => $timeline,
        ]);
    }
}
