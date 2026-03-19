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

        // Hospital directly linked to user
        $hospital = Hospital::where('user_id', $authUser->id)->firstOrFail();

        // Resolve MAIN hospital
        $mainHospitalId = $hospital->main_branch_id ?? $hospital->id;
        $hospitalIds = Hospital::where('id', $mainHospitalId)
            ->orWhere('main_branch_id', $mainHospitalId)
            ->pluck('id');

        // Get users with roles (Doctor, VA, Biller)
        $usersQuery = User::with(['roles', 'doctor.hospital', 'doctor.specialities:name', 'address', 'userSkills', 'hospital'])
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', [
                    'Doctor',
                    'Virtual Assistant',
                    'Biller',
                ]);
            })
            ->where(function ($q) use ($hospitalIds) {
                // Doctors with linked user account
                $q->whereHas('doctor', function ($d) use ($hospitalIds) {
                    $d->whereNotNull('user_id')->whereIn('hospital_id', $hospitalIds);
                })
                // OR Other staff (VA, Biller) directly linked to hospital
                    ->orWhereIn('hospital_id', $hospitalIds);
            })
            // 🔍 Search
            ->when($request->filled('Keyword'), function ($query) use ($request) {
                $s = trim($request->Keyword);
                $query->where(function ($q) use ($s) {
                    $q->where('name', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%")
                        ->orWhere('mobile', 'like', "%{$s}%");
                });
            });

        // Get doctors without user relation but linked to hospital
        $doctorsQuery = Doctor::with(['hospital', 'specialities'])
            ->whereNull('user_id')
            ->whereIn('hospital_id', $hospitalIds)
            ->when($request->filled('Keyword'), function ($query) use ($request) {
                $s = trim($request->Keyword);
                $query->where(function ($q) use ($s) {
                    $q->where('name', 'like', "%{$s}%")
                        ->orWhere('first_name', 'like', "%{$s}%")
                        ->orWhere('last_name', 'like', "%{$s}%");
                });
            });

        // Get all results and merge
        $users = $usersQuery->orderBy('name', 'ASC')->get();
        $doctors = $doctorsQuery->orderBy('name', 'ASC')->get();
        // Combine collections
        $mergedItems = $users->merge($doctors);

        // Paginate merged results
        $perPage = request('per_page', paginateLimit());
        $page = request('page', 1);
        $offset = ($page - 1) * $perPage;

        $paginatedItems = $mergedItems->slice($offset, $perPage)->values();

        $mergedCollection = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $mergedItems->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // 🔹 Get all branches under main hospital
        $branches = Hospital::where('main_branch_id', $mainHospitalId)->orWhere('id', $mainHospitalId)->get();

        return Inertia::render('Admin/Users/Index', [
            'hospitalId' => $mainHospitalId,
            'users' => $mergedCollection,
            'branches' => $branches,
            'Keyword' => $request->Keyword,
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
