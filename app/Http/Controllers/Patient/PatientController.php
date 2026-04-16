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
use Illuminate\Pagination\LengthAwarePaginator;
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
        $keyword = trim((string) $request->input('keyword', $request->input('search', '')));
        $recordType = trim((string) $request->input('record_type', ''));

        $issues = Issue::where('patient_id', auth()->user()->patient->id ?? null)
            ->when($keyword !== '', function ($issues) use ($keyword) {
                $issues->where(function ($query) use ($keyword) {
                    $query->where('issue', 'like', '%'.$keyword.'%')
                        ->orWhere('type', 'like', '%'.$keyword.'%')
                        ->orWhere('notes', 'like', '%'.$keyword.'%')
                        ->orWhere('reconcile', 'like', '%'.$keyword.'%')
                        ->orWhere('rcopia_sync', 'like', '%'.$keyword.'%')
                        ->orWhere('date_active', 'like', '%'.$keyword.'%')
                        ->orWhere('date_inactive', 'like', '%'.$keyword.'%');
                });
            })
            ->when($recordType !== '' && $recordType !== 'all', function ($issues) use ($recordType) {
                $typeMap = [
                    'problem' => 'Problem',
                    'past' => 'MedicalHistory',
                    'surgery' => 'SurgicalHistory',
                ];

                if (isset($typeMap[$recordType])) {
                    $issues->where('type', $typeMap[$recordType]);
                }
            });

        $encounters = Encounter::where('patient_id', auth()->user()->patient->id ?? null)->latest()->first();
        $data = $issues->orderByDesc('date_active')->paginate(request('per_page', paginateLimit()))->withQueryString();

        $data->getCollection()->transform(function ($issue) {
            return [
                'id' => $issue->id,
                'issue' => $issue->issue,
                'type' => $issue->type,
                'type_label' => match ($issue->type) {
                    'Problem' => 'Problem',
                    'MedicalHistory' => 'Past Medical History',
                    'SurgicalHistory' => 'Surgical History',
                    default => $issue->type,
                },
                'notes' => $issue->notes,
                'date_active' => $issue->date_active,
                'date_inactive' => $issue->date_inactive,
                'can_move_to_problem' => $issue->type !== 'Problem',
                'can_move_to_medical_history' => $issue->type !== 'MedicalHistory',
                'can_move_to_surgical_history' => $issue->type !== 'SurgicalHistory',
            ];
        });

        return Inertia::render('Patients/Conditions', [
            'issues' => $data,
            'filters' => [
                'keyword' => $keyword,
                'record_type' => $recordType,
            ],
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
        $keyword = trim((string) $request->input('keyword', ''));
        $status = trim((string) $request->input('status', ''));
        $prescriptionStatus = trim((string) $request->input('prescription_status', ''));

        $medications = Prescription::where('patient_id', auth()->user()->patient->id ?? null)
            ->when($keyword !== '', function ($medications) use ($keyword) {
                $medications->where(function ($query) use ($keyword) {
                    $query->where('medication', 'like', '%'.$keyword.'%')
                        ->orWhere('dosage', 'like', '%'.$keyword.'%')
                        ->orWhere('dosage_unit', 'like', '%'.$keyword.'%')
                        ->orWhere('route', 'like', '%'.$keyword.'%')
                        ->orWhere('frequency', 'like', '%'.$keyword.'%')
                        ->orWhere('reason', 'like', '%'.$keyword.'%')
                        ->orWhere('prescription', 'like', '%'.$keyword.'%')
                        ->orWhere('date_active', 'like', '%'.$keyword.'%')
                        ->orWhere('date_inactive', 'like', '%'.$keyword.'%')
                        ->orWhere('due_date', 'like', '%'.$keyword.'%');
                });
            })
            ->when($status !== '', function ($medications) use ($status) {
                if ($status === 'active') {
                    $medications->whereNull('date_inactive');
                } elseif ($status === 'inactive') {
                    $medications->whereNotNull('date_inactive');
                }
            })
            ->when($prescriptionStatus !== '', function ($medications) use ($prescriptionStatus) {
                $medications->where('prescription', $prescriptionStatus);
            })
            ->orderByDesc('date_active')
            ->paginate(request('per_page', paginateLimit()))
            ->withQueryString();

        $medications->getCollection()->transform(function ($medication) {
            return [
                'id' => $medication->id,
                'medication' => $medication->medication,
                'date_active' => $medication->date_active,
                'date_inactive' => $medication->date_inactive,
                'due_date' => $medication->due_date,
                'prescription' => $medication->prescription,
                'status_label' => $medication->date_inactive ? 'Inactive' : 'Active',
            ];
        });

        $prescriptionStatuses = Prescription::where('patient_id', auth()->user()->patient->id ?? null)
            ->whereNotNull('prescription')
            ->pluck('prescription')
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->merge(['pending'])
            ->unique()
            ->sort()
            ->values();

        return Inertia::render('Patients/Medications', [
            'medications' => $medications,
            'filters' => [
                'keyword' => $keyword,
                'status' => $status,
                'prescription_status' => $prescriptionStatus,
            ],
            'prescriptionStatuses' => $prescriptionStatuses,
        ]);
    }

    public function supplements(Request $request)
    {
        $patientId = auth()->user()->patient->id ?? null;
        $keyword = trim((string) $request->input('keyword', $request->input('search', '')));
        $status = trim((string) $request->input('status', ''));
        $route = trim((string) $request->input('route_name', ''));

        $supplements = PatientSupplement::where('patient_id', $patientId)
            ->when($keyword !== '', function ($supplements) use ($keyword) {
                $supplements->where(function ($query) use ($keyword) {
                    $query->where('supplement', 'like', '%'.$keyword.'%')
                        ->orWhere('dosage', 'like', '%'.$keyword.'%')
                        ->orWhere('dosage_unit', 'like', '%'.$keyword.'%')
                        ->orWhere('sig', 'like', '%'.$keyword.'%')
                        ->orWhere('route', 'like', '%'.$keyword.'%')
                        ->orWhere('frequency', 'like', '%'.$keyword.'%')
                        ->orWhere('instructions', 'like', '%'.$keyword.'%')
                        ->orWhere('reason', 'like', '%'.$keyword.'%');
                });
            })
            ->when($status !== '', function ($supplements) use ($status) {
                if ($status === 'active') {
                    $supplements->whereNull('date_inactive');
                } elseif ($status === 'inactive') {
                    $supplements->whereNotNull('date_inactive');
                }
            })
            ->when($route !== '', function ($supplements) use ($route) {
                $supplements->where('route', $route);
            })
            ->orderByDesc('id')
            ->paginate(request('per_page', paginateLimit()))
            ->withQueryString();

        $supplements->through(function (PatientSupplement $supplement) {
            return array_merge($supplement->toArray(), [
                'status_label' => $supplement->date_inactive ? 'Inactive' : 'Active',
                'active_date_label' => $supplement->date_active?->format('d M, Y') ?? '-',
                'inactive_date_label' => $supplement->date_inactive?->format('d M, Y') ?? '-',
            ]);
        });

        $routeOptions = PatientSupplement::where('patient_id', $patientId)
            ->whereNotNull('route')
            ->pluck('route')
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->unique()
            ->sort()
            ->values();

        return Inertia::render('Patients/Supplements', [
            'supplements' => $supplements,
            'routeOptions' => config('route'),
            'filters' => [
                'keyword' => $keyword,
                'status' => $status,
                'route_name' => $route,
            ],
        ]);
    }

    public function immunizations(Request $request)
    {
        $patientId = auth()->user()->patient->id ?? null;
        $keyword = trim((string) $request->input('keyword', $request->input('search', '')));
        $routeName = trim((string) $request->input('route_name', ''));
        $bodySite = trim((string) $request->input('body_site', ''));

        $immunizations = Immunization::where('patient_id', $patientId)
            ->when($keyword !== '', function ($immunization) use ($keyword) {
                $immunization->where(function ($query) use ($keyword) {
                    $query->where('immunization', 'like', '%'.$keyword.'%')
                        ->orWhere('dosage', 'like', '%'.$keyword.'%')
                        ->orWhere('dosage_unit', 'like', '%'.$keyword.'%')
                        ->orWhere('sequence', 'like', '%'.$keyword.'%')
                        ->orWhere('route', 'like', '%'.$keyword.'%')
                        ->orWhere('body_site', 'like', '%'.$keyword.'%')
                        ->orWhere('manufacturer', 'like', '%'.$keyword.'%')
                        ->orWhere('brand', 'like', '%'.$keyword.'%')
                        ->orWhere('cvx_code', 'like', '%'.$keyword.'%');
                });
            })
            ->when($routeName !== '', function ($immunization) use ($routeName) {
                $immunization->where('route', $routeName);
            })
            ->when($bodySite !== '', function ($immunization) use ($bodySite) {
                $immunization->where('body_site', $bodySite);
            })
            ->orderByDesc('id')
            ->paginate(request('per_page', paginateLimit()))
            ->withQueryString();

        return Inertia::render('Patients/Immunizations', [
            'immunizations' => $immunizations,
            'filters' => [
                'keyword' => $keyword,
                'route_name' => $routeName,
                'body_site' => $bodySite,
            ],
            'routeOptions' => config('route'),
            'bodySiteOptions' => config('bodyside'),
        ]);
    }

    public function allergies(Request $request)
    {
        $patientId = auth()->user()->patient->id ?? null;
        $keyword = trim((string) $request->input('keyword', $request->input('search', '')));
        $status = trim((string) $request->input('status', ''));
        $severity = trim((string) $request->input('severity', ''));

        $allergies = Allergy::where('patient_id', $patientId)
            ->when($keyword !== '', function ($allergies) use ($keyword) {
                $allergies->where(function ($query) use ($keyword) {
                    $query->where('allergies_medicine', 'like', '%'.$keyword.'%')
                        ->orWhere('allergies_reaction', 'like', '%'.$keyword.'%')
                        ->orWhere('allergies_severity', 'like', '%'.$keyword.'%')
                        ->orWhere('notes', 'like', '%'.$keyword.'%')
                        ->orWhere('rcopia_sync', 'like', '%'.$keyword.'%')
                        ->orWhere('medicine_ndcid', 'like', '%'.$keyword.'%');
                });
            })
            ->when($status !== '', function ($allergies) use ($status) {
                if ($status === 'active') {
                    $allergies->whereNull('date_inactive');
                } elseif ($status === 'inactive') {
                    $allergies->whereNotNull('date_inactive');
                }
            })
            ->when($severity !== '', function ($allergies) use ($severity) {
                $allergies->where('allergies_severity', $severity);
            })
            ->orderByDesc('id')
            ->paginate(request('per_page', paginateLimit()))
            ->withQueryString();

        $allergies->through(function (Allergy $allergy) {
            return array_merge($allergy->toArray(), [
                'status_label' => $allergy->date_inactive ? 'Inactive' : 'Active',
                'active_date_label' => $allergy->date_active ?: '-',
            ]);
        });

        $severityOptions = collect(['mild', 'moderate', 'severe']);

        return Inertia::render('Patients/Allergies', [
            'allergies' => $allergies,
            'filters' => [
                'keyword' => $keyword,
                'status' => $status,
                'severity' => $severity,
            ],
            'severityOptions' => $severityOptions,
        ]);
    }

    public function orders(Request $request)
    {
        $patientId = auth()->user()->patient->id ?? null;
        $keyword = trim((string) $request->input('keyword', $request->input('search', '')));
        $status = trim((string) $request->input('status', ''));
        $tab = trim((string) $request->input('tab', 'laboratory'));
        $perPage = (int) $request->input('per_page', paginateLimit());

        $orders = Order::with('patient.user', 'doctor.user', 'encounter')
            ->where('patient_id', $patientId)
            ->orderByDesc('orders_date')
            ->get();

        $parseOrderText = function ($data) {
            if (blank($data)) {
                return null;
            }

            if (is_array($data)) {
                return collect($data)
                    ->map(function ($item) {
                        if (is_string($item)) {
                            return $item;
                        }

                        if (is_array($item)) {
                            return $item['name'] ?? $item['text'] ?? json_encode($item);
                        }

                        return data_get($item, 'name') ?? data_get($item, 'text') ?? json_encode($item);
                    })
                    ->filter()
                    ->join(', ');
            }

            if (is_string($data)) {
                try {
                    $decoded = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

                    if (is_array($decoded)) {
                        return collect($decoded)
                            ->map(function ($item) {
                                if (is_string($item)) {
                                    return $item;
                                }

                                return $item['name'] ?? $item['text'] ?? json_encode($item);
                            })
                            ->filter()
                            ->join(', ');
                    }
                } catch (\Throwable $e) {
                    return trim($data) !== '' ? $data : null;
                }

                return trim($data) !== '' ? $data : null;
            }

            return (string) $data;
        };

        $mappedOrders = $orders->flatMap(function ($order) use ($parseOrderText) {
            $doctorName = $order->doctor?->user?->name ?? $order->doctor?->name ?? 'Unknown Doctor';
            $statusLabel = $order->is_completed ? 'completed' : 'pending';
            $base = [
                'id' => $order->id,
                'date' => $order->orders_date?->format('d M, Y') ?? '-',
                'raw_date' => $order->orders_date?->format('Y-m-d') ?? null,
                'description' => $order->notes ?: 'No description',
                'status' => $statusLabel,
                'doctor' => $doctorName,
                'encounter_id' => $order->encounter_id,
                'view_route' => route('patient.orders.show', $order->id),
            ];

            $types = [
                'laboratory' => $parseOrderText($order->labs),
                'imaging' => $parseOrderText($order->radiology),
                'cardiopulmonary' => $parseOrderText($order->cp),
                'referrals' => $parseOrderText($order->referrals),
            ];

            return collect($types)
                ->filter()
                ->map(fn ($text, $type) => array_merge($base, [
                    'type' => $type,
                    'text' => $text,
                ]))
                ->values();
        });

        $activeTab = in_array($tab, ['laboratory', 'imaging', 'cardiopulmonary', 'referrals']) ? $tab : 'laboratory';

        $filteredOrders = $mappedOrders
            ->where('type', $activeTab)
            ->when($status !== '', function ($collection) use ($status) {
                return $collection->where('status', $status);
            })
            ->when($keyword !== '', function ($collection) use ($keyword) {
                $needle = strtolower($keyword);

                return $collection->filter(function ($order) use ($needle) {
                    return collect([
                        $order['text'] ?? '',
                        $order['description'] ?? '',
                        $order['date'] ?? '',
                        $order['doctor'] ?? '',
                        $order['status'] ?? '',
                    ])->contains(fn ($value) => str_contains(strtolower((string) $value), $needle));
                });
            })
            ->values();

        $offset = max(0, ((int) $request->input('page', 1) - 1) * $perPage);
        $paginatedOrders = new LengthAwarePaginator(
            $filteredOrders->slice($offset, $perPage)->values(),
            $filteredOrders->count(),
            $perPage,
            (int) $request->input('page', 1),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $patientId = auth()->user()->patient->id ?? null;
        $patient = \App\Models\Patient::with('user')->find($patientId);

        return Inertia::render('Patients/Orders', [
            'orders' => $paginatedOrders,
            'patient' => $patient,
            'filters' => [
                'keyword' => $keyword,
                'status' => $status,
                'tab' => $activeTab,
            ],
            'tabCounts' => [
                'laboratory' => $mappedOrders->where('type', 'laboratory')->count(),
                'imaging' => $mappedOrders->where('type', 'imaging')->count(),
                'cardiopulmonary' => $mappedOrders->where('type', 'cardiopulmonary')->count(),
                'referrals' => $mappedOrders->where('type', 'referrals')->count(),
            ],
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
        $keyword = trim((string) $request->input('keyword', $request->input('search', '')));
        $status = trim((string) $request->input('status', ''));

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
            'filters' => [
                'keyword' => $keyword,
                'status' => $status,
            ],
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
        $patientId = auth()->user()->patient->id ?? null;
        $keyword = trim((string) $request->input('keyword', $request->input('search', '')));
        $tab = trim((string) $request->input('tab', 'Laboratory'));

        $results = Test::where('patient_id', $patientId)
            ->when($tab !== '', function ($query) use ($tab) {
                $query->where('type', $tab);
            })
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($resultQuery) use ($keyword) {
                    $resultQuery->where('name', 'like', '%'.$keyword.'%')
                        ->orWhere('code', 'like', '%'.$keyword.'%')
                        ->orWhere('result', 'like', '%'.$keyword.'%')
                        ->orWhere('type', 'like', '%'.$keyword.'%')
                        ->orWhere('units', 'like', '%'.$keyword.'%')
                        ->orWhere('reference', 'like', '%'.$keyword.'%');
                });
            })
            ->orderByDesc('created_at')
            ->paginate(request('per_page', paginateLimit()))
            ->withQueryString();

        return Inertia::render('Patients/Results/Index', [
            'results' => $results,
            'filters' => [
                'keyword' => $keyword,
                'tab' => in_array($tab, ['Laboratory', 'Imaging']) ? $tab : 'Laboratory',
            ],
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
        $keyword = trim((string) $request->input('keyword', $request->input('search', '')));
        $paymentStatus = trim((string) $request->input('payment_status', ''));
        $perPage = (int) $request->input('per_page', paginateLimit());

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
        if ($keyword !== '') {

            $query->where(function ($q) use ($keyword) {

                $q->where('appointments.appointment_date', 'like', "%{$keyword}%")
                    ->orWhere('appointments.fee_amount', 'like', "%{$keyword}%")
                    ->orWhere('appointments.payment_status', 'like', "%{$keyword}%")

                    /* Invoice search */
                    ->orWhereHas('invoice', function ($qq) use ($keyword) {
                        $qq->where('invoice_number', 'like', "%{$keyword}%")
                            ->orWhere('discount_amount', 'like', "%{$keyword}%")
                            ->orWhere('tax_amount', 'like', "%{$keyword}%");
                    })

                    /* Doctor search */
                    ->orWhereHas('doctor.user', function ($qq) use ($keyword) {
                        $qq->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($paymentStatus !== '') {
            $query->where('appointments.payment_status', $paymentStatus);
        }

        $summaryQuery = clone $query;
        $summary = [
            'records' => (clone $summaryQuery)->count(),
            'balance' => (clone $summaryQuery)->sum('appointments.total_amount'),
            'charges' => (clone $summaryQuery)->sum('appointments.fee_amount'),
        ];

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

        $invoices = $query->paginate($perPage)->withQueryString();
        $invoices->through(function (Appointment $appointment) {
            return array_merge($appointment->toArray(), [
                'invoice_number' => $appointment->invoice?->invoice_number
                    ? 'INV-'.$appointment->invoice->invoice_number
                    : '-',
                'doctor_name' => $appointment->doctor?->user?->name ?: '-',
                'appointment_date_label' => $appointment->appointment_date ?: '-',
                'discount_amount' => $appointment->invoice?->discount_amount ?? 0,
                'tax_amount_label' => $appointment->tax_amount ?? $appointment->invoice?->tax_amount ?? 0,
                'payment_status_label' => ucfirst((string) ($appointment->payment_status ?: 'pending')),
            ]);
        });

        return Inertia::render(
            'Patients/Billing/Index',
            [
                'invoices' => $invoices,
                'summary' => $summary,
                'filters' => [
                    'keyword' => $keyword,
                    'payment_status' => $paymentStatus,
                ],
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

        $query = Message::with(['patient.user', 'doctor.user', 'lab', 'pharmacy'])
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
        $keyword = trim((string) $request->input('keyword', $request->input('search', '')));
        $relationship = trim((string) $request->input('relationship', ''));
        $gender = trim((string) $request->input('gender', ''));
        $perPage = (int) $request->input('per_page', paginateLimit());

        $patientId = Patient::where('user_id', auth()->user()->id)->first()->id ?? null;

        $familyRows = OtherHistory::where('patient_id', $patientId)
            ->orderByDesc('id')
            ->get()
            ->flatMap(function ($history) {
                $familyHistory = [];

                if (! empty($history->oh_fh)) {
                    try {
                        $formatter = Formatter::make($history->oh_fh, Formatter::YAML);
                        $familyHistory = $formatter->toArray();
                    } catch (\Exception $e) {
                        $familyHistory = [];
                    }
                }

                if (! is_array($familyHistory)) {
                    return [];
                }

                return collect($familyHistory)->map(function ($item, $index) use ($history) {
                    return [
                        'id' => $history->id.'-'.$index,
                        'parent_id' => $history->id,
                        'name' => $item['name'] ?? $item['Name'] ?? '',
                        'relationship' => $item['relationship'] ?? $item['Relationship'] ?? '',
                        'living_status' => $item['living_status'] ?? $item['Status'] ?? $item['status'] ?? '',
                        'gender' => $item['gender'] ?? $item['Gender'] ?? '',
                        'dob' => $item['dob'] ?? $item['Date of Birth'] ?? $item['date_of_Birth'] ?? '',
                        'marital_status' => $item['marital_status'] ?? $item['Marital Status'] ?? '',
                        'mother' => $item['mother'] ?? $item['Mother'] ?? '',
                        'father' => $item['father'] ?? $item['Father'] ?? '',
                        'medical_history' => $item['medical_history'] ?? $item['Medical'] ?? $item['medical'] ?? '',
                        'created_label' => optional($history->created_at)->format('M d, Y'),
                    ];
                });
            })
            ->filter(function ($item) {
                return ! empty($item['name']) || ! empty($item['relationship']) || ! empty($item['medical_history']);
            })
            ->when($keyword !== '', function ($collection) use ($keyword) {
                $search = strtolower($keyword);

                return $collection->filter(function ($item) use ($search) {
                    return collect([
                        $item['name'] ?? '',
                        $item['relationship'] ?? '',
                        $item['gender'] ?? '',
                        $item['dob'] ?? '',
                        $item['marital_status'] ?? '',
                        is_array($item['medical_history'] ?? null)
                            ? implode(', ', $item['medical_history'])
                            : ($item['medical_history'] ?? ''),
                    ])->some(fn ($value) => str_contains(strtolower((string) $value), $search));
                });
            })
            ->when($relationship !== '', function ($collection) use ($relationship) {
                return $collection->filter(fn ($item) => ($item['relationship'] ?? '') === $relationship);
            })
            ->when($gender !== '', function ($collection) use ($gender) {
                return $collection->filter(fn ($item) => ($item['gender'] ?? '') === $gender);
            })
            ->values();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paginatedRows = new LengthAwarePaginator(
            $familyRows->slice(($currentPage - 1) * $perPage, $perPage)->values(),
            $familyRows->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $relationshipOptions = collect([
            'Father',
            'Mother',
            'Brother',
            'Sister',
            'Son',
            'Daughter',
            'Grandfather',
            'Grandmother',
            'Uncle',
            'Aunt',
            'Cousin',
            'Other',
        ]);

        $genderOptions = collect(['Male', 'Female', 'Other']);

        return Inertia::render('Patients/FamilyHistory', [
            'familyHistory' => $paginatedRows,
            'filters' => [
                'keyword' => $keyword,
                'relationship' => $relationship,
                'gender' => $gender,
            ],
            'relationshipOptions' => $relationshipOptions,
            'genderOptions' => $genderOptions,
            'patientId' => $patientId,
        ]);
    }

    public function providers(Request $request)
    {
        $patientId = auth()->user()->patient->id ?? null;
        $patient = Patient::with('user')->find($patientId);
        $keyword = trim((string) $request->input('keyword', $request->input('search', '')));
        $speciality = trim((string) $request->input('speciality', ''));

        $connectedDoctors = Doctor::with(['user', 'specialities'])
            ->whereHas('doctorPatients', function ($query) use ($patientId) {
                $query->where('patient_id', $patientId);
            })
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($doctorQuery) use ($keyword) {
                    $doctorQuery->whereHas('user', function ($userQuery) use ($keyword) {
                        $userQuery->where('name', 'like', '%'.$keyword.'%')
                            ->orWhere('email', 'like', '%'.$keyword.'%')
                            ->orWhere('mobile', 'like', '%'.$keyword.'%');
                    })->orWhereHas('specialities', function ($specialityQuery) use ($keyword) {
                        $specialityQuery->where('name', 'like', '%'.$keyword.'%');
                    });
                });
            })
            ->when($speciality !== '', function ($query) use ($speciality) {
                $query->whereHas('specialities', function ($specialityQuery) use ($speciality) {
                    $specialityQuery->where('name', $speciality);
                });
            })
            ->orderByDesc('created_at')
            ->paginate(request('per_page', paginateLimit()))
            ->withQueryString();

        $connectedDoctors->through(function ($doctor) {
            return [
                'id' => $doctor->id,
                'name' => $doctor->user?->name,
                'email' => $doctor->user?->email,
                'mobile' => $doctor->user?->mobile,
                'specialities' => $doctor->specialities->pluck('name')->values(),
                'avatar' => $doctor->user?->profile_photo_url
                    ?? ($doctor->user?->profile_photo_path ? '/storage/'.$doctor->user->profile_photo_path : '/images/avatar.webp'),
            ];
        });

        $specialityOptions = Doctor::whereHas('doctorPatients', function ($query) use ($patientId) {
            $query->where('patient_id', $patientId);
        })
            ->with('specialities')
            ->get()
            ->flatMap(fn ($doctor) => $doctor->specialities->pluck('name'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return Inertia::render('Patients/Providers', [
            'connectedDoctors' => $connectedDoctors,
            'patient' => $patient,
            'filters' => [
                'keyword' => $keyword,
                'speciality' => $speciality,
            ],
            'specialityOptions' => $specialityOptions,
        ]);
    }

    /**
     * shareDetailProviders
     *
     * Return doctors from hospitals where the patient has had appointments in the past.
     */
    public function shareDetailProviders()
    {
        $patientId = auth()->user()?->patient?->id;

        $appointments = Appointment::with('doctor:id,hospital_id')
            ->where('patient_id', $patientId)
            ->get();

        $hospitalIds = $appointments
            ->pluck('hospital_id')
            ->filter()
            ->merge(
                $appointments->pluck('doctor.hospital_id')->filter()
            )
            ->unique()
            ->values();

        if ($hospitalIds->isEmpty()) {
            return response()->json([]);
        }

        $doctors = Doctor::with(['user:id,name,email,mobile', 'hospital:id,name'])
            ->whereIn('hospital_id', $hospitalIds)
            ->whereHas('user', function ($query) {
                $query->whereNotNull('email')->where('email', '!=', '');
            })
            ->get()
            ->map(function ($doctor) {
                return [
                    'id' => $doctor->id,
                    'name' => $doctor->user?->name ?? 'Unknown provider',
                    'email' => $doctor->user?->email ?? '',
                    'mobile' => $doctor->user?->mobile ?? '',
                    'hospital_name' => $doctor->hospital?->name ?? '',
                ];
            })
            ->sortBy('name')
            ->values();

        return response()->json($doctors);
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
            'provider_id' => 'required|string|exists:doctors,id',
            'provider_name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'sms' => 'nullable|string|max:20',
        ]);

        $doctor = Doctor::with('user')->findOrFail($validated['provider_id']);
        $providerName = $doctor->user?->name ?? $validated['provider_name'] ?? 'Provider';
        $providerEmail = $doctor->user?->email ?? $validated['email'] ?? null;
        $providerMobile = $doctor->user?->mobile ?? $validated['sms'] ?? null;

        // try {
        $data_message = [
            'temp_url' => URL::to('uma_auth'),
            'patient' => auth()->user()->name,
            'provider_name' => $providerName,
        ];

        $email = $providerEmail;

        // Check if doctor already exists with this email
        $existingDoctor = Doctor::whereHas('user', function ($query) use ($email) {
            $query->where('email', $email);
        })->first();

        // Store invitation in database
        $invitation = UmaInvitation::create([
            'email' => $providerEmail,
            'name' => $providerName,
            'timeout' => now()->addHour(),
            'resource_set_ids' => auth()->user()->patient->id,
        ]);

        // Create in-app notification for existing doctor
        if ($existingDoctor?->user) {
            app(\App\Services\InAppNotificationService::class)->notifyUser(
                $existingDoctor->user,
                [
                    'title' => 'Patient Access Request',
                    'message' => 'Patient '.auth()->user()->name.' has requested to share their medical records with you.',
                    'type' => 'patient_invitation',
                    'recipient_role' => 'Doctor',
                    'patient_id' => auth()->user()->patient->id,
                    'doctor_id' => $existingDoctor->id,
                    'action_url' => route('doctor.select.patient', auth()->user()->patient->id),
                    'related_model_type' => \App\Models\Patient::class,
                    'related_model_id' => auth()->user()->patient->id,
                ]
            );
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

        $keyword = trim((string) $request->get('keyword', $request->get('search', '')));
        $status = $request->get('status', '');
        $perPage = (int) $request->get('per_page', 10);
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
            ->paginate($perPage)->withQueryString();

        $appointments->getCollection()->transform(function ($appointment) {
            $appointment->doctor_name = $appointment->doctor?->user?->name ?? '-';
            $appointment->doctor_speciality = $appointment->doctor?->specialities?->pluck('name')->filter()->implode(', ') ?: '-';
            $appointment->visit_type_label = $appointment->visitType?->name ?? $appointment->appointment_type ?? 'Consultation';
            $appointment->appointment_date_label = $appointment->appointment_date ?: '-';
            $appointment->appointment_time_label = $appointment->appointment_time ?: '-';
            $appointment->reason_label = $appointment->reason ?: '-';
            $appointment->status_label = ucfirst((string) ($appointment->status ?: 'pending'));
            $appointment->payment_status_label = ucfirst((string) ($appointment->payment_status ?: 'pending'));
            $appointment->can_join_live = $appointment->status === 'confirmed'
                && strtolower((string) ($appointment->payment_status ?? '')) === 'paid';

            return $appointment;
        });

        return Inertia::render('Patients/BookingList', [
            'appointments' => $appointments,
            'filters' => [
                'keyword' => $keyword,
                'status' => $status,
            ],
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
