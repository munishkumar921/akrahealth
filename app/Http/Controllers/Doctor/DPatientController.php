<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientRequest;
use App\Models\Doctor;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\User;
use App\Services\AllPatients;
use App\Services\PatientHistoryService;
use App\Services\PatientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class DPatientController extends Controller
{
    public $patient;

    protected $timelineService;

    /**
     * __construct
     *
     * @param  mixed  $encounter
     * @return void
     */
    public function __construct(patientService $patient, PatientHistoryService $timelineService)
    {
        $this->patient = $patient;
        $this->timelineService = $timelineService;

    }

    /**
     * patient
     */
    public function index(AllPatients $patients): Response
    {
        $patients = $patients->List(request());

        return Inertia::render('Doctors/Patient/AllPatients/Index', [
            'patients' => $patients,
            'search' => request()->get('search', ''),
        ]);
    }

    public function Demographics(): Response|RedirectResponse
    {
        $patient = $this->patient->Demographics();
        if (! $patient) {
            return Redirect::route('doctor.dashboard');
        }

        return Inertia::render('Doctors/Patient/Demographics', [
            'patient' => $patient,
        ]);
    }

    /**
     * updateDemographics
     *
     * @param  mixed  $request
     * @return void
     */
    public function updateDemographics(Request $request)
    {
        return $this->patient->updateDemographics($request);
    }

    /**
     * getPatients
     *
     * @return array
     */
    public function patientList(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string'],
        ]);

        $patients = User::where('role_id', 4)->where('name', 'LIKE', '%'.$request->name.'%')->paginate(request('per_page', paginateLimit()))->withQueryString();

        return response()->json($patients);
    }

    /**
     * patientHistory
     *
     * @return void
     */
    public function patientHistory()
    {
        $doctor = Doctor::where('user_id', Auth::id())->first();
        $selectedPatientId = $doctor ? $doctor->selected_patient_id : null;

        if (! $selectedPatientId) {
            return Redirect::route('doctor.dashboard');
        }

        $patient = Patient::with('user')->findOrFail($selectedPatientId);
        $lastVisit = Encounter::where('patient_id', $selectedPatientId)
            ->orderByDesc('encounter_date')
            ->first();

        // Get timeline events
        $timeline = $this->timelineService->getTimeline($selectedPatientId);

        // Format last visit date
        $lastVisitDate = $lastVisit ?
            ($lastVisit->encounter_date ? \Carbon\Carbon::parse($lastVisit->encounter_date)->format('F jS, Y') : $lastVisit->created_at->format('F jS, Y'))
            : 'No previous visits';

        return Inertia::render('Doctors/Patient/PatientHistory', [
            'patient' => $patient,
            'lastVisit' => $lastVisitDate,
            'timeline' => $timeline,
        ]);
    }

    /**
     * selectPatient
     *
     * @param  mixed  $slug
     * @return void
     */
    public function selectPatient($slug)
    {
        $patient = User::where('slug', $slug)->first();

        if ($patient) {
            $patient_id = $patient->id;
            $message = $patient->name.' has been selected successfully.';
        } else {
            $patient_id = null;
            $message = 'Selected Patient has been released successfully.';
        }

        Doctor::where('user_id', Auth::id())->update([
            'selected_patient_id' => $patient_id,
        ]);

        return Redirect::back()->with('success', $message);
    }

    public function patientSummary(Request $request)
    {
        $doctor = auth()->user()->doctor;
        $patientId = $doctor->selected_patient_id ?? null;
        $user = auth()->user();

        if (! $patientId && $user) {
            // if the logged user is a patient, try to find the related patient record
            if ($user->hasRole('Patient') && property_exists($user, 'id')) {
                $patient = Patient::where('user_id', $user->id)->first();
                $patientId = $patient->id ?? null;
            }
        }

        if (! $patientId) {
            return response()->json([
                'patient' => null,
                'conditions' => [],
                'medications' => [],
                'supplements' => [],
                'allergies' => [],
                'immunizations' => [],
            ]);
        }

        // Eager load common relationships; adjust names if your relations differ
        $patient = Patient::with([
            'conditions',
            'medications',
            'supplements',
            'allergies',
            'immunizations',
            'user',
            'address',
        ])->find($patientId);
        if (! $patient) {
            return response()->json([
                'patient' => null,
                'conditions' => [],
                'medications' => [],
                'supplements' => [],
                'allergies' => [],
                'immunizations' => [],
            ]);
        }

        // Return as array (models + relations will be serialized)
        return response()->json($patient->toArray());
    }

    public function store(PatientRequest $request, PatientService $obj)
    {
        $obj->savePatient($request->all());

        return redirect()->back()->with('success', 'Patient created successfully.');
    }

    public function update(PatientRequest $request, PatientService $obj, $id)
    {
        $data = $request->all();
        $data['id'] = $id;
        $obj->savePatient($data);

        return redirect()->back()->with('success', 'Patient updated successfully.');
    }

    public function registerPatient(Request $request, PatientService $obj)
    {
        $obj->registerPatientPortal($request->all());

        return response()->json(['success' => 'Patient registered to portal send registration code successfully.']);
    }
}
