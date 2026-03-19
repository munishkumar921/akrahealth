<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\BankAccount;
use App\Models\Doctor;
use App\Models\DoctorPatient;
use App\Models\Document;
use App\Models\Encounter;
use App\Models\Hospital;
use App\Models\Invoice;
use App\Models\LabOrder;
use App\Models\Message;
use App\Models\Order;
use App\Models\PharmacyOrder;
use App\Models\Prescription;
use App\Models\Roles;
use App\Models\Schedule;
use App\Models\SubscriptionPlan;
use App\Models\Test;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\AuditService;
use App\Services\SettingService;
use App\Services\UserService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminController extends Controller
{
    protected $userService;

    protected $settingService;

    protected $auditService;

    public $paginate = 5;

    /**
     * __construct
     *
     * @param  mixed  $userService
     * @param  mixed  $settingService
     * @param  mixed  $auditService
     * @return void
     */
    public function __construct(UserService $userService, SettingService $settingService, AuditService $auditService)
    {
        // hasAccess('manage admins');
        $this->userService = $userService;
        $this->userService->role = 'Admin';
        $this->settingService = $settingService;
        $this->auditService = $auditService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $admins = $this->userService->list(request());
        $request = request();
        $keyword = $request->get('keyword') ?? '';

        return inertia('Admin/AdminList', compact('admins', 'request', 'keyword'));
    }

    /**
     * create
     *
     * @return void
     */
    public function create()
    {
        $user = new User;
        $permissions = $this->userService->getPermissions();

        return inertia('Admin/AdminCreate', compact('user', 'permissions'));
    }

    /**
     * store
     *
     * @param  mixed  $request
     * @return void
     */
    public function store(Request $request)
    {
        $this->userService->upsert($request->all());

        return redirect()->route('admin.admins.index')->with('success', __('Admin created successfully.'));
    }

    /**
     * edit
     *
     * @param  mixed  $id
     * @return void
     */
    public function edit($id)
    {
        $user = User::where('id', $id)->first();
        $permissions = $this->userService->getPermissions();
        $formPermissions = $user->getPermissionNames();

        return inertia('Admin/AdminCreate', compact('user', 'permissions', 'formPermissions'));
    }

    /**
     * update
     *
     * @param  mixed  $request
     * @param  mixed  $id
     * @return void
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $data['id'] = $id;

        $this->userService->upsert($data);

        return redirect()->route('admin.admins.index')->with('success', __('Admin updated successfully.'));
    }

    /**
     * destroy
     *
     * @param  mixed  $id
     * @return void
     */
    public function destroy($id)
    {
        User::where('id', $id)->delete();

        return redirect()->route('admin.admins.index')->with('success', __('Admin deleted successfully.'));
    }

    /**
     * dashboard
     *
     * @return void
     */
    public function dashboard()
    {
        $user = auth()->user();
        $hospital = Hospital::where('user_id', $user->id)->with('timings')->first();
        // If admin is viewing as a doctor, redirect to doctor dashboard
        if ($user->hasRole('Admin') && session('switched_role') === 'Doctor') {
            return redirect()->route('doctor.dashboard');
        }

        if (! $hospital) {
            return redirect()->route('admin.profile')->with('warning', 'Please create your hospital profile before accessing the dashboard.');
        }
        if (! $hospital->timings()->exists()) {
            return redirect()
                ->route('admin.profile')
                ->with('warning', 'Please complete your profile (Timings and other details) before accessing the dashboard.');
        }

        $hospitalId = $hospital->id;

        // Encounters counts for the hospital
        $encountersTotal = Encounter::where('hospital_id', $hospitalId)->count();

        // Patients counts for the hospital
        $patientsTotal = DoctorPatient::whereHas('doctor', function ($q) use ($hospitalId) {
            $q->where('hospital_id', $hospitalId);
        })->distinct('patient_id')->count();

        $patientsActive = DoctorPatient::whereHas('doctor', function ($q) use ($hospitalId) {
            $q->where('hospital_id', $hospitalId);
        })->whereHas('patient.user', function ($q) {
            $q->where('is_active', true);
        })->distinct('patient_id')->count();

        $patientsInactive = $patientsTotal - $patientsActive;

        // Documents counts for the hospital
        $documentsTotal = Document::where('hospital_id', $hospitalId)->count();

        // Tests counts for the hospital
        $testsTotal = Test::where('hospital_id', $hospitalId)->count();

        // Appointments counts for the hospital
        $appointmentsQuery = Appointment::whereHas('doctor', function ($q) use ($hospitalId) {
            $q->where('hospital_id', $hospitalId);
        });
        $appointmentsTotal = (clone $appointmentsQuery)->count();
        $appointmentsPending = (clone $appointmentsQuery)->where('status', 'pending')->count();
        $appointmentsCompleted = (clone $appointmentsQuery)->where('status', 'completed')->count();

        // Appointments list for the hospital
        $appointments = Appointment::whereHas('doctor', function ($query) use ($hospitalId) {
            $query->where('hospital_id', $hospitalId);
        })
            ->with('patient.user', 'doctor.user')
            ->leftJoin('users', 'appointments.created_by', '=', 'users.id')
            ->select('appointments.*', 'users.name as created_by_name')
            ->latest('appointments.created_at')
            ->paginate($this->paginate)->withQueryString();

        // Schedules for all doctors in the hospital
        $doctorIds = Doctor::where('hospital_id', $hospitalId)->pluck('id');
        $schedules = Schedule::whereIn('doctor_id', $doctorIds)->get();
        $events = [];
        foreach ($schedules as $schedule) {
            $events[] = [
                'title' => $schedule->reason,
                'start' => $schedule->start_date,
                'duration' => $schedule->duration,
                'description' => $schedule->notes,
                'background' => '#c6d3e6',
            ];
        }

        $dashboardData = [
            'messages' => 0, // This seems to be a placeholder
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

            'reminders' => 0, // placeholder
            'scanned_documents' => $documentsTotal,
            'bills_to_process' => 0, // placeholder
            'test_results_to_review' => $testsTotal,
            'appointments' => $appointments,
            'schedules' => $events ?? [],
        ];

        return Inertia::render('Admin/Dashboard', [
            'dashboardData' => $dashboardData,
        ]);
    }

    /**
     * Switch role between Admin and Doctor
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switchRole(Request $request)
    {
        $user = $request->user();

        // Check if user has Admin role
        if (! $user || ! $user->hasRole('Admin')) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['error' => 'Only Admin can switch roles.'], 403);
            }

            return redirect()->back()->withErrors(['error' => 'Only Admin can switch roles.']);
        }

        $currentSwitchedRole = session('switched_role');

        if ($currentSwitchedRole === 'Doctor') {
            // Switch back to Admin
            session()->forget('switched_role');
            $redirectUrl = route('admin.dashboard');
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Switched back to Admin role.',
                    'redirect' => $redirectUrl,
                ]);
            }

            return redirect($redirectUrl)->with('success', 'Switched back to Admin role.');
        } else {
            // Switch to Doctor
            session(['switched_role' => 'Doctor']);
            $redirectUrl = route('doctor.dashboard');
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Switched to Doctor role.',
                    'redirect' => $redirectUrl,
                ]);
            }

            return redirect($redirectUrl)->with('success', 'Switched to Doctor role.');
        }
    }

    /**
     * profile
     *
     * @param  mixed  $slug
     * @return void
     */
    public function profile()
    {
        $hospital = Hospital::with(['user', 'timings'])->where('user_id', auth()->user()->id)->first();
        $branches = collect();

        if ($hospital) {
            $branches = Hospital::with(['user', 'timings'])->where('user_id', auth()->user()->id)->orWhere('main_branch_id', $hospital->id)->get();

            // Map timings to schedules format for frontend compatibility
            $branches = $branches->map(function ($branch) {
                $timings = $branch->timings ?? collect();
                $branch->schedules = $timings->map(function ($timing) {
                    return [
                        'id' => $timing->id,
                        'day_of_week' => $timing->day_of_week,
                        'open_time' => $timing->open_time,
                        'close_time' => $timing->close_time,
                    ];
                })->toArray();

                return $branch;
            });

            // Also map hospital timings
            $hospitalTimings = $hospital->timings ?? collect();
            $hospital->schedules = $hospitalTimings->map(function ($timing) {
                return [
                    'id' => $timing->id,
                    'day_of_week' => $timing->day_of_week,
                    'open_time' => $timing->open_time,
                    'close_time' => $timing->close_time,
                ];
            })->toArray();
        } else {
            $hospital = new Hospital;
        }

        return Inertia::render('Admin/Profile/Index', [
            'hospital' => $hospital,
            'branches' => $branches,

        ]);
    }

    public function patientList(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string'],
        ]);

        $patients = User::where('role_id', 4)->where('name', 'LIKE', '%'.$request->name.'%')->limit(20)->get();

        return response()->json($patients);
    }

    public function task()
    {
        return Inertia::render('Admin/Task');
    }

    public function setup()
    {
        return Inertia::render('Admin/PracticeSetup');
    }

    public function products()
    {
        return Inertia::render('Admin/Products');
    }

    public function adminList()
    {
        return Inertia::render('Admin/AdminList');
    }

    public function doctorList()
    {
        return Inertia::render('Admin/DoctorList');
    }

    public function patientsList()
    {
        return Inertia::render('Admin/PatientList');
    }

    public function labList()
    {
        return Inertia::render('Admin/LabList');
    }

    public function pharmacyList()
    {
        return Inertia::render('Admin/PharmacyList');
    }

    public function specalitiesList()
    {
        return Inertia::render('Admin/SpecialityList');
    }

    public function mediciniesList()
    {
        return Inertia::render('Admin/MedicinesList');
    }

    public function prescriptionsList()
    {
        return Inertia::render('Admin/PrescriptionList');
    }

    public function transactionlist(Request $request)
    {
        $keyword = $request->keyword;
        $type = $request->type;
        $status = $request->status;

        $subscriptions = UserSubscription::with(['user', 'subscriptionPlan'])
            ->where('user_id', auth()->id())
            ->whereNotNull('razorpay_order_id')
            ->when($keyword, function ($q) use ($keyword) {
                $q->whereHas('user', function ($uq) use ($keyword) {
                    $uq->where('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%");
                });
            })
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'type' => 'subscription',
                    'type_label' => 'Subscription',
                    'order_id' => $sub->razorpay_order_id
                        ?? $sub->razorpay_subscription_id
                        ?? 'N/A',
                    'payment_id' => $sub->razorpay_payment_id ?? 'N/A',
                    'user' => $sub->user->name ?? 'N/A',
                    'user_email' => $sub->user->email ?? 'N/A',
                    'description' => $sub->subscriptionPlan->title ?? 'Subscription',
                    'amount' => $sub->amount ?? 0,
                    'currency' => $sub->currency ?? 'INR',
                    'status' => $sub->status ?? 'pending',
                    'gateway' => 'Razorpay',
                    'created_at' => optional($sub->created_at)->format('Y-m-d H:i:s'),
                ];
            });

        $transactions = Transaction::with(['user', 'labOrder', 'pharmacyOrder', 'invoice'])
            ->where('payment_type', 'razorpay')
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($query) use ($keyword) {
                    $query->where('transaction_id', 'like', "%{$keyword}%")
                        ->orWhere('razorpay_payment_id', 'like', "%{$keyword}%")
                        ->orWhere('razorpay_order_id', 'like', "%{$keyword}%")
                        ->orWhereHas('user', function ($uq) use ($keyword) {
                            $uq->where('name', 'like', "%{$keyword}%")
                                ->orWhere('email', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($trans) {
                if ($trans->labOrder) {
                    $type = 'lab_order';
                    $label = 'Lab Order';
                    $desc = 'Lab Order #'.$trans->labOrder->id;
                } elseif ($trans->pharmacyOrder) {
                    $type = 'pharmacy_order';
                    $label = 'Pharmacy Order';
                    $desc = 'Pharmacy Order #'.$trans->pharmacyOrder->id;
                } elseif ($trans->invoice) {
                    $type = 'invoice';
                    $label = 'Invoice';
                    $desc = 'Invoice #'.($trans->invoice->invoice_number ?? $trans->invoice->id);
                } elseif ($trans->order) {
                    $type = 'order';
                    $label = 'Order';
                    $desc = 'Payment';
                } else {
                    $type = 'appointment';
                    $label = 'Appointment';
                    $desc = 'Payment';
                }

                return [
                    'id' => $trans->id,
                    'type' => $type,
                    'type_label' => $label,
                    'order_id' => $trans->razorpay_order_id
                        ?? $trans->transaction_id
                        ?? 'N/A',
                    'payment_id' => $trans->razorpay_payment_id ?? 'N/A',
                    'user' => $trans->user->name ?? 'N/A',
                    'user_email' => $trans->user->email ?? 'N/A',
                    'description' => $desc,
                    'amount' => $trans->amount ?? 0,
                    'currency' => $trans->currency ?? 'INR',
                    'status' => $trans->status ?? 'pending',
                    'gateway' => 'Razorpay',
                    'created_at' => optional($trans->created_at)->format('Y-m-d H:i:s'),
                ];
            });

        $allTransactions = $subscriptions->merge($transactions);

        if ($type && $type !== 'all') {
            $allTransactions = $allTransactions->where('type', $type);
        }

        $perPage = $this->paginate;
        $currentPage = $request->integer('page', 1);

        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $allTransactions->forPage($currentPage, $perPage)->values(),
            $allTransactions->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return Inertia::render('Admin/TransactionList', [
            'transactions' => $paginated,
            'keyword' => $keyword,
            'filters' => [
                'type' => $type ?? 'all',
                'status' => $status ?? '',
            ],
        ]);
    }

    public function print($id)
    {
        $appointment = Appointment::with(['invoice', 'patient.user', 'doctor.user', 'doctor.specialities'])
            ->where('id', $id)->first();

        return Inertia::render('Patients/Billing/Print', [
            'appointment' => $appointment,
        ]);
    }

    public function invoicesList(Request $request)
    {
        $search = $request->input('search');
        $sort = request('sort', 'created_at');
        $direction = request('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        $query = Invoice::select('invoices.*')
            ->with([
                'patient.user',
                'user',
                'doctor.user',
                'hospital',
                'appointment',
                'subscription.subscriptionPlan',
            ])->where('hospital_id', auth()->user()->hospital->id);

        /* search */
        $query->when($search, function ($q, $search) {

            $q->where(function ($sub) use ($search) {

                $sub->where('invoices.invoice_number', 'like', "%{$search}%")
                    ->orWhere('invoices.razorpay_payment_id', 'like', "%{$search}%")
                    ->orWhere('invoices.appointment_id', 'like', "%{$search}%")
                    ->orWhere('invoices.total_amount', 'like', "%{$search}%")
                    ->orWhere('invoices.currency', 'like', "%{$search}%")
                    ->orWhere('invoices.status', 'like', "%{$search}%")
                    ->orWhereHas('patient.user', function ($qq) use ($search) {
                        $qq->where('name', 'like', "%{$search}%");
                    });
            });
        });

        /* sorting */
        $sortable = [
            'appointment_id' => 'invoices.appointment_id',
            'invoice_number' => 'invoices.invoice_number',
            'razorpay_payment_id' => 'invoices.razorpay_payment_id',
            'total_amount' => 'invoices.total_amount',
            'currency' => 'invoices.currency',
            'status' => 'invoices.status',
            'created_at' => 'invoices.created_at',
        ];

        if ($sort === 'patient_name') {

            $query->leftJoin('patients', 'patients.id', '=', 'invoices.patient_id')
                ->leftJoin('users', 'users.id', '=', 'patients.user_id')
                ->orderBy('users.name', $direction);
        } elseif (array_key_exists($sort, $sortable)) {

            $query->orderBy($sortable[$sort], $direction);
        } else {

            $query->orderBy('invoices.created_at', 'desc');
        }

        $invoices = $query->paginate($this->paginate)->withQueryString();

        return Inertia::render('Admin/InvoiceList', [
            'invoices' => $invoices,
            'search' => $search,
        ]);
    }

    /**
     * Download Invoice PDF
     *
     * @param  int  $id
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadInvoice($id)
    {
        $invoice = Invoice::with(['user', 'order.items', 'order.patient.user', 'hospital'])
            ->findOrFail($id);

        $data = [
            'invoice' => $invoice,
            'company' => setting('company_name') ?? config('app.name'),
        ];

        // load Blade view that renders the invoice HTML
        $pdf = Pdf::loadView('admin.invoices.pdf', $data)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true, // allow external images/CSS if used
            ]);

        return $pdf->download("invoice-{$invoice->id}.pdf");
    }

    public function allAppointments(Request $request)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        // Get hospital_id from doctor or from session for admin viewing doctor mode
        $hospitalId = null;

        if ($doctor) {
            $hospitalId = $doctor->hospital_id;
        } elseif ($user->hasRole('Admin') && session('switched_role') === 'Doctor') {
            // For admin viewing in doctor mode, we'll show all doctors without hospital filter
            $hospitalId = null;
        }

        // Get all doctors - optionally filtered by hospital
        $doctorsQuery = Doctor::with(['user', 'specialities', 'hospital'])
            ->when($hospitalId, function ($q) use ($hospitalId) {
                $q->where('hospital_id', $hospitalId);
            })
            ->where('is_active', true)
            ->where('is_verified', true);

        $doctors = $doctorsQuery->get();

        // Format doctors for dropdown
        $doctorsList = $doctors->map(function ($doc) {
            return [
                'id' => $doc->id,
                'name' => $doc->user->name ?? $doc->user->first_name.' '.$doc->user->last_name,
                'speciality' => $doc->specialities->first()->name ?? 'General',
                'hospital' => $doc->hospital->name ?? 'N/A',
                'photo' => $doc->user->profile_photo_path ?? null,
            ];
        });

        // Get all appointments with relationships
        $appointmentsQuery = Appointment::with(['patient.user', 'doctor.user', 'doctor.specialities'])
            ->when($hospitalId, function ($q) use ($hospitalId) {
                $q->whereHas('doctor', function ($query) use ($hospitalId) {
                    $query->where('hospital_id', $hospitalId);
                });
            })
            ->leftJoin('users as created_by_user', 'appointments.created_by', '=', 'created_by_user.id')
            ->select('appointments.*', 'created_by_user.name as created_by_name')
            ->orderBy('appointments.created_at', 'desc');

        // Apply doctor filter if selected
        if ($request->has('doctor_id') && $request->doctor_id) {
            $appointmentsQuery->where('doctor_id', $request->doctor_id);
        }

        // Apply status filter if selected
        if ($request->has('status') && $request->status) {
            $appointmentsQuery->where('status', $request->status);
        }

        // Apply upcoming filter if tab is upcoming
        if ($request->has('tab') && $request->tab === 'upcoming') {
            $appointmentsQuery->where('appointment_date', '>=', now()->toDateString());
        }

        $appointments = $appointmentsQuery->paginate($this->paginate)->withQueryString();

        // Get calendar events using a fresh query (clone the base query before pagination)
        $calendarQuery = Appointment::with(['patient.user', 'doctor.user', 'doctor.specialities'])
            ->when($hospitalId, function ($q) use ($hospitalId) {
                $q->whereHas('doctor', function ($query) use ($hospitalId) {
                    $query->where('hospital_id', $hospitalId);
                });
            })
            ->leftJoin('users as created_by_user', 'appointments.created_by', '=', 'created_by_user.id')
            ->select('appointments.*', 'created_by_user.name as created_by_name')
            ->orderBy('appointments.created_at', 'desc');

        // Apply same filters to calendar query
        if ($request->has('doctor_id') && $request->doctor_id) {
            $calendarQuery->where('doctor_id', $request->doctor_id);
        }
        if ($request->has('status') && $request->status) {
            $calendarQuery->where('status', $request->status);
        }
        if ($request->has('tab') && $request->tab === 'upcoming') {
            $calendarQuery->where('appointment_date', '>=', now()->toDateString());
        }

        $calendarEvents = $calendarQuery->get()->map(function ($apt) {
            $doctorName = $apt->doctor->user->name ?? 'Dr. Unknown';
            $patientName = $apt->patient->user->name ?? 'Unknown Patient';

            // Get raw time value to avoid Carbon formatting issues
            $rawTime = $apt->getOriginal('appointment_time');

            // Determine color based on status
            $backgroundColor = match ($apt->status) {
                'completed' => '#06c270',
                'cancelled' => '#f35353',
                'pending' => '#3b82f6',
                'rescheduled' => '#f59e0b',
                default => '#6b7280',
            };

            return [
                'id' => $apt->id,
                'title' => "{$patientName} - {$doctorName}",
                'start' => $apt->getOriginal('appointment_date').' '.$rawTime,
                'status' => ucfirst($apt->status),
                'backgroundColor' => $backgroundColor,
                'borderColor' => $backgroundColor,
                'extendedProps' => [
                    'patient' => $patientName,
                    'doctor' => $doctorName,
                    'type' => $apt->appointment_type,
                    'mode' => $apt->appointment_mode,
                    'status' => $apt->status,
                    'appointment_time' => $rawTime,
                ],
            ];
        });

        return Inertia::render('Admin/AllAppointments', [
            'doctors' => $doctorsList,
            'appointments' => $appointments,
            'calendarEvents' => $calendarEvents,
            'filters' => [
                'doctor_id' => $request->doctor_id ?? null,
                'status' => $request->status ?? null,
                'tab' => $request->tab ?? null,
            ],
        ]);
    }

    public function rolespermission(Request $request)
    {
        $keyword = $request->get('keyword') ?? '';

        $roles = Roles::query();
        $roles = $roles->orderBy('created_at', 'desc')->paginate(request('per_page', paginateLimit()))->withQueryString();

        return Inertia::render('Admin/RolesandPermission', [
            'roles' => $roles,
            'keyword' => $keyword,
        ]);
    }

    /**
     * API: List all roles
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiRolesList(Request $request)
    {
        try {
            $keyword = $request->get('keyword', '');

            $query = Roles::query();

            if ($keyword) {
                $query->where('name', 'LIKE', '%'.$keyword.'%');
            }

            $roles = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $roles,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch roles: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Store a new role
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiRolesStore(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:roles,name,'.$request->id,
                'guard_name' => 'nullable|string|max:255',
            ]);

            $role = \Spatie\Permission\Models\Role::updateOrCreate(
                ['id' => $request->id],
                [
                    'name' => $request->name,
                    'guard_name' => $request->guard_name ?? 'web',
                    'is_active' => $request->has('is_status') ? (bool) $request->is_status : true,
                ]
            );

            return back()->with('success', __('Role created successfully.'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create role: '.$e->getMessage());
        }
    }

    /**
     * API: Delete a role
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function apiRolesDestroy($id)
    {
        try {
            $role = \Spatie\Permission\Models\Role::findOrFail($id);
            $role->delete();

            return back()->with('success', __('Role deleted successfully.'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete role: '.$e->getMessage());
        }
    }

    /**
     * API: Toggle role active status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiRolesToggle(Request $request, int $id)
    {
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $role = Roles::findOrFail($id);
        // Update the is_active field
        $role->is_active = $request->is_active;
        $role->save();

        return back()->with('success', __('Role status updated successfully.'));
    }

    /**
     * Display bank accounts list
     *
     * @return \Inertia\Response
     */
    public function bankAccounts(Request $request)
    {
        $keyword = $request->get('keyword', '');
        $status = $request->get('status', '');

        $query = BankAccount::query()
            ->when($keyword, function ($q) use ($keyword) {
                $q->where('account_holder_name', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('bank_name', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('account_number', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('ifsc_code', 'LIKE', '%'.$keyword.'%');
            })
            ->when($status === 'active', function ($q) {
                $q->where('is_active', true);
            })
            ->when($status === 'inactive', function ($q) {
                $q->where('is_active', false);
            })
            ->when($status === 'primary', function ($q) {
                $q->where('is_primary', true);
            })
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc');

        $bankAccounts = $query->paginate($this->paginate)->withQueryString();

        // Transform for frontend
        $bankAccounts->getCollection()->transform(function ($account) {
            return [
                'id' => $account->id,
                'account_holder_name' => $account->account_holder_name,
                'bank_name' => $account->bank_name,
                'account_number' => $account->account_number,
                'masked_account_number' => $account->masked_account_number,
                'ifsc_code' => $account->ifsc_code,
                'branch_address' => $account->branch_address,
                'account_type' => $account->account_type,
                'is_primary' => $account->is_primary,
                'is_active' => $account->is_active,
                'created_at' => $account->created_at?->format('Y-m-d H:i:s') ?? 'N/A',
                'updated_at' => $account->updated_at?->format('Y-m-d H:i:s') ?? 'N/A',
            ];
        });

        return Inertia::render('Admin/Epayment', [
            'bankAccounts' => $bankAccounts,
            'keyword' => $keyword,
            'filters' => [
                'status' => $status,
            ],
        ]);
    }

    /**
     * Store a new bank account
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeBankAccount(Request $request)
    {
        $validated = $request->validate([
            'account_holder_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'ifsc_code' => 'required|string|max:50',
            'branch_address' => 'nullable|string',
            'account_type' => 'required|in:savings,current',
            'is_primary' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        // If setting as primary, unset other primary accounts
        if ($request->boolean('is_primary')) {
            BankAccount::where('user_id', auth()->id())
                ->orWhereNull('user_id')
                ->update(['is_primary' => false]);
        }

        $bankAccount = BankAccount::create([
            'user_id' => auth()->id(),
            'account_holder_name' => $validated['account_holder_name'],
            'bank_name' => $validated['bank_name'],
            'account_number' => $validated['account_number'],
            'ifsc_code' => $validated['ifsc_code'],
            'branch_address' => $validated['branch_address'] ?? null,
            'account_type' => $validated['account_type'],
            'is_primary' => $request->boolean('is_primary'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Create audit log for bank account creation
        $this->auditService->logCreate('bank_accounts', $bankAccount, __('Bank account created for').' '.$validated['bank_name']);

        return redirect()->route('admin.bank-accounts')->with('success', __('Bank account added successfully.'));
    }

    /**
     * Update an existing bank account
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateBankAccount(Request $request, $id)
    {
        $bankAccount = BankAccount::findOrFail($id);

        $validated = $request->validate([
            'account_holder_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'ifsc_code' => 'required|string|max:50',
            'branch_address' => 'nullable|string',
            'account_type' => 'required|in:savings,current',
            'is_primary' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        // If setting as primary, unset other primary accounts
        if ($request->boolean('is_primary') && ! $bankAccount->is_primary) {
            BankAccount::where('user_id', auth()->id())
                ->where('id', '!=', $id)
                ->update(['is_primary' => false]);
        }

        $bankAccount->update([
            'account_holder_name' => $validated['account_holder_name'],
            'bank_name' => $validated['bank_name'],
            'account_number' => $validated['account_number'],
            'ifsc_code' => $validated['ifsc_code'],
            'branch_address' => $validated['branch_address'] ?? null,
            'account_type' => $validated['account_type'],
            'is_primary' => $request->boolean('is_primary'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.bank-accounts')->with('success', __('Bank account updated successfully.'));
    }

    /**
     * Delete a bank account
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyBankAccount($id)
    {
        $bankAccount = BankAccount::findOrFail($id);
        $bankAccount->delete();

        return redirect()->route('admin.bank-accounts')->with('success', __('Bank account deleted successfully.'));
    }

    /**
     * Set a bank account as primary
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setPrimaryBankAccount($id)
    {
        $bankAccount = BankAccount::findOrFail($id);

        // Unset primary from all other accounts
        BankAccount::where('user_id', auth()->id())
            ->orWhereNull('user_id')
            ->where('id', '!=', $id)
            ->update(['is_primary' => false]);

        // Set this account as primary
        $bankAccount->update(['is_primary' => true]);

        return redirect()->route('admin.bank-accounts')->with('success', __('Bank account set as primary successfully.'));
    }

    public function subscription()
    {
        $user = auth()->user();
        $currency = $this->getUserLocation();

        $subscription = UserSubscription::where('user_id', $user->id)
            ->with('subscriptionPlan')
            ->where('status', 'active')
            ->latest()
            ->first();

        $subscriptionPlans = SubscriptionPlan::where('status', true)
            ->where('currency', $currency['currency'] ?? 'USD')
            ->select('id', 'title', 'price', 'currency', 'frequency', 'plan_for')
            ->orderBy('price', 'ASC')
            ->get();

        // Check if admin is viewing as another user
        $isViewingAsAdmin = session('switched_role') === 'Doctor' && $user->hasRole('Admin');

        return Inertia::render('Admin/MySubscription/Index', [
            'subscription' => $subscription,
            'subscriptionPlans' => $subscriptionPlans,
            'isViewingAsAdmin' => $isViewingAsAdmin,
        ]);
    }

    public function SubscriptionsHistory()
    {
        $user = auth()->user();
        $subscriptions = UserSubscription::where('user_id', $user->id)
            ->with('subscriptionPlan')

            ->latest()
            ->get();

        return Inertia::render('Admin/MySubscription/SubscriptionsHistory', ['subscriptions' => $subscriptions]);
    }

    public function service()
    {
        return Inertia::render('Admin/Service');
    }

    public function general()
    {
        return Inertia::render('Admin/General');
    }

    public function notification(Request $request)
    {
        $keyword = $request->get('keyword');
        $type = $request->get('type');
        $status = $request->get('status');

        // 🔹 Get hospital safely
        $hospital = Hospital::where('user_id', auth()->user()->id)->firstOrFail();

        // 🔹 Get doctor IDs for hospital
        $doctorIds = Doctor::where('hospital_id', $hospital->id)
            ->pluck('id')
            ->toArray();

        // 🔹 Notifications query
        $query = \App\Models\Notification::whereIn('doctor_id', $doctorIds)
            ->when($keyword, function ($q) use ($keyword) {
                $q->where('data', 'like', "%{$keyword}%");
            })
            ->when($type, function ($q) use ($type) {
                $q->where('type', $type);
            })
            ->when($status === 'read', function ($q) {
                $q->whereNotNull('read_at');
            })
            ->when($status === 'unread', function ($q) {
                $q->whereNull('read_at');
            })
            ->orderByDesc('created_at');

        $notifications = $query
            ->paginate($this->paginate)
            ->withQueryString();

        // 🔹 Transform for frontend
        $notifications->getCollection()->transform(function ($notification) {
            $data = is_array($notification->data)
                ? $notification->data
                : json_decode($notification->data ?? '{}', true);

            return [
                'id' => $notification->id,
                'title' => $data['title'] ?? 'Notification',
                'notification' => $data['message'] ?? $data['notification'] ?? 'No message',
                'type' => $notification->type ?? 'General',
                'channel' => $data['channel'] ?? 'System',
                'status' => $notification->read_at ? 'Read' : 'Unread',
                'sent_at' => $notification->created_at?->format('d M, Y h:i A'),
                'read_at' => $notification->read_at?->format('d M, Y h:i A'),
            ];
        });

        // 🔹 Notification types for filter
        $types = \App\Models\Notification::whereIn('doctor_id', $doctorIds)
            ->distinct()
            ->pluck('type')
            ->filter()
            ->values();

        return Inertia::render('Admin/Notification', [
            'notifications' => $notifications,
            'keyword' => $keyword,
            'type' => $type,
            'status' => $status,
            'types' => $types,
        ]);
    }

    public function labOrdersList(Request $request)
    {
        $keyword = $request->get('keyword', '');
        $status = $request->get('status', '');

        $orders = Order::with('patient.user', 'doctor.user', 'encounter', 'lab')->where('doctor_id', auth()->user()->doctor->id)
            ->orderBy('created_at', 'desc')
            ->paginate(request('per_page', paginateLimit()))->withQueryString();
        $orders->through(function ($order) {

            $lab = $order->lab ?? $order->encounterProvider;

            return [
                'id' => $order->id,
                'lab_name' => $lab?->name ?? '-',

                'order_type' => match (true) {
                    ! empty($order->labs) => 'labs',
                    ! empty($order->radiology) => 'radiology',
                    ! empty($order->cp) => 'cp',
                    default => '-',
                },

                // pending date
                'pending_date' => $order->pending_date
                    ?? $order->created_at?->format('Y-m-d'),

                'notes' => $order->notes ?? '',

                // optional extras
                'patient' => $order->patient?->user?->name,
                'doctor' => $order->doctor?->user?->name,
                'status' => $order->status,
            ];
        });

        return Inertia::render('Admin/Labs/LabOrdersList', [
            'labOrders' => $orders,
            'keyword' => $keyword,

        ]);
    }

    public function pharmacyOrdersList(Request $request)
    {
        $keyword = $request->get('keyword', '');
        $status = $request->get('status', '');
        $paymentStatus = $request->get('payment_status', '');

        $pharmacyOrders = [];
        if (isset(auth()->user()->doctor->hospital_id)) {

            $doctorIds = Doctor::where('hospital_id', auth()->user()->doctor->hospital_id)->pluck('id')->toArray();
            $pharmacyOrders = Prescription::with(['doctor', 'patient', 'pharmacy'])
                ->whereIn('doctor_id', $doctorIds)
                ->when($keyword, function ($q) use ($keyword) {
                    $q->whereHas('pharmacy', function ($pharmacyQuery) use ($keyword) {
                        $pharmacyQuery->where('name', 'LIKE', '%'.$keyword.'%');
                    });
                })
                ->orderBy('created_at', 'desc')
                ->paginate(request('per_page', paginateLimit()))->withQueryString();
        }

        return Inertia::render('Admin/PharmacyOrdersList', [
            'pharmacyOrders' => $pharmacyOrders,
            'keyword' => $keyword,
            'filters' => [
                'status' => $status,
                'payment_status' => $paymentStatus,
            ],
        ]);
    }

    public function pharmacyReports(Request $request)
    {
        $keyword = $request->get('keyword', '');
        $status = $request->get('status', '');
        $paymentStatus = $request->get('payment_status', '');

        $query = PharmacyOrder::with(['pharmacy', 'appointment.patient.user', 'doctor.user'])
            ->when($keyword, function ($q) use ($keyword) {
                $q->whereHas('pharmacy', function ($pharmacyQuery) use ($keyword) {
                    $pharmacyQuery->where('name', 'LIKE', '%'.$keyword.'%');
                })
                    ->orWhereHas('appointment.patient.user', function ($patientQuery) use ($keyword) {
                        $patientQuery->where('name', 'LIKE', '%'.$keyword.'%')
                            ->orWhere('email', 'LIKE', '%'.$keyword.'%');
                    })
                    ->orWhereHas('doctor.user', function ($doctorQuery) use ($keyword) {
                        $doctorQuery->where('name', 'LIKE', '%'.$keyword.'%');
                    })
                    ->orWhere('id', 'LIKE', '%'.$keyword.'%');
            })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($paymentStatus, function ($q) use ($paymentStatus) {
                $q->where('payment_status', $paymentStatus);
            });

        $pharmacyReports = $query->orderBy('created_at', 'desc')
            ->paginate($this->paginate)
            ->withQueryString();

        // Transform the data for frontend
        $pharmacyReports->getCollection()->transform(function ($order) {
            // Get patient name from appointment relationship
            $patientName = 'N/A';
            if ($order->appointment && $order->appointment->patient) {
                $patient = $order->appointment->patient;
                $patientName = $patient->name ?? ($patient->user ? $patient->user->name : 'Patient');
            }

            // Get doctor name
            $doctorName = 'N/A';
            if ($order->doctor) {
                if ($order->doctor->user) {
                    $doctorName = $order->doctor->user->name;
                } else {
                    $firstName = $order->doctor->first_name ?? '';
                    $lastName = $order->doctor->last_name ?? '';
                    $doctorName = trim($firstName.' '.$lastName) ?: 'Dr. Unknown';
                }
            }

            return [
                'id' => $order->id,
                'order_id' => 'ORD-'.substr($order->id, 0, 8),
                'pharmacy' => $order->pharmacy->name ?? 'N/A',
                'pharmacy_id' => $order->pharmacy_id,
                'doctor' => $doctorName,
                'doctor_id' => $order->doctor_id,
                'patient' => $patientName,
                'patient_id' => $order->patient_id,
                'amount' => $order->total_amount ?? 0,
                'payment_status' => $order->payment_status ?? 'pending',
                'payment_status_label' => $order->formatted_payment_status ?? ucfirst($order->payment_status ?? 'Pending'),
                'status' => $order->status ?? 'pending',
                'status_label' => $order->formatted_status ?? ucfirst($order->status ?? 'Pending'),
                'created_at' => $order->created_at->format('Y-m-d'),
            ];
        });

        return Inertia::render('Admin/PharmacyReportsList', [
            'pharmacyReports' => $pharmacyReports,
            'keyword' => $keyword,
            'filters' => [
                'status' => $status,
                'payment_status' => $paymentStatus,
            ],
        ]);
    }

    public function labReports(Request $request)
    {
        $keyword = $request->get('keyword', '');
        $status = $request->get('status', '');
        $paymentStatus = $request->get('payment_status', '');
        $dateFrom = $request->get('date_from', '');
        $dateTo = $request->get('date_to', '');

        $query = LabOrder::with(['lab', 'appointment.patient.user', 'doctor.user'])
            ->when($keyword, function ($q) use ($keyword) {
                $q->whereHas('lab', function ($labQuery) use ($keyword) {
                    $labQuery->where('name', 'LIKE', '%'.$keyword.'%');
                })
                    ->orWhereHas('appointment.patient.user', function ($patientQuery) use ($keyword) {
                        $patientQuery->where('name', 'LIKE', '%'.$keyword.'%')
                            ->orWhere('email', 'LIKE', '%'.$keyword.'%');
                    })
                    ->orWhereHas('doctor.user', function ($doctorQuery) use ($keyword) {
                        $doctorQuery->where('name', 'LIKE', '%'.$keyword.'%');
                    })
                    ->orWhere('id', 'LIKE', '%'.$keyword.'%');
            })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($paymentStatus, function ($q) use ($paymentStatus) {
                $q->where('payment_status', $paymentStatus);
            })
            ->when($dateFrom, function ($q) use ($dateFrom) {
                $q->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($dateTo, function ($q) use ($dateTo) {
                $q->whereDate('created_at', '<=', $dateTo);
            });

        $labReports = $query->orderBy('created_at', 'desc')
            ->paginate($this->paginate)
            ->withQueryString();

        // Transform the data for frontend
        $labReports->getCollection()->transform(function ($order) {
            // Get patient name from appointment relationship
            $patientName = 'N/A';
            $patientId = null;
            if ($order->appointment && $order->appointment->patient) {
                $patient = $order->appointment->patient;
                $patientName = $patient->name ?? ($patient->user ? $patient->user->name : 'Patient');
                $patientId = $patient->id;
            }

            // Get doctor name
            $doctorName = 'N/A';
            $doctorId = null;
            if ($order->doctor) {
                $doctorId = $order->doctor->id;
                if ($order->doctor->user) {
                    $doctorName = $order->doctor->user->name;
                } else {
                    $firstName = $order->doctor->first_name ?? '';
                    $lastName = $order->doctor->last_name ?? '';
                    $doctorName = trim($firstName.' '.$lastName) ?: 'Dr. Unknown';
                }
            }

            // Get lab name
            $labName = $order->lab->name ?? 'N/A';

            return [
                'id' => $order->id,
                'order_id' => '#LAB-'.str_pad($order->id, 3, '0', STR_PAD_LEFT),
                'lab' => $labName,
                'lab_id' => $order->lab_id,
                'doctor' => $doctorName,
                'doctor_id' => $doctorId,
                'patient' => $patientName,
                'patient_id' => $patientId,
                'amount' => $order->total_amount ?? 0,
                'payment_status' => $order->payment_status ?? 'unpaid',
                'status' => $order->status ?? 'pending',
                'is_active' => $order->status === 'completed',
                'scheduled_at' => $order->scheduled_at
                    ? Carbon::parse($order->scheduled_at)->format('Y-m-d H:i A')
                    : '-',
                'reported_at' => $order->reported_at
                    ? Carbon::parse($order->reported_at)->format('Y-m-d H:i A')
                    : '-',
                'created_at' => $order->created_at->format('Y-m-d'),
                'report_file' => $order->report_file ?? null,
            ];
        });

        return Inertia::render('Admin/Labs/LabReportsList', [
            'labReports' => $labReports,
            'keyword' => $keyword,
            'filters' => [
                'status' => $status,
                'payment_status' => $paymentStatus,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    public function CCDAReports()
    {
        return Inertia::render('Admin/CCDAReportsList');
    }

    public function chartReports()
    {
        return Inertia::render('Admin/ChartReportsList');
    }

    public function messages(Request $request)
    {
        $messages = Message::where('hospital_id', auth()->user()->hospital->id)->with(['patient', 'doctor.user'])
            ->when($request->patient_id, fn ($q, $pid) => $q->where('patient_id', $pid))
            ->when($request->search, fn ($q, $keyword) => $q->where(
                fn ($q2) => $q2
                    ->where('subject', 'like', "%{$keyword}%")
                    ->orWhere('message', 'like', "%{$keyword}%")
                    ->orWhereHas('patient.user', fn ($q3) => $q3->where('name', 'like', "%{$keyword}%"))
            ))
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 15)
            ->withQueryString();

        return Inertia::render('Admin/Messages', [
            'messages' => $messages,
        ]);
    }

    /**
     * Assign lab order to a specific lab
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignLabOrder(Request $request, $id)
    {
        try {
            $request->validate([
                'lab_id' => 'required|exists:labs,id',
            ]);

            $labOrder = LabOrder::findOrFail($id);

            // Update the lab assignment
            $labOrder->update([
                'lab_id' => $request->lab_id,
                'status' => $labOrder->status === 'pending' ? 'in_progress' : $labOrder->status,
            ]);

            // Load relationships for response
            $labOrder->load(['lab', 'appointment.patient.user', 'doctor.user']);

            return response()->json([
                'success' => true,
                'message' => 'Lab order assigned successfully.',
                'order' => [
                    'id' => $labOrder->id,
                    'lab' => $labOrder->lab->name ?? 'N/A',
                    'lab_id' => $labOrder->lab_id,
                    'doctor' => $labOrder->doctor && $labOrder->doctor->user
                        ? $labOrder->doctor->user->name
                        : ($labOrder->doctor ? 'Dr. '.$labOrder->doctor->first_name.' '.$labOrder->doctor->last_name : 'N/A'),
                    'patient' => $labOrder->appointment && $labOrder->appointment->patient
                        ? ($labOrder->appointment->patient->name ?? 'Patient')
                        : 'N/A',
                    'status' => $labOrder->status,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error assigning lab order: '.$e->getMessage(), [
                'order_id' => $id,
                'lab_id' => $request->lab_id ?? null,
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign lab order: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Assign pharmacy order to a specific pharmacy
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignPharmacyOrder(Request $request, $id)
    {
        try {
            $request->validate([
                'pharmacy_id' => 'required|exists:pharmacies,id',
            ]);

            $pharmacyOrder = PharmacyOrder::findOrFail($id);

            // Update the pharmacy assignment
            $pharmacyOrder->update([
                'pharmacy_id' => $request->pharmacy_id,
                'status' => $pharmacyOrder->status === 'pending' ? 'accepted' : $pharmacyOrder->status,
            ]);

            // Load relationships for response
            $pharmacyOrder->load(['pharmacy', 'appointment.patient.user', 'doctor.user']);

            // Get patient name from appointment relationship
            $patientName = 'N/A';
            if ($pharmacyOrder->appointment && $pharmacyOrder->appointment->patient) {
                $patient = $pharmacyOrder->appointment->patient;
                $patientName = $patient->name ?? ($patient->user ? $patient->user->name : 'Patient');
            }

            // Get doctor name
            $doctorName = 'N/A';
            if ($pharmacyOrder->doctor) {
                if ($pharmacyOrder->doctor->user) {
                    $doctorName = $pharmacyOrder->doctor->user->name;
                } else {
                    $firstName = $pharmacyOrder->doctor->first_name ?? '';
                    $lastName = $pharmacyOrder->doctor->last_name ?? '';
                    $doctorName = trim($firstName.' '.$lastName) ?: 'Dr. Unknown';
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Pharmacy order assigned successfully.',
                'order' => [
                    'id' => $pharmacyOrder->id,
                    'order_id' => 'ORD-'.substr($pharmacyOrder->id, 0, 8),
                    'pharmacy' => $pharmacyOrder->pharmacy->name ?? 'N/A',
                    'pharmacy_id' => $pharmacyOrder->pharmacy_id,
                    'doctor' => $doctorName,
                    'patient' => $patientName,
                    'status' => $pharmacyOrder->status,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error assigning pharmacy order: '.$e->getMessage(), [
                'order_id' => $id,
                'pharmacy_id' => $request->pharmacy_id ?? null,
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign pharmacy order: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logout user from all sessions and clear cache
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logoutAllSessionsAndClearCache(Request $request)
    {
        $user = $request->user();

        // Logout from all sessions by deleting all sessions for this user
        DB::table('sessions')->where('user_id', $user->id)->delete();

        // Clear the application cache
        Cache::flush();

        return redirect()->back()->with('success', 'User logged out from all sessions and cache cleared successfully.');
    }

    /**
     * Update .env file with new values
     *
     * @param  array  $data  Key-value pairs to update
     * @return void
     */
    protected function setEnvValue(array $data)
    {
        $envPath = base_path('.env');
        $envContents = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            // Quote the value if it contains special characters
            if (str_contains($value, ' ') || str_contains($value, '=') || str_contains($value, '"')) {
                $value = '"'.addslashes($value).'"';
            }

            // Check if the key already exists in .env
            if (preg_match("/^{$key}=/m", $envContents)) {
                // Replace existing value
                $envContents = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$value}",
                    $envContents
                );
            } else {
                // Add new key-value pair
                $envContents .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envPath, $envContents);
    }

    /**
     * Get quick appointments for calendar dropdown
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function quickAppointments(Request $request)
    {
        try {
            $user = auth()->user();
            $hospital = Hospital::where('user_id', $user->id)->first();
            $hospitalId = $hospital?->id;

            // Get today's date
            $today = now()->toDateString();

            // Get appointments for the next 30 days
            $endDate = now()->addDays(30)->toDateString();

            // Build query
            $appointmentsQuery = Appointment::with(['patient.user', 'doctor.user', 'doctor.specialities'])
                ->when($hospitalId, function ($q) use ($hospitalId) {
                    $q->whereHas('doctor', function ($query) use ($hospitalId) {
                        $query->where('hospital_id', $hospitalId);
                    });
                })
                ->whereBetween('appointment_date', [$today, $endDate])
                ->whereNotIn('status', ['cancelled'])
                ->orderBy('appointment_date', 'asc')
                ->orderBy('appointment_time', 'asc')
                ->limit(50);

            $appointments = $appointmentsQuery->get();

            // Format appointments for dropdown
            $formattedAppointments = $appointments->map(function ($apt) {
                // Get raw time value to avoid Carbon formatting issues
                $rawTime = $apt->getOriginal('appointment_time');

                return [
                    'id' => $apt->id,
                    'patient_name' => $apt->patient->user->name ?? 'Unknown Patient',
                    'doctor_name' => $apt->doctor->user->name ?? 'Unknown Doctor',
                    'appointment_date' => $apt->appointment_date,
                    'appointment_time' => $rawTime,
                    'status' => $apt->status,
                    'type' => $apt->appointment_type ?? 'General',
                    'mode' => $apt->appointment_mode ?? 'In-person',
                ];
            });

            return response()->json([
                'success' => true,
                'appointments' => $formattedAppointments,
                'todayCount' => $formattedAppointments->where('appointment_date', $today)->count(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching quick appointments: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch appointments',
                'appointments' => [],
                'todayCount' => 0,
            ], 500);
        }
    }
}
