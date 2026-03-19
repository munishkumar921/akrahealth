<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\SocialHistoryRequest;
use App\Mail\ApiRegisterMail;
use App\Models\Allergy;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Encounter;
use App\Models\Immunization;
use App\Models\Issue;
use App\Models\Message;
use App\Models\Order;
use App\Models\OtherHistory;
use App\Models\Patient;
use App\Models\PatientSupplement;
use App\Models\Prescription;
use App\Models\SocialHistory;
use App\Models\Test;
use App\Models\UmaInvitation;
use App\Services\ConditionService;
use App\Services\EncounterService;
use App\Services\PatientService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use SoapBox\Formatter\Formatter;

class PatientController extends Controller
{
    public $encounter;

    public $patient;

    /**
     * __construct
     *
     * @param  mixed  $encounter
     * @return void
     */
    public function __construct(EncounterService $encounter, patientService $patient)
    {
        $this->encounter = $encounter;
        $this->patient = $patient;
    }

    /**
     * dashboard
     *
     * @return void
     */
    public function dashboard()
    {
        $patientId = auth()->user()->patient->id ?? null;

        $appointmentsQuery = Appointment::where('patient_id', $patientId);
        $appointmentsTotal = $appointmentsQuery->count();
        $appointmentsPending = $appointmentsQuery->where('status', 'pending')->count();
        $appointmentsCompleted = $appointmentsQuery->where('status', 'completed')->count();

        $counts = [
            'messages' => Message::where('patient_id', $patientId)->where('read', false)->count(),
            'encounters_to_complete' => Encounter::where('patient_id', $patientId)->whereNull('date_signed')->count(), // Encounters not signed are "to complete"
            'telephone_messages' => Encounter::where('patient_id', $patientId)->where('encounter_type', 'phone')->count(),
            'calendar' => [
                'total' => $appointmentsTotal,
                'pending' => $appointmentsPending,
                'completed' => $appointmentsCompleted,
            ],
            'reminders' => Appointment::where('patient_id', $patientId)->where('appointment_date', '>=', now()->toDateString())->count(),
            'documents' => \App\Models\Document::where('patient_id', $patientId)->count(),
            'bills' => \App\Models\Billing::where('patient_id', $patientId)->count(),
            'test_results' => \App\Models\Test::where('patient_id', $patientId)->count(),
        ];

        return Inertia::render('Patients/Dashboard', [
            'counts' => $counts,
        ]);
    }

    /**
     * demographics
     *
     * @return void
     */
    public function demographics()
    {
        $patient = $this->patient->Demographics();

        return Inertia::render('Patients/Demographics', [
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
     * doctor List
     */
    public function doctorsList(Request $request, $speciality = null)
    {
        return [
            Doctor::get(),
        ];
    }

    /**
     * doctor List
     */
    public function doctorList(Request $request, $speciality = null)
    {
        return [
            Doctor::get(),
        ];
    }

    /**
     * profile
     *
     * @return void
     */
    public function profile()
    {
        $patient = Patient::with(['guardian.address', 'address'])->find(auth()->user()->patient->id ?? null);
        $patientId = $patient->id ?? null;

        // Messages
        $messages = \App\Models\Message::where('patient_id', $patientId)->latest()->take(5)->get();

        // Documents
        $documents = \App\Models\Document::where('patient_id', $patientId)->latest()->take(5)->get();

        // Orders
        $orders = \App\Models\Order::with('patient.user', 'doctor.user', 'encounter')->where('patient_id', $patientId)->latest()->take(5)->get();

        // Family History - Flatten and normalize the data like Doctor's side does
        $familyHistory = \App\Models\OtherHistory::where('patient_id', $patientId)
            ->latest()
            ->take(5)
            ->get()
            ->flatMap(function ($history) {
                // Parse YAML if not already parsed
                $oh_fh = $history->oh_fh;
                if (! empty($oh_fh) && is_string($oh_fh)) {
                    try {
                        $formatter = \SoapBox\Formatter\Formatter::make($oh_fh, \SoapBox\Formatter\Formatter::YAML);
                        $oh_fh = $formatter->toArray();
                    } catch (\Exception $e) {
                        $oh_fh = [];
                    }
                }

                // If oh_fh is not an array, return empty array
                if (! is_array($oh_fh)) {
                    return [];
                }

                // Flatten: map each item in oh_fh to a separate record
                return collect($oh_fh)->map(function ($fh, $index) use ($history) {
                    // Normalize field names (same as Doctor's side)
                    $normalized = [
                        'id' => $history->id,
                        'parent_id' => $history->id,
                        'index' => $index,
                        'name' => $fh['name'] ?? $fh['Name'] ?? '',
                        'relationship' => $fh['relationship'] ?? $fh['Relationship'] ?? '',
                        'living_status' => $fh['living_status'] ?? $fh['Status'] ?? $fh['status'] ?? '',
                        'gender' => $fh['gender'] ?? $fh['Gender'] ?? '',
                        'dob' => $fh['dob'] ?? $fh['Date of Birth'] ?? $fh['date_of_Birth'] ?? '',
                        'marital_status' => $fh['marital_status'] ?? $fh['Marital Status'] ?? '',
                        'mother' => $fh['mother'] ?? $fh['Mother'] ?? '',
                        'father' => $fh['father'] ?? $fh['Father'] ?? '',
                        'medical_history' => $fh['medical_history'] ?? $fh['Medical'] ?? $fh['medical'] ?? '',
                        'created_at' => $history->created_at,
                        'updated_at' => $history->updated_at,
                    ];

                    return $normalized;
                });
            })
            ->filter(function ($item) {
                // Filter out empty records
                return ! empty($item['name']) || ! empty($item['relationship']) || ! empty($item['medical_history']);
            })
            ->take(5)
            ->values();

        // Financial Data
        $financialData = \App\Models\Billing::where('patient_id', $patientId)->latest()->take(5)->get();

        // Encounters
        $encounters = \App\Models\Encounter::with('patient.user', 'doctor.user', 'appointment')->where('patient_id', $patientId)->latest()->take(5)->get();

        // History (Encounters for timeline) - formatted for PatientTimeline component
        $history = \App\Models\Encounter::with('patient.user', 'doctor.user')->where('patient_id', $patientId)->orderBy('created_at', 'desc')->take(10)->get()->map(function ($encounter) {
            // Determine icon based on encounter type
            $icon = 'fa-solid fa-stethoscope';
            $iconColor = 'bg-green-500';

            switch ($encounter->encounter_type) {
                case 'phone':
                    $icon = 'fa-solid fa-phone';
                    $iconColor = 'bg-blue-500';
                    break;
                case 'virtual':
                    $icon = 'fa-solid fa-video';
                    $iconColor = 'bg-purple-500';
                    break;
                case 'standardpsych':
                case 'standardpsych1':
                    $icon = 'fa-solid fa-brain';
                    $iconColor = 'bg-pink-500';
                    break;
                case 'clinicalsupport':
                    $icon = 'fa-solid fa-hands-helping';
                    $iconColor = 'bg-yellow-500';
                    break;
                case 'standardmtm':
                    $icon = 'fa-solid fa-pills';
                    $iconColor = 'bg-orange-500';
                    break;
            }

            return [
                'id' => $encounter->id,
                'date' => Carbon::parse($encounter->created_at)->format('M d, Y'),
                'title' => $encounter->encounter_type ? ucfirst($encounter->encounter_type).' Encounter' : 'Medical Encounter',
                'description' => $encounter->chief_complaint ?? 'Visit with Dr. '.($encounter->doctor->user->name ?? 'Doctor'),
                'icon' => $icon,
                'iconColor' => $iconColor,
                'url' => route('patient.encounters.show', $encounter->id),
                'doctor_name' => $encounter->doctor->user->name ?? 'Doctor',
                'created_at' => $encounter->created_at,
            ];
        });

        // Medical Records
        $medicalRecords = \App\Models\Issue::where('patient_id', $patientId)->latest()->take(5)->get();

        return \Inertia\Inertia::render('Patients/Profile/Index', [
            'patient' => $patient,
            'messages' => $messages,
            'documents' => $documents,
            'orders' => $orders,
            'familyHistory' => $familyHistory,
            'financialData' => $financialData,
            'encounters' => $encounters,
            'history' => $history,
            'medicalRecords' => $medicalRecords,
        ]);
    }

    /**
     * updateProfile
     *
     * @return RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        return $this->patient->updateDemographics($request);
    }

    public function summary(Request $request)
    {
        $patientId = auth()->user()->patient->id ?? null;

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

        return response()->json($patient->toArray());
    }

    public function conditions(Request $request)
    {
        $issues = Issue::where('patient_id', auth()->user()->patient->id ?? null);
        if ($request->has('search')) {
            $keyword = $request->get('search');

            $issues = $issues->where(function ($query) use ($keyword) {
                $query->where('issue', 'like', '%'.$keyword.'%')
                    ->orWhere('type', 'like', '%'.$keyword.'%')
                    ->orWhere('notes', 'like', '%'.$keyword.'%')
                    ->orWhere('reconcile', 'like', '%'.$keyword.'%')
                    ->orWhere('rcopia_sync', 'like', '%'.$keyword.'%');
            });
        }
        $encounters = Encounter::where('patient_id', auth()->user()->patient->id ?? null)->latest()->first();
        $data = $issues->paginate(request('per_page', paginateLimit()))->withQueryString();

        return Inertia::render('Patients/Conditions', [
            'issues' => $data,
            'keyword' => $request->get('search'),
            'encounters' => $encounters,
        ]);
    }

    /**
     * moveCondition
     *
     * @param  mixed  $id
     * @param  mixed  $type
     */
    public function moveCondition(ConditionService $obj, string $id, string $type): RedirectResponse
    {
        //
        $obj->moveCondition($id, $type);

        return back()->with('success', 'The condition has been updated successfully.');
    }

    public function issues(Request $request)
    {
        $issues = Issue::where('patient_id', auth()->user()->id ?? null);
        if ($request->has('search')) {
            $keyword = $request->get('search');

            $issues = $issues->where(function ($query) use ($keyword) {
                $query->where('issue', 'like', '%'.$keyword.'%')
                    ->orWhere('status', 'like', '%'.$keyword.'%');
            });
        }
        $issues = $issues->paginate(request('per_page', paginateLimit()))->withQueryString();

        return Inertia::render('Patients/Issues', [
            'issues' => $issues,
        ]);
    }

    public function medications(Request $request)
    {
        $medication = Prescription::where('patient_id', auth()->user()->patient->id ?? null);
        $keyword = '';
        if ($request->has('keyword')) {
            $keyword = $request->get('keyword');
            $medication = $medication->where('medication', 'Like', '%'.$keyword.'%');
        }

        $medications = $medication->paginate(request('per_page', paginateLimit()))->withQueryString();

        return Inertia::render('Patients/Medications', [
            'medications' => $medications,

            'keyword' => $keyword,
        ]);
    }

    public function supplements(Request $request)
    {
        $supplements = PatientSupplement::where('patient_id', auth()->user()->patient->id ?? null);
        if ($request->has('search')) {
            $keyword = $request->get('search');
            $supplements = $supplements->where('supplement', 'Like', '%'.$keyword.'%');
        }
        $supplements = $supplements->paginate(request('per_page', paginateLimit()))->withQueryString();

        return Inertia::render('Patients/Supplements', [
            'supplements' => $supplements,
            'keyword' => $request->get('search'),
        ]);
    }

    public function immunizations(Request $request)
    {
        $immunization = Immunization::where('patient_id', auth()->user()->patient->id ?? null);
        if ($request->has('keyword')) {
            $keyword = $request->get('keyword');
            $immunization = $immunization->where('immunization', 'Like', '%'.$keyword.'%');
        }
        $immunizations = $immunization->paginate(request('per_page', paginateLimit()))->withQueryString();

        return Inertia::render('Patients/Immunizations', [
            'immunizations' => $immunizations,
            'keyword' => $request->get('keyword'),
        ]);
    }

    public function allergies(Request $request)
    {
        $allergies = Allergy::where('patient_id', auth()->user()->patient->id ?? null);
        if ($request->has('search')) {

            $keyword = $request->get('search');
            $allergies = $allergies->where('allergies_medicine', 'Like', '%'.$keyword.'%');
        }
        $allergies = $allergies->paginate(request('per_page', paginateLimit()))->withQueryString();

        return Inertia::render('Patients/Allergies', [
            'allergies' => $allergies,
            'keyword' => $request->get('search'),
        ]);
    }

    public function orders(Request $request)
    {
        $orders = Order::with('patient.user', 'doctor.user', 'encounter')->where('patient_id', auth()->user()->patient->id ?? null)->get();
        $patientId = auth()->user()->patient->id ?? null;
        $patient = \App\Models\Patient::with('user')->find($patientId);

        return Inertia::render('Patients/Orders', [
            'orders' => $orders,
            'patient' => $patient,
        ]);
    }

    /**
     * Display order details
     */
    public function orderShow(string $id)
    {
        $patientId = auth()->user()->patient->id ?? null;

        $order = Order::with('patient.user', 'doctor.user', 'encounter')
            ->where('id', $id)
            ->where('patient_id', $patientId)
            ->firstOrFail();

        return Inertia::render('Patients/Orders/Show', [
            'order' => $order,
        ]);
    }

    public function encounters(Request $request)
    {
        $keyword = $request->get('search', ''); // get keyword or empty string
        $status = $request->get('status');

        $query = Encounter::with('patient.user', 'doctor.user', 'appointment');
        if (auth()->user()->hasRole('Doctor')) {
            $query->where('patient_id', auth()->user()->doctor->selected_patient_id ?? null);
        } else {
            $query->where('patient_id', auth()->user()->patient->id ?? null);
        }

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                // Search in Encounter fields
                $q->where('chief_complaint', 'like', '%'.$keyword.'%');

                // Search in Patient name
                $q->orWhereHas('patient.user', function ($q2) use ($keyword) {
                    $q2->where('name', 'like', '%'.$keyword.'%');
                });

                // Search in Doctor name
                $q->orWhereHas('doctor.user', function ($q3) use ($keyword) {
                    $q3->where('name', 'like', '%'.$keyword.'%');
                });
            });
        }

        if ($status) {
            // Since encounters table doesn't have status column, filter by date_signed instead
            if ($status === 'completed') {
                $query->whereNotNull('date_signed');
            } elseif ($status === 'pending') {
                $query->whereNull('date_signed');
            }
        }

        $encounters = $query->orderBy('created_at', 'DESC')->paginate(request('per_page', paginateLimit()))->withQueryString();

        return Inertia::render('Patients/Encounters', [
            'encounters' => $encounters,
            'keyword' => $keyword,
            'status' => $status,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function encounterShow(string $id)
    {
        $data = $this->encounter->getFormData($id);
        $data['encounter'] = Encounter::with([
            'patient.user',
            'doctor.user',
            'appointment.patient.user',
            'appointment.doctor.user',
            'patientIllnessHistory',
            'reviewOfSystem',
            'vital',
            'assessment',
            'plan',
            'physicalExamination',
            'prescriptions',
            'supplements',
            'labOrders',
            'radiologyOrders',
            'cardOrders',
            'images',
            'photos',
            'procedures',
            'billingCore',
            'billing',
            'referral.doctor.user',
        ])->where('id', $id)->first();

        return Inertia::render('Doctors/Patient/Encounters/ViewEncounter', [
            'data' => $data,
        ]);
    }

    public function results(Request $request)
    {
        $results = Test::where('patient_id', auth()->user()->patient->id ?? null)
            ->when($request->filled('search'), function ($q) use ($request) {
                $keyword = '%'.$request->get('search').'%';
                $q->where('name', 'like', $keyword)
                    ->orWhere('code', 'like', 'like', $keyword)
                    ->orWhere('result', 'like', $keyword);
            })
            ->paginate(request('per_page', paginateLimit()))->withQueryString();

        return Inertia::render('Patients/Results/Index', [
            'results' => $results,
            'keyword' => $request->get('search'),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Inertia::render('Patients/Results/Show', [
            'result' => Test::with('patient.user', 'doctor.user')->findOrFail($id),

        ]);
    }

    public function billing(Request $request)
    {
        $sort = request('sort', 'appointment_date');
        $direction = request('direction', 'desc') === 'asc' ? 'asc' : 'desc';
        $search = request('search');

        $query = Appointment::query()
            ->with(['encounter'])
            ->select('appointments.*')
            ->with(['invoice', 'patient.user', 'doctor.user'])
            ->where('appointments.patient_id', auth()->user()?->patient?->id)
            ->whereHas('invoice');

        /* Appointment table sortable columns */
        $sortable = [
            'appointment_date' => 'appointments.appointment_date',
            'fee_amount' => 'appointments.fee_amount',
            'total_amount' => 'appointments.total_amount',
            'payment_status' => 'appointments.payment_status',
        ];

        /* search */
        if ($search) {

            $query->where(function ($q) use ($search) {

                $q->where('appointments.appointment_date', 'like', "%{$search}%")
                    ->orWhere('appointments.fee_amount', 'like', "%{$search}%")
                    ->orWhere('appointments.payment_status', 'like', "%{$search}%")

                    /* Invoice search */
                    ->orWhereHas('invoice', function ($qq) use ($search) {
                        $qq->where('invoice_number', 'like', "%{$search}%")
                            ->orWhere('discount_amount', 'like', "%{$search}%")
                            ->orWhere('tax_amount', 'like', "%{$search}%");
                    })

                    /* Doctor search */
                    ->orWhereHas('doctor.user', function ($qq) use ($search) {
                        $qq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        /* sorting */

        if (in_array($sort, ['invoice.invoice_number', 'invoice.discount_amount', 'invoice.tax_amount'])) {

            $query->leftJoin('invoices', 'invoices.appointment_id', '=', 'appointments.id');

            $invoiceColumns = [
                'invoice.invoice_number' => 'invoices.invoice_number',
                'invoice.discount_amount' => 'invoices.discount_amount',
                'invoice.tax_amount' => 'invoices.tax_amount',
            ];

            $query->orderBy($invoiceColumns[$sort], $direction);
        } elseif ($sort === 'doctor_name') {

            $query->leftJoin('doctors', 'doctors.id', '=', 'appointments.doctor_id')
                ->leftJoin('users', 'users.id', '=', 'doctors.user_id')
                ->orderBy('users.name', $direction);
        } elseif (array_key_exists($sort, $sortable)) {

            $query->orderBy($sortable[$sort], $direction);
        } else {

            $query->orderBy('appointments.appointment_date', 'desc');
        }

        $invoices = $query->paginate(request('per_page', paginateLimit()))->withQueryString();

        return Inertia::render(
            'Patients/Billing/Index',
            [
                'invoices' => $invoices,
                'search' => $search,
            ]
        );
    }

    public function print($id)
    {
        $appointment = Appointment::with(['invoice', 'patient.user', 'doctor.user', 'doctor.specialities'])
            ->where('patient_id', auth()->user()?->patient?->id)
            ->where('id', $id)->first();

        return Inertia::render('Patients/Billing/Print', [
            'appointment' => $appointment,
        ]);
    }

    public function billing_payment_history($id, Request $request)
    {
        $keyword = $request->get('keyword', '');

        $paymentHistory = DB::table('billing_cores')
            ->where('encounter_id', $id)
            ->where('payment', '!=', '0')
            ->where(function ($query) use ($keyword) {
                if ($keyword) {
                    $query->where('dos_f', 'like', "%$keyword%")
                        ->orWhere('reason', 'like', "%$keyword%");
                }
            })
            ->orderBy('dos_f', 'desc')
            ->get();

        return Inertia::render('Doctors/Patient/Billing/PaymentHistory', [
            'paymentHistory' => $paymentHistory,
            'encounter' => Encounter::find($id),
            'search' => $keyword,
        ]);
    }

    public function billing_history($id, Request $request) {}

    public function billing_print($id, Request $request) {}

    public function SocialHistory()
    {
        $patientId = auth()->user()->patient->id ?? null;
        $socialHistory = SocialHistory::where('patient_id', $patientId)->first();

        return Inertia::render('Patients/SocialHistory/SocialHistory', [
            'socialHistory' => $socialHistory,
        ]);
    }

    public function storeSocialHistory(SocialHistoryRequest $request)
    {

        $patientId = auth()->user()->patient->id ?? null;

        $input = $request->all();
        $input['patient_id'] = $patientId;

        $this->patient->storeSocialHistory($input);

        if (! $patientId) {
            return back()->with('error', 'Patient not found.');
        }

        return back()->with('success', 'Social history updated successfully.');
    }

    public function Messages(Request $request)
    {
        $patientId = auth()->user()->patient->id ?? null;

        if (! $patientId) {
            return redirect()->route('patient.dashboard');
        }

        $query = Message::with(['patient.user', 'doctor.user'])
            ->where('patient_id', $patientId);

        if ($request->has('search')) {
            $keyword = $request->get('search');
            $query->where(function ($q) use ($keyword) {
                $q->where('subject', 'like', '%'.$keyword.'%')
                    ->orWhere('message', 'like', '%'.$keyword.'%')
                    ->orWhereHas('doctor.user', function ($q2) use ($keyword) {
                        $q2->where('name', 'like', '%'.$keyword.'%');
                    });
            });
        }

        if ($request->has('read')) {
            $query->where('read', $request->boolean('read'));
        }

        $messages = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(request('per_page', paginateLimit()))->withQueryString();

        // Get unread count
        $unreadCount = Message::where('patient_id', $patientId)
            ->where('read', false)
            ->count();

        return Inertia::render('Patients/Messages/Index', [
            'messages' => $messages,
            'keyword' => $request->get('search'),
            'unreadCount' => $unreadCount,
        ]);
    }

    public function messageShow(string $id)
    {
        $patientId = auth()->user()->patient->id ?? null;

        if (! $patientId) {
            return redirect()->route('patient.dashboard');
        }

        $message = Message::with(['patient.user', 'doctor.user'])
            ->where('patient_id', $patientId)
            ->where('id', $id)
            ->firstOrFail();

        // Mark as read
        if (! $message->read) {
            $message->read = true;
            $message->save();
        }

        // Get unread count
        $unreadCount = Message::where('patient_id', $patientId)
            ->where('read', false)
            ->count();

        return Inertia::render('Patients/Messages/Show', [
            'message' => $message,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function FamilyHistory(Request $request)
    {

        $keyword = $request->get('search', '');

        $patientId = Patient::where('user_id', auth()->user()->id)->first()->id ?? null;

        // Paginate and filter
        $familyHistories = OtherHistory::where('patient_id', $patientId)
            ->when($keyword, function ($query, $keyword) {
                $query->where('family_history', 'like', "%{$keyword}%");
            })
            ->orderByDesc('id')
            ->paginate(request('per_page', paginateLimit()))->withQueryString();

        // Convert YAML data to arrays
        $familyHistories->getCollection()->transform(function ($history) {
            if (! empty($history->oh_fh)) {
                try {
                    $formatter = Formatter::make($history->oh_fh, Formatter::YAML);
                    $history->oh_fh = $formatter->toArray();
                } catch (\Exception $e) {
                    $history->oh_fh = [];
                }
            } else {
                $history->oh_fh = [];
            }

            return $history;
        });

        return Inertia::render('Patients/FamilyHistory', [
            'familyHistory' => $familyHistories,
            'keyword' => $keyword,
            'patientId' => $patientId,
        ]);
    }

    public function providers(Request $request)
    {
        $patientId = auth()->user()->patient->id ?? null;
        $patient = Patient::with('user')->find($patientId);

        $connectedDoctors = Doctor::with(['user', 'specialities'])
            ->whereHas('doctorPatients', function ($query) use ($patientId) {
                $query->where('patient_id', $patientId);
            })
            ->get();

        return Inertia::render('Patients/Providers', [
            'connectedDoctors' => $connectedDoctors,
            'patient' => $patient,
            'keyword' => request('serach'),
        ]);
    }

    /**
     * shareDetails
     *
     * @param  mixed  $request
     * @return void
     */
    public function shareDetails(Request $request)
    {
        $validated = $request->validate([
            'provider_name' => 'required|string|max:255',
            'email' => 'required|email',
            'sms' => 'nullable|string|max:20',
        ]);

        // try {
        $data_message = [
            'temp_url' => URL::to('uma_auth'),
            'patient' => auth()->user()->name,
            'provider_name' => $validated['provider_name'],
        ];

        $email = $validated['email'];

        // Check if doctor already exists with this email
        $existingDoctor = Doctor::whereHas('user', function ($query) use ($email) {
            $query->where('email', $email);
        })->first();

        // Store invitation in database
        $invitation = UmaInvitation::create([
            'email' => $validated['email'],
            'name' => $validated['provider_name'],
            'timeout' => now()->addHour(),
            'resource_set_ids' => auth()->user()->patient->id,
        ]);

        // Create in-app notification for existing doctor
        if ($existingDoctor) {
            \App\Models\Notification::create([
                'notifiable_type' => 'App\Models\Doctor',
                'notifiable_id' => $existingDoctor->id,
                'patient_id' => auth()->user()->patient->id,
                'doctor_id' => $existingDoctor->id,
                'data' => json_encode([
                    'type' => 'patient_invitation',
                    'title' => 'Patient Access Request',
                    'message' => 'Patient '.auth()->user()->name.' has requested to share their medical records with you.',
                    'patient_name' => auth()->user()->name,
                    'patient_id' => auth()->user()->patient->id,
                    'action_url' => route('doctor.select.patient', auth()->user()->patient->id),
                ]),
            ]);
        }

        if ($email) {
            // Send email invitation
            try {
                Mail::to($email)->send(new ApiRegisterMail($data_message));
            } catch (\Exception $e) {
                // If there's an error, delete the invitation if it was created
                $invitation->delete();
                throw $e;
            }
        }

        if ($validated['sms']) {
            // Send SMS invitation
            try {
                $message = "You've been invited to use {$data_message['patient']}'s personal health record. Go to {$data_message['temp_url']} to register";

                $response = Http::post('http://textbelt.com/text', [
                    'number' => $validated['sms'],
                    'message' => $message,
                    'key' => config('services.textbelt.key'), // Add your API key in config
                ]);

                if (! $response->successful()) {
                    throw new \Exception('SMS sending failed');
                }
            } catch (\Exception $e) {
                // \Log::error('SMS sending failed: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Invitation sent successfully to '.$email);
        // } catch (\Exception $e) {
        //     \Log::error('Provider invitation failed: ' . $e->getMessage());

        //     return back()->with('error', 'Failed to send invitation:');
        // }
    }

    public function liveConsultation($appointmentId)
    {
        // Verify the appointment belongs to the authenticated patient
        $appointment = Appointment::with(['patient.user', 'doctor.user', 'doctor.hospital'])
            ->where('id', $appointmentId)
            ->where('patient_id', auth()->user()->patient->id)
            ->first();

        if (! $appointment) {
            return redirect()->route('patient.dashboard')->with('error', 'Appointment not found or access denied.');
        }

        // Check if appointment is for today or within a reasonable time window
        $appointmentDateTime = Carbon::parse($appointment->appointment_date.' '.$appointment->appointment_time);
        $now = Carbon::now();

        // Allow joining within 2 hours before and after the appointment time (consistent with doctor side)
        if (abs($now->diffInMinutes($appointmentDateTime, false)) > 120) {
            return redirect()->route('patient.dashboard')->with('error', 'This appointment is not available for joining at this time. Please wait until closer to your appointment time.');
        }

        return Inertia::render('Patients/LiveConsultation', [
            'appointment' => $appointment,
        ]);
    }

    /**
     * removeDoctorAccess
     *
     * @param  mixed  $doctorId
     * @return void
     */
    public function bookingList(Request $request)
    {
        $patientId = auth()->user()->patient->id ?? null;

        if (! $patientId) {
            return redirect()->route('patient.dashboard')->with('error', 'Patient not found.');
        }

        $keyword = $request->get('search', '');
        $status = $request->get('status', '');
        $sort = $request->get('sort', 'appointment_date');
        $direction = $request->get('direction', 'desc');

        // Validate sort column to prevent SQL injection
        $allowedSortColumns = [
            'appointment_date',
            'appointment_time',
            'status',
            'visit_type',
            'reason',
            'created_at',
        ];
        if (! in_array($sort, $allowedSortColumns)) {
            $sort = 'appointment_date';
        }
        if (! in_array(strtolower($direction), ['asc', 'desc'])) {
            $direction = 'desc';
        }

        $query = Appointment::with(['doctor.user', 'doctor.specialities', 'patient.user', 'visitType'])
            ->where('patient_id', $patientId);

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->whereHas('doctor.user', function ($q2) use ($keyword) {
                    $q2->where('name', 'like', '%'.$keyword.'%');
                });
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $appointments = $query->orderBy($sort, $direction)
            ->orderBy('appointment_time', 'desc')
            ->paginate($request->get('per_page', 20))->withQueryString();

        $appointments->map(function ($appointment) {
            $appointment->doctor->speciality_names = implode(', ', $appointment->doctor->specialities->pluck('name')->toArray());
        });

        return Inertia::render('Patients/BookingList', [
            'appointments' => $appointments,
            'keyword' => $keyword,
            'status' => $status,
        ]);
    }

    public function removeDoctorAccess($doctorId)
    {
        try {
            $patientId = auth()->user()->patient->id ?? null;

            if (! $patientId) {
                \Log::error('removeDoctorAccess: Patient not found for user', ['user_id' => auth()->id()]);

                return response()->json(['error' => 'Patient not found'], 404);
            }

            $doctorPatient = \App\Models\DoctorPatient::where('patient_id', $patientId)
                ->where('doctor_id', $doctorId)
                ->first();

            if (! $doctorPatient) {
                \Log::error('removeDoctorAccess: Doctor not connected to patient', [
                    'patient_id' => $patientId,
                    'doctor_id' => $doctorId,
                ]);

                return response()->json(['error' => 'Doctor not connected to this patient'], 404);
            }

            $doctorPatient->delete();

            \Log::info('removeDoctorAccess: Successfully removed doctor access', [
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
            ]);

            return response()->json(['message' => 'Doctor access removed successfully']);
        } catch (\Exception $e) {
            \Log::error('removeDoctorAccess: Exception occurred', [
                'error' => $e->getMessage(),
                'patient_id' => $patientId ?? null,
                'doctor_id' => $doctorId,
            ]);

            return response()->json(['error' => 'An error occurred while removing doctor access'], 500);
        }
    }
}
