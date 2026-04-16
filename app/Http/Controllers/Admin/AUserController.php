<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\User;
use App\Services\DoctorService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AUserController extends Controller
{
    protected $userService;

    /**
     * __construct
     *
     * @param  mixed  $userService
     * @return void
     */
    public function __construct(UserService $userService)
    {
        // hasAccess('manage doctors');
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     */
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $authUser = auth()->user();
        $hospital = Hospital::where('user_id', $authUser->id)->firstOrFail();

        // Resolve MAIN hospital
        $mainHospitalId = $hospital->main_branch_id ?? $hospital->id;
        $hospitalIds = Hospital::where('id', $mainHospitalId)
            ->orWhere('main_branch_id', $mainHospitalId)
            ->pluck('id');
        $users = $this->userService->listAdminUsers($request, $hospitalIds);

        // 🔹 Get all branches under main hospital
        $branches = Hospital::where('main_branch_id', $mainHospitalId)->orWhere('id', $mainHospitalId)->get();

        return Inertia::render('Admin/Users/Index', [
            'hospitalId' => $mainHospitalId,
            'users' => $users,
            'branches' => $branches,
            'filters' => [
                'keyword' => trim((string) $request->input('keyword', $request->input('Keyword', ''))),
                'role' => (string) $request->input('role', ''),
                'status' => $request->input('status', ''),
                'branch_id' => (string) $request->input('branch_id', ''),
                'speciality' => (string) $request->input('speciality', ''),
            ],
            'specialities' => \App\Models\Speciality::select('name')
                ->distinct()
                ->orderBy('name')
                ->pluck('name'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return \Inertia\Inertia::render('Admin/Create');
    }

    public function patientCreate()
    {
        return \Inertia\Inertia::render('Admin/PatientCreate');
    }

    public function doctorCreate()
    {
        return \Inertia\Inertia::render('Admin/DoctorCreate');
    }

    public function labCreate()
    {
        return \Inertia\Inertia::render('Admin/LabCreate');
    }

    public function pharmacyCreate()
    {
        return \Inertia\Inertia::render('Admin/PharmacyCreate');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        /* Check User limit per plan start */
        $plan = strtolower(auth()->user()->userSubscription->subscriptionPlan->title);
        $hospital_id = auth()->user()->hospital->id;
        if ($plan == 'starter') {

            $assistantCount = User::where('hospital_id', $hospital_id)->role('Virtual Assistant')->count();
            if ($assistantCount >= 2) {
                return redirect()->route('admin.users.index')->with('error', 'You have reached the limit of 2 assistants for this plan. Upgrade to add more.');
            }

            if ($request->role != 'Virtual Assistant') {
                return redirect()->route('admin.users.index')->with('error', 'You can only create 2 assistants under this plan.');
            }
        }

        if ($plan == 'growth') {

            $doctorCount = Doctor::where('hospital_id', $hospital_id)->count();
            if ($doctorCount >= 5) {
                return redirect()->route('admin.users.index')->with('error', 'You have reached the limit of 2 doctors for this plan. Upgrade to add more.');
            }

            $count = User::where('hospital_id', $hospital_id)->role($request->role)->count();

            if ($count >= 1) {
                return redirect()->route('admin.users.index')->with('error', "Only 1 {$request->role} is allowed under this plan.");
            }
        }
        /* Check User limit per plan end */

        $this->userService->role = request()->input('role');
        if ($request->id && is_string(request()->input('role')) && str_contains(request()->input('role'), ',')) {
            $roles = array_map('trim', explode(',', request()->input('role')));
            if (in_array('Doctor', $roles) && $request->filled('doctorId')) {
                $data = $request->all();
                $data['user_id'] = $request->input('id') ?? null;
                (new DoctorService)->saveDoctor($data);
            }
        } else {
            $user = $this->userService->upsert($request->all());
        }

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
