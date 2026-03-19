<?php

namespace App\Http\Controllers\VirtualAssistant;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\DoctorAssistant;
use App\Models\DoctorPatient;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\Skill;
use App\Models\State;
use App\Models\User;
use App\Services\AllPatients;
use App\Services\AssistantService;
use App\Services\HirUsService;
use App\Services\PatientService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VirtualAssistantController extends Controller
{
    public function dashboard(Request $request)
    {
        // Basic dashboard data for Biller
        $dashboardData = [
            'messages' => 0,
            'encounters' => ['total' => 0],
            'patients' => ['total' => 0],
            'documents' => ['total' => 0],
            'calendars' => ['total' => 0],
            'reminders' => 0,
            'bills_to_process' => 0,
            'test_results_to_review' => 0,
        ];

        return Inertia::render('VirtualAssistant/Dashboard', [
            'dashboardData' => $dashboardData,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $VirtualAssistant = user::with('address')->where('role_id', '=', '5')->where('status', true)->where('is_email_verified', true);

        if ($request->has('keyword')) {

            $keyword = $request->get('keyword');
            $VirtualAssistant = $VirtualAssistant->where('name', 'Like', '%'.$keyword.'%');
        }
        $VirtualAssistant = $VirtualAssistant->orderBy('id', 'DESC')->paginate(request('per_page', paginateLimit()))->withQueryString();

        return Inertia::render('VirtualAssistant/VirtualAssistantList', [
            'assistants' => $VirtualAssistant,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function show(string $slug)
    {
        $virtualAssistant = user::with(['address', 'DoctorAssistant'])->where('slug', '=', $slug)->first();
        $doctorAssistant = DoctorAssistant::with(['user'])->where('doctor_assistant_id', $virtualAssistant->id)->where('is_accepted', true)->first();
        $results = Skill::where('id', json_decode($virtualAssistant['skill_id']))->get();
        $assistantSkills = [];
        foreach ($results as $data) {
            $assistantSkills[] =
                $data->skill;
        }
        $skills = Skill::orderBy('skill', 'ASC')->pluck('skill')->toArray();
        $languages = __('language.isoLangs');
        $lang = $languages;
        $language = [];
        foreach ($lang as $data) {
            $language[] =
            $data['name'];
        }

        return Inertia::render('VirtualAssistant/VirtualAssistantDetail', [
            'assistant' => $virtualAssistant,
            'AssistantSkills' => $assistantSkills,
            'slug' => $slug,
            'language' => $language,
            'skills' => $skills,
            'doctorAssistant' => $doctorAssistant,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function hir_us(Request $request, HirUsService $obj)
    {
        $obj->store($request->all());

        return back()->with('success', 'Your  request has been sent successfully.Thank you');
    }

    public function is_accepted($token)
    {

        $accepted = DoctorAssistant::where('token', $token)->first();

        if (! is_null($accepted)) {
            $accepted->is_accepted = 1;
            $accepted->save();

            return redirect()->route('home')->with('success', 'Thank you');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    public function store(Request $request, AssistantService $obj): RedirectResponse
    {
        $obj->store($request);

        return back()->with('success', 'Your detail has been saved successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function searchPatient()
    {
        $search = request('q');

        $data = DoctorPatient::query()
            ->where('doctor_id', auth()->user()->doctor->id)
            ->whereHas('patient', function ($query) use ($search) {
                $query->where('is_active', true);
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('first_name', 'like', '%'.$search.'%')
                            ->orWhere('last_name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%')
                            ->orWhere('mobile', 'like', '%'.$search.'%');
                    });
                }
            })
            ->with('patient.user')
            ->latest('id')
            ->get()
            ->unique('patient_id')   // ✅ unique patients
            ->values()
            ->map(function ($item) {

                $user = $item->patient->user;
                $patient = $item->patient;

                return [
                    'id' => $item->patient_id,
                    'name' => trim($patient->first_name ?? $patient->user->first_name.' '.$patient->last_name ?? $patient->user->last_name),
                    'email' => $user->email ?? $patient->email ?? null,
                    'mobile' => $user->mobile ?? $patient->mobile ?? null,
                ];
            });

        return response()->json($data);
    }

    /**
     * selectPatient
     *
     * @param  mixed  $patient_id
     * @return void
     */
    public function selectPatient($patient_id)
    {
        $doctor = auth()->user()->doctor;

        if (! $doctor) {
            return redirect()->back()->with('error', 'No doctor record found.');
        }

        if ($patient_id == 'empty') {
            $patient_id = null;
        }

        // Update the virtual assistant's (who is a doctor) selected patient
        $doctor->update([
            'selected_patient_id' => $patient_id,
        ]);

        return redirect()->back()->with('success', 'Patient selected successfully.');
    }

    /**
     * patientSummary
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function patientSummary(Request $request)
    {
        $doctor = auth()->user()->doctor;

        if (! $doctor) {
            return response()->json([
                'patient' => null,
                'conditions' => [],
                'medications' => [],
                'supplements' => [],
                'allergies' => [],
                'immunizations' => [],
            ]);
        }

        $patientId = $doctor->selected_patient_id ?? null;

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
        $patient = \App\Models\Patient::with([
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

    /**
     * Display patients for the virtual assistant (who operates as their own doctor)
     *
     * @return Response
     */
    public function patients(AllPatients $patients)
    {
        $patientsList = $patients->List(request());

        return Inertia::render('Doctors/Patient/AllPatients/Index', [
            'patients' => $patientsList,
            'search' => request()->get('search', ''),
        ]);
    }

    /**
     * Show patient demographics for virtual assistant
     *
     * @return \Inertia\Response|\Illuminate\Http\RedirectResponse
     */
    public function demographics(PatientService $patientService)
    {
        $patient = $patientService->Demographics();
        if (! $patient) {
            return redirect()->route('assistant.dashboard')->with('error', 'No patient selected.');
        }

        return Inertia::render('Doctors/Patient/Demographics', [
            'patient' => $patient,
            'countries' => Country::select('name')->distinct()->orderBy('name')->pluck('name'),
            'states' => State::select('name')->distinct()->orderBy('name')->pluck('name'),
        ]);
    }

    /**
     * Show patient history for virtual assistant
     *
     * @return \Inertia\Response|\Illuminate\Http\RedirectResponse
     */
    public function patientHistory()
    {
        $doctor = auth()->user()->doctor;

        if (! $doctor) {
            return redirect()->route('assistant.dashboard')->with('error', 'No doctor record found.');
        }

        $selectedPatientId = $doctor->selected_patient_id ?? null;

        if (! $selectedPatientId) {
            return redirect()->route('assistant.dashboard')->with('error', 'No patient selected.');
        }

        $patient = Patient::with('user')->findOrFail($selectedPatientId);
        $lastVisit = Encounter::where('patient_id', $selectedPatientId)
            ->orderByDesc('encounter_date')
            ->first();

        // Get timeline events (reuse the logic from DPatientController)
        $timeline = $this->timeline($selectedPatientId);

        // Format last visit date - use safe parsing
        $lastVisitDate = 'No previous visits';
        if ($lastVisit) {
            $parsedDate = $this->safeDateParse($lastVisit->encounter_date);
            if ($parsedDate) {
                $lastVisitDate = Carbon::parse($parsedDate)->format('F jS, Y');
            } elseif ($lastVisit->created_at) {
                $lastVisitDate = $lastVisit->created_at->format('F jS, Y');
            }
        }

        return Inertia::render('Doctors/Patient/PatientHistory', [
            'patient' => $patient->user,
            'lastVisit' => $lastVisitDate,
            'timeline' => $timeline,
        ]);
    }

    /**
     * Helper function to safely parse a date
     *
     * @param  mixed  $date
     * @return string|null
     */
    private function safeDateParse($date)
    {
        if (empty($date)) {
            return null;
        }

        // Skip invalid values like "No", "yes", empty strings, etc.
        if (! is_string($date) || strlen($date) < 4) {
            return null;
        }

        // Skip common non-date string values
        if (in_array(strtolower($date), ['no', 'yes', 'n', 'y', 'null', 'undefined'])) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Generate timeline for patient history
     *
     * @param  int  $selectedPatientId
     * @return array
     */
    protected function timeline($selectedPatientId)
    {
        $timeline = [];

        // Get encounters with related data
        $encounters = Encounter::with(['prescriptions', 'supplements', 'doctor.user', 'assessment'])
            ->where('patient_id', $selectedPatientId)
            ->orderBy('encounter_date', 'desc')
            ->get();

        foreach ($encounters as $encounter) {
            // Encounter event - use safe date parsing
            $encounterDate = $this->safeDateParse($encounter->encounter_date);
            $timeline[] = [
                'date' => $encounterDate ? $encounterDate : $encounter->created_at->format('Y-m-d'),
                'title' => 'Encounter: '.($encounter->chief_complaint ?: 'Visit'),
                'description' => $encounter->chief_complaint ?: 'Patient visit',
                'icon' => 'fa-solid fa-stethoscope',
                'iconColor' => 'bg-green-500 text-white',
                'type' => 'encounter',
                'id' => $encounter->id,
                'url' => route('doctor.encounters.show', $encounter->id),
            ];

            // Assessment
            if ($encounter->assessment) {
                $assessmentInfo = $encounter->assessment;
                $assessmentArr = $this->array_assessment();
                $assessmentDescription = 'Assessment:';

                // Loop through dynamic assessment_1 ... assessment_12 fields
                for ($i = 1; $i <= 12; $i++) {
                    $col = "assessment_{$i}";
                    $value = $assessmentInfo->{$col} ?? null;

                    if (! empty($value)) {
                        $assessmentDescription .= " $value ";
                    }
                }

                // Loop through structured fields defined in array_assessment()
                foreach ($assessmentArr as $key => $labels) {
                    $value = $assessmentInfo->{$key} ?? null;

                    if (! empty($value)) {
                        $label = ($encounter->encounter_template === 'standardmtm')
                            ? $labels['standardmtm']
                            : $labels['standard'];

                        $assessmentDescription .= " $label ";
                        $assessmentDescription .= nl2br(e($value));
                        $assessmentDescription .= "\n";
                    }
                }

                // Add to timeline
                $assessmentDate = $this->safeDateParse($encounter->encounter_date);
                $timeline[] = [
                    'date' => $assessmentDate ? $assessmentDate : $encounter->created_at->format('Y-m-d'),
                    'title' => 'Assessment Summary',
                    'description' => $assessmentDescription,
                    'icon' => 'fa-solid fa-clipboard-check',
                    'iconColor' => 'bg-green-500 text-white',
                    'type' => 'assessment',
                    'id' => $encounter->id,
                    'url' => route('doctor.encounters.show', $encounter->id),
                ];
            }

            // Prescriptions
            foreach ($encounter->prescriptions as $prescription) {
                if ($prescription->medication) {
                    $rxDate = $this->safeDateParse($encounter->encounter_date);
                    $timeline[] = [
                        'date' => $rxDate ? $rxDate : $encounter->created_at->format('Y-m-d'),
                        'title' => 'Prescribed Medication',
                        'description' => "{$prescription->medication} {$prescription->dosage}{$prescription->dosage_unit}, {$prescription->route}, {$prescription->frequency} for ".($prescription->reason ?: 'treatment'),
                        'icon' => 'fa-solid fa-eyedropper',
                        'iconColor' => 'bg-yellow-500 text-white',
                        'type' => 'prescription',
                        'id' => $prescription->id,
                        'url' => route('doctor.encounters.show', $encounter->id),
                    ];
                }
            }

            // Supplements
            foreach ($encounter->supplements as $supplement) {
                if ($supplement->supplement) {
                    $suppDate = $this->safeDateParse($encounter->encounter_date);
                    $timeline[] = [
                        'date' => $suppDate ? $suppDate : $encounter->created_at->format('Y-m-d'),
                        'title' => 'Prescribed Supplement',
                        'description' => "{$supplement->supplement} {$supplement->dosage}{$supplement->dosage_unit}, {$supplement->route}, {$supplement->frequency}",
                        'icon' => 'fa-solid fa-capsules',
                        'iconColor' => 'bg-purple-500 text-white',
                        'type' => 'supplement',
                        'id' => $supplement->id,
                        'url' => route('doctor.supplements.index'),
                    ];
                }
            }
        }

        // Medications
        $medications = \App\Models\Medication::where('patient_id', $selectedPatientId)
            ->orderBy('date_active', 'desc')
            ->get();

        foreach ($medications as $medication) {
            $dateInactive = $this->safeDateParse($medication->date_inactive);
            $dateActive = $this->safeDateParse($medication->date_active);

            if ($dateInactive) {
                $timeline[] = [
                    'date' => $dateInactive,
                    'title' => 'Medication Stopped',
                    'description' => "{$medication->medication} {$medication->dosage}{$medication->dosage_unit}, {$medication->route}, {$medication->frequency}",
                    'icon' => 'fa-solid fa-flask',
                    'iconColor' => 'bg-danger text-white',
                    'type' => 'medication_stopped',
                    'id' => $medication->id,
                ];
            } elseif ($dateActive) {
                $timeline[] = [
                    'date' => $dateActive,
                    'title' => 'Medication Started',
                    'description' => "{$medication->medication} {$medication->dosage}{$medication->dosage_unit}, {$medication->route}, {$medication->frequency}",
                    'icon' => 'fa-solid fa-pills',
                    'iconColor' => 'bg-green-500 text-white',
                    'type' => 'medication_started',
                    'id' => $medication->id,
                    'url' => route('doctor.medications.index'),
                ];
            }
        }

        // Issues
        $issues = \App\Models\Issue::where('patient_id', $selectedPatientId)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($issues as $issue) {
            $title = match ($issue->type) {
                'Problem' => 'New Problem',
                'MedicalHistory' => 'New Medical Event',
                'SurgicalHistory' => 'New Surgical Event',
                default => 'Issue',
            };

            // Skip if date_active is empty or invalid - use safe parsing
            $dateActive = $this->safeDateParse($issue->date_active);
            if (! $dateActive) {
                continue;
            }

            $timeline[] = [
                'date' => $dateActive,
                'title' => $title,
                'description' => $issue->issue,
                'icon' => 'fa-solid fa-notes-medical',
                'iconColor' => 'bg-danger text-white',
                'type' => 'issue',
                'id' => $issue->id,
                'url' => route('doctor.conditions.index'),
            ];
        }

        // Immunizations
        $immunizations = \App\Models\Immunization::where('patient_id', $selectedPatientId)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($immunizations as $immunization) {
            // Skip if date is empty or invalid - use safe parsing
            $immDate = $this->safeDateParse($immunization->date);
            if (! $immDate) {
                continue;
            }
            $timeline[] = [
                'date' => $immDate,
                'title' => 'Immunization Given',
                'description' => "{$immunization->immunization} ({$immunization->route})",
                'icon' => 'fa-solid fa-syringe',
                'iconColor' => 'bg-yellow-500 text-white',
                'type' => 'immunization',
                'id' => $immunization->id,
                'url' => route('doctor.immunizations.index'),
            ];
        }

        // Allergies
        $allergies = \App\Models\Allergy::where('patient_id', $selectedPatientId)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($allergies as $allergy) {
            $date = $allergy->date_active ?: $allergy->date_inactive;
            // Skip if date is empty or invalid - use safe parsing
            $allergyDate = $this->safeDateParse($date);
            if (! $allergyDate) {
                continue;
            }
            $timeline[] = [
                'date' => $allergyDate,
                'title' => 'Allergy Update',
                'description' => "{$allergy->note} {$allergy->allergies_medicine}, {$allergy->allergies_reaction}, {$allergy->allergies_severity}",
                'icon' => 'fa-solid fa-allergies',
                'iconColor' => 'bg-yellow-500 text-white',
                'type' => 'allergy',
                'id' => $allergy->id,
                'url' => route('doctor.allergies.index'),
            ];
        }

        // Lab orders
        $labOrders = \App\Models\Order::where('patient_id', $selectedPatientId)
            ->whereNotNull('labs')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($labOrders as $order) {
            $timeline[] = [
                'date' => $order->created_at->format('Y-m-d'),
                'title' => 'Lab Order',
                'description' => $order->labs ?: 'Laboratory test ordered',
                'icon' => 'fa-solid fa-flask',
                'iconColor' => 'bg-blue-500 text-white',
                'type' => 'lab_order',
                'id' => $order->id,
                'url' => route('doctor.orders.index'),
            ];
        }

        // Radiology orders
        $radiologyOrders = \App\Models\Order::where('patient_id', $selectedPatientId)
            ->whereNotNull('radiology')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($radiologyOrders as $order) {
            $timeline[] = [
                'date' => $order->created_at->format('Y-m-d'),
                'title' => 'Radiology Order',
                'description' => $order->radiology ?: 'Imaging study ordered',
                'icon' => 'fa-solid fa-x-ray',
                'iconColor' => 'bg-indigo-500 text-white',
                'type' => 'radiology_order',
                'id' => $order->id,
                'url' => route('doctor.orders.index'),
            ];
        }

        // Messages
        $messages = \App\Models\Message::where('patient_id', $selectedPatientId)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($messages as $message) {
            $timeline[] = [
                'date' => $message->created_at->format('Y-m-d'),
                'title' => 'Message',
                'description' => substr($message->message, 0, 500),
                'icon' => 'fa-solid fa-message',
                'id' => $message->id,
                'url' => route('doctor.messages.index'),
            ];
        }

        // Sort newest first - filter out invalid dates first
        $timeline = array_filter($timeline, function ($item) {
            return ! empty($item['date']) && is_string($item['date']) && strlen($item['date']) >= 4;
        });

        usort($timeline, fn ($a, $b) => strtotime($b['date']) <=> strtotime($a['date']));

        return $timeline;
    }

    /**
     * Assessment array configuration
     *
     * @return array
     */
    protected function array_assessment()
    {
        return [
            'assessment_other' => [
                'standardmtm' => 'SOAP Note',
                'standard' => 'Additional Diagnoses',
            ],
            'assessment_ddx' => [
                'standardmtm' => 'MAP2',
                'standard' => 'Differential Diagnoses Considered',
            ],
            'assessment_notes' => [
                'standardmtm' => 'Pharmacist Note',
                'standard' => 'Assessment Discussion',
            ],
        ];
    }

    /**
     * Get the doctor user for the virtual assistant (virtual assistants have their own doctor functionality)
     *
     * @return \App\Models\User|null
     */
    protected function getAssignedDoctor()
    {
        // Virtual assistants are a separate role with same permissions as doctors
        // They operate as their own doctor user
        return auth()->user();
    }

    /**
     * Virtual assistants operate as their own doctors, no auth switching needed
     *
     * @return \App\Models\User|null
     */
    protected function switchToDoctor()
    {
        // Virtual assistants are separate doctors, no switching needed
        return auth()->user();
    }

    /**
     * Proxy conditions to Doctor controller
     */
    public function conditions(Request $request)
    {
        return app(\App\Http\Controllers\Doctor\DConditionController::class)->index($request);
    }

    /**
     * Proxy medications to Doctor controller
     */
    public function medications(Request $request)
    {
        return app(\App\Http\Controllers\Doctor\DMedicationController::class)->index($request);
    }

    /**
     * Proxy supplements to Doctor controller
     */
    public function supplements(Request $request)
    {
        return app(\App\Http\Controllers\Doctor\DSupplementController::class)->index($request);
    }

    /**
     * Proxy immunizations to Doctor controller
     */
    public function immunizations(Request $request)
    {
        return app(\App\Http\Controllers\Doctor\DImmunizationController::class)->index($request);
    }

    /**
     * Proxy allergies to Doctor controller
     */
    public function allergies(Request $request)
    {
        return app(\App\Http\Controllers\Doctor\DAllergyController::class)->index($request);
    }

    /**
     * Proxy documents to Doctor controller
     */
    public function documents(Request $request)
    {
        return app(\App\Http\Controllers\Doctor\DDocumentsController::class)->index($request);
    }

    /**
     * Proxy results to Doctor controller
     */
    public function results(Request $request)
    {
        return app(\App\Http\Controllers\Doctor\DResultsController::class)->index($request);
    }

    /**
     * Proxy forms to Doctor controller
     */
    public function forms(Request $request)
    {
        return app(\App\Http\Controllers\Doctor\DFormsController::class)->index($request);
    }

    /**
     * Proxy orders to Doctor controller
     */
    public function orders(Request $request)
    {
        return app(\App\Http\Controllers\Doctor\DOrdersController::class)->index($request);
    }

    /**
     * Proxy billing to Doctor controller
     */
    public function billing(Request $request)
    {
        return app(\App\Http\Controllers\Doctor\DBillingController::class)->index($request);
    }

    /**
     * Proxy insurance to Doctor controller
     */
    public function insurance(Request $request)
    {
        return app(\App\Http\Controllers\Doctor\DInsuranceController::class)->index($request);
    }

    /**
     * Proxy coordination to Doctor controller
     */
    public function coordination(Request $request)
    {
        return app(\App\Http\Controllers\Doctor\DCoordinationController::class)->index($request);
    }

    /**
     * Proxy finance bills to submit to Common controller
     */
    public function financeBillsToSubmit(Request $request)
    {
        return app(\App\Http\Controllers\Common\FinancialController::class)->bills_to_submit($request);
    }

    /**
     * Proxy store patient to Doctor controller
     */
    public function storePatient(Request $request)
    {
        return app(\App\Http\Controllers\Doctor\DPatientController::class)->store($request, app(\App\Services\PatientService::class));
    }

    /**
     * Proxy register patient to Doctor controller
     */
    public function registerPatient(Request $request)
    {
        return app(\App\Http\Controllers\Doctor\DPatientController::class)->registerPatient($request, app(\App\Services\PatientService::class));
    }
}
