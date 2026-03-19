<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorAssistant;
use App\Models\DoctorPatient;
use App\Models\Document;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\Schedule;
use App\Models\Speciality;
use App\Models\Test;
use App\Models\User;
use App\Services\DoctorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DoctorController extends Controller
{
    /**
     * index
     *
     * @param  mixed  $request
     * @return void
     */
    public function index(Request $request)
    {
        $specialties = Speciality::orderBy('specialty', 'ASC')->get();
        $doctors = User::with('address', 'doctor')->where('role_id', '=', '3')->where('status', true)->where('is_email_verified', true);
        if ($request->has('keyword')) {

            $keyword = $request->get('keyword');
            $doctors = $doctors->where('name', 'Like', '%'.$keyword.'%');
        }
        $doctors = $doctors->orderBy('id', 'DESC')->paginate($this->paginate);
        $results = [];
        foreach ($doctors as $data) {
            $results[] = Speciality::where('id', json_decode($data->specialty_id))->get();
        }
        $doctorSpecialty = [];
        foreach ($results as $data) {
            $doctorSpecialty[] =
                $data;
        }

        return Inertia::render('Doctors/DoctorList', [
            'doctors' => $doctors,
            'doctorSpecialty' => $doctorSpecialty,
            'keyword' => $request->get('keyword'),
            'specialties' => $specialties,
        ]);
    }

    /**
     * show
     *
     * @param  mixed  $slug
     * @return void
     */
    public function show($slug)
    {
        $doctor = User::with('address', 'DoctorAssistant', 'doctor')->where('slug', $slug)->first();
        $doctorAssistant = DoctorAssistant::with(['user'])->where('doctor_assistant_id', $doctor->id)->first();
        $specialties = Speciality::orderBy('specialty', 'ASC')->pluck('specialty')->toArray();
        $results = Speciality::where('id', json_decode($doctor['specialty_id']))->get();
        $doctorSpecialty = [];
        foreach ($results as $data) {
            $doctorSpecialty[] =
                $data->specialty;
        }
        $languages = __('language.isoLangs');
        $lang = $languages;
        $language = [];
        foreach ($lang as $data) {
            $language[] =
                $data['name'];
        }

        return Inertia::render('Doctors/DoctorDetail', [
            'doctor' => $doctor,
            'language' => $language,
            'doctorSpecialty' => $doctorSpecialty,
            'specialties' => $specialties,
            'doctorAssistant' => $doctorAssistant,
        ]);
    }

    /**
     * profile
     *
     * @param  mixed  $slug
     * @return void
     */
    public function profile()
    {
        $user = auth()->user();

        // Handle admin switching to doctor view (no doctor record exists)
        if (! $user->doctor && $user->hasRole('Admin') && session('switched_role') === 'Doctor') {
            return redirect()->route('admin.dashboard')->with('error', 'Doctor profile not available in admin doctor view mode.');
        }
        $doctor = Doctor::with('user.address', 'user', 'specialities', 'hospital.timings')->where('id', $user->doctor->id)->first();
        $specialties = Speciality::orderBy('name', 'ASC')->get();
        $activities = [];
        if ($user->doctor) {
            $doctorId = $user->doctor->id;

            $encounters = Encounter::where('doctor_id', $doctorId)
                ->with('patient.user')
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => 'Encounter',
                        'description' => 'Encounter with '.($item->patient->user->name ?? 'Patient'),
                        'date' => $item->created_at,
                        'type' => 'encounter',
                        'icon' => 'ri-file-list-line',
                        'color' => 'primary',
                    ];
                });

            $appointments = Appointment::where('doctor_id', $doctorId)
                ->with('patient.user')
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => 'Appointment',
                        'description' => 'Appointment with '.($item->patient->user->name ?? 'Patient'),
                        'date' => $item->created_at,
                        'type' => 'appointment',
                        'icon' => 'ri-calendar-line',
                        'color' => 'success',
                    ];
                });

            $prescriptions = \App\Models\Prescription::where('doctor_id', $doctorId)
                ->with('patient.user')
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => 'Prescription',
                        'description' => 'Prescribed '.$item->medication.' for '.($item->patient->user->name ?? 'Patient'),
                        'date' => $item->created_at,
                        'type' => 'prescription',
                        'icon' => 'ri-capsule-line',
                        'color' => 'warning',
                    ];
                });

            $activities = $encounters->concat($appointments)->concat($prescriptions)
                ->sortByDesc('date')
                ->take(20)
                ->values();
        }

        return Inertia::render('Doctors/Profile/Index', [
            'doctor' => $doctor,
            'specialties' => $specialties,
            'activities' => $activities,
        ]);
    }

    /**
     * store
     *
     * @param  mixed  $request
     * @param  mixed  $obj
     */
    public function store(Request $request, DoctorService $obj): RedirectResponse
    {
        $obj->profileUpdate($request->all(), $request);

        return back()->with('success', 'Your detail has been saved successfully.');
    }

    /**
     * dashboard
     *
     * @param  mixed  $request
     * @return void
     */
    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        // For Doctor users, ensure profile (About & Speciality) is complete before accessing dashboard
        if ($user->hasRole('Doctor') && $doctor) {
            $about = trim((string) $doctor->about);
            $hasSpeciality = $doctor->specialities()->exists();

            if ($about === '' || ! $hasSpeciality) {
                return redirect()
                    ->route('doctor.profile')
                    ->with('warning', 'Please complete your profile (About and Speciality) before accessing the dashboard.');
            }
        }

        // Handle admin switching to doctor view (no doctor record exists)
        if (! $doctor && $user->hasRole('Admin') && session('switched_role') === 'Doctor') {
            // Create a mock doctor object for admin viewing doctor interface
            $doctor = (object) [
                'id' => null,
            ];
        }

        // If still no doctor, redirect back (shouldn't happen with proper middleware)
        if (! $doctor) {
            return redirect()->route('admin.dashboard')->with('error', 'Doctor profile not found.');
        }

        // Get selected patient if any
        $selectedPatientId = $doctor->selected_patient_id;

        // Encounters counts - filter by selected patient if set
        $encountersQuery = Encounter::where('doctor_id', $doctor->id);
        if ($selectedPatientId) {
            $encountersQuery->where('patient_id', $selectedPatientId);
        }
        $encountersTotal = $encountersQuery->count();

        // Patients counts (via DoctorPatient relationship) - keep general for doctor
        $patientsQuery = DoctorPatient::with('patient')->where('doctor_id', $doctor->id);
        if ($selectedPatientId) {
            $patientsQuery->where('patient_id', $selectedPatientId);
        }
        $patientsTotal = $patientsQuery->count();
        $patientsActive = $patientsQuery->where('patient_id', '!=', null)->count();
        $patientsInactive = $patientsTotal - $patientsActive;

        // Documents counts - filter by selected patient if set
        $documentsQuery = Document::query();
        if ($selectedPatientId) {
            $documentsQuery->where('patient_id', $selectedPatientId);
        }
        $documentsTotal = $documentsQuery->count();

        // Tests counts - filter by selected patient if set
        $testsQuery = Test::query();
        if ($selectedPatientId) {
            $testsQuery->where('patient_id', $selectedPatientId);
        }
        $testsTotal = $testsQuery->count();

        // Appointments counts - filter by selected patient if set
        $appointmentsQuery = Appointment::where('doctor_id', $doctor->id);
        if ($selectedPatientId) {
            $appointmentsQuery->where('patient_id', $selectedPatientId);
        }
        $appointmentsTotal = $appointmentsQuery->count();
        $appointmentsPending = $appointmentsQuery->where('status', 'pending')->count();
        $appointmentsCompleted = $appointmentsQuery->where('status', 'completed')->count();

        // Appointments list - filter by selected patient if set
        $appointments = Appointment::where('doctor_id', $doctor->id)
            ->when($selectedPatientId, function ($query) use ($selectedPatientId) {
                return $query->where('patient_id', $selectedPatientId);
            })
            ->with('patient.user')
            ->leftJoin('users', 'appointments.created_by', '=', 'users.id')
            ->select('appointments.*', 'users.name as created_by_name')
            ->latest('appointments.created_at')
            ->paginate(request('per_page', paginateLimit()))->withQueryString();
        $schedules = Schedule::where('doctor_id', auth()->user()->doctor->id)->get();
        foreach ($schedules as $schedule) {
            $events[] = [
                'title' => $schedule->reason,
                'start' => $schedule->start_date,
                'duration' => $schedule->duration,
                'description' => $schedule->notes,
                'background' => '#c6d3e6',
            ];
        }

        // Get selected patient info if any
        $selectedPatient = null;
        if ($selectedPatientId) {
            $selectedPatient = Patient::with('user')->find($selectedPatientId);
        }

        $dashboardData = [
            'messages' => 0,
            'encounters' => [
                'total' => $encountersTotal,
            ],
            'patients' => [
                'total' => $patientsTotal ?? 0,
                'active' => $patientsActive ?? 0,
                'inactive' => $patientsInactive ?? 0,
            ],
            'documents' => [
                'total' => $documentsTotal,
            ],
            'tests' => [
                'total' => $testsTotal,
            ],
            'calendars' => [
                'total' => $appointmentsTotal,
                'pending' => $appointmentsPending,
                'completed' => $appointmentsCompleted,
            ],

            'reminders' => 0,
            'scanned_documents' => $documentsTotal, // keep original key if used elsewhere
            'bills_to_process' => 0,
            'test_results_to_review' => $testsTotal,
            'appointments' => $appointments,
            'schedules' => $events ?? [],
            'selected_patient' => $selectedPatient,

        ];

        return Inertia::render('Doctors/Dashboard', ['dashboardData' => $dashboardData]);
    }

    /**
     * searchPatient
     *
     * @return void
     */
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
     * searchRecipients - Search for non-patient recipients (labs, pharmacies, doctors, assistants, billers)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchRecipients()
    {
        $search = request('q');
        $results = [];

        // Debug: Log the search query and user
        \Log::info('searchRecipients called', ['q' => $search, 'user_id' => auth()->id()]);

        // Search for Lab users (users with lab relationship)
        $labs = \App\Models\User::whereHas('lab')
            ->where(function ($q) use ($search) {
                if ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                }
            })
            ->select('id', 'name', 'email')
            ->limit(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'type' => 'lab',
                    'email' => $user->email,
                ];
            });
        \Log::info('Labs found:', ['count' => count($labs)]);
        $results = array_merge($results, $labs->toArray());

        // Search for Pharmacy users (users with pharmacy relationship)
        $pharmacies = \App\Models\User::whereHas('pharmacy')
            ->where(function ($q) use ($search) {
                if ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                }
            })
            ->select('id', 'name', 'email')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'type' => 'pharmacy',
                    'email' => $user->email,
                ];
            });
        \Log::info('Pharmacies found:', ['count' => count($pharmacies)]);
        $results = array_merge($results, $pharmacies->toArray());

        // Search for other Doctors (users with doctor relationship) - excluding current doctor
        $currentUserId = auth()->id();
        $doctorsQuery = \App\Models\User::whereHas('doctor');

        $doctors = $doctorsQuery
            ->where(function ($q) use ($search) {
                if ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                }
            })
            ->select('id', 'name', 'email')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'type' => 'doctor',
                    'email' => $user->email,
                ];
            });
        \Log::info('Doctors found:', ['count' => count($doctors)]);
        $results = array_merge($results, $doctors->toArray());

        // Search for Assistants (users with DoctorAssistant relationship)
        $assistants = \App\Models\User::whereHas('DoctorAssistant')
            ->where(function ($q) use ($search) {
                if ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                }
            })
            ->select('id', 'name', 'email')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'type' => 'assistant',
                    'email' => $user->email,
                ];
            });
        \Log::info('Assistants found:', ['count' => count($assistants)]);
        $results = array_merge($results, $assistants->toArray());

        // Search for Billers (using role name 'Biller')
        $billers = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('name', 'Biller');
        })
            ->where(function ($q) use ($search) {
                if ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                }
            })
            ->select('id', 'name', 'email')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'type' => 'biller',
                    'email' => $user->email,
                ];
            });
        \Log::info('Billers found:', ['count' => count($billers)]);
        $results = array_merge($results, $billers->toArray());

        // If search term provided, filter all results by type as well
        if ($search) {
            $searchLower = strtolower($search);
            $results = array_filter($results, function ($item) use ($searchLower) {
                return stripos($item['name'], $searchLower) !== false
                    || stripos($item['email'], $searchLower) !== false
                    || stripos($item['type'], $searchLower) !== false;
            });
            $results = array_values($results);
        }

        // Limit results
        $results = array_slice($results, 0, 15);

        \Log::info('Final results:', ['count' => count($results)]);

        return response()->json($results);
    }

    /**
     * selectPatient
     *
     * @param  mixed  $patient_id
     * @return void
     */
    public function selectPatient($patient_id)
    {
        $user = auth()->user();

        if ($patient_id == 'empty') {
            $patient_id = null;
        }

        // Check if user has a doctor record (admins switching to doctor view don't have one)
        if ($user->doctor) {
            Doctor::where('user_id', $user->id)->update([
                'selected_patient_id' => $patient_id,
            ]);

            return redirect()->back()->with('success', 'Patient selected successfully.');
        } else {
            // Admin switching to doctor view - no doctor record to update
            return redirect()->back()->with('info', 'Patient selection not available in admin doctor view mode.');
        }
    }

    /**
     * getSelectedPatient - Get the currently selected patient ID
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSelectedPatient()
    {
        $user = auth()->user();

        if (! $user->doctor) {
            return response()->json([
                'id' => null,
                'message' => 'No doctor profile',
            ]);
        }

        $selectedPatientId = $user->doctor->selected_patient_id;

        if (! $selectedPatientId) {
            return response()->json([
                'id' => null,
                'message' => 'No patient selected',
            ]);
        }

        return response()->json([
            'id' => $selectedPatientId,
            'message' => 'Patient found',
        ]);
    }

    /**
     * search
     *
     * @param  mixed  $request
     * @return void
     */
    public function search(Request $request)
    {
        $query = $request->get('query');

        return User::whereHas('doctor')
            ->where('name', 'LIKE', "%{$query}%")
            ->select('id', 'name', 'email', 'mobile')
            ->limit(10)
            ->get();
    }

    /**
     * List doctors (optionally filtered by speciality)
     */
    public function list(Request $request)
    {
        $specialityId = $request->query('speciality');

        return Doctor::query()
            ->when($specialityId, function ($q) use ($specialityId) {
                $q->whereHas('specialities', function ($sq) use ($specialityId) {
                    $sq->where('specialities.id', $specialityId);
                });
            })
            ->get();
    }
}
