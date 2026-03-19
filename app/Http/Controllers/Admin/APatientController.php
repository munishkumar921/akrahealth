<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientRequest;
use App\Models\Country;
use App\Models\Doctor;
use App\Models\Patient;
use App\Services\PatientService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Throwable;

class APatientController extends Controller
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
        // hasAccess('manage patients');
        $this->userService = $userService;
        $this->userService->role = 'Patient';
    }

    /**
     * Display a listing of the resource.
     */
    public function index(PatientService $patientService)
    {
        $patients = $patientService->list(request());
        $doctors = Doctor::with('user:id,name')->where('hospital_id', auth()->user()->doctor->hospital_id)
            ->get()
            ->map(fn ($doctor) => [
                'id' => $doctor->id,
                'name' => $doctor->name ?? '',
            ]);
        $request = request();
        $keyword = $request->get('keyword') ?? '';

        return Inertia::render('Admin/Patient/Index', ['patients' => $patients, 'request' => $request, 'doctors' => $doctors, 'keyword' => $keyword]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $patient = new Patient;
        $countries = Country::select('name')->distinct()->orderBy('name')->pluck('name');

        return inertia('Admin/Patient/Create', compact('patient', 'countries'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PatientRequest $request)
    {
        $patient = $this->userService->upsert($request->all());

        return redirect()->back()->with('success', __('Patient created successfully. Verification link sent to email.'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // try {
        // Get the patient to find the associated user ID
        $patient = Patient::findOrFail($id);
        // Add the user ID to the request data for the upsert
        $data = $request->all();
        $patient = $this->userService->upsert($data);

        return redirect()->back()->with('success', __('Patient updated successfully.'));
        // } catch (\Throwable $th) {
        //     \Log::error('Patient update error: ' . $th->getMessage());
        //     return redirect()->back()->with('error', __('Unable to update patient. Please try again.'));
        // }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $patient = Patient::with('user.address')->where('id', $id)->firstOrFail();

        return inertia('Admin/Patient/Create', compact('patient'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $doctor = Patient::findOrFail($id);
            $user = $doctor->user;
            $doctor->delete();
            $user->delete();

            return redirect()->back()->with('success', 'Doctor deleted successfully.');
        } catch (Throwable $th) {
            return redirect()->back()->with('error', 'Unable to deleted the doctor!');
        }
    }
}
