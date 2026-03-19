<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Speciality;
use App\Services\DoctorService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Throwable;

class ADoctorController extends Controller
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
        $this->userService->role = 'Doctor';

    }

    /**
     * index
     *
     * @return void
     */
    public function index(DoctorService $doctorService)
    {
        $doctors = $doctorService->list(request());
        $request = request();
        $keyword = $request->get('keyword') ?? '';

        return inertia('Admin/DoctorList', compact('doctors', 'request', 'keyword'));
    }

    /**
     * create
     *
     * @return void
     */
    public function create()
    {
        $specialities = Speciality::where('type', 'Doctor')->orderBy('name', 'asc')->select('id', 'name')->get();

        $doctor = new Doctor;

        return inertia('Admin/DoctorCreate', compact('specialities', 'doctor'));
    }

    /**
     * store
     *
     * @param  mixed  $request
     * @return void
     */
    public function store(Request $request)
    {
        $doctor = $this->userService->upsert($request->all());

        return redirect()->route('admin.doctors.index')->with('success', __('Doctor created successfully.'));
    }

    /**
     * edit
     *
     * @param  mixed  $id
     * @return void
     */
    public function edit(string $id, DoctorService $doctorService)
    {
        $specialities = Speciality::orderBy('name', 'asc')->select('id', 'name')->get();
        $doctor = $doctorService->getDoctor($id);

        $selected_specialities_ids = $doctor->specialities->pluck('id')->toArray();
        $selected_specialities = Speciality::whereIn('id', $selected_specialities_ids)
            ->orderBy('name', 'asc')
            ->select('id', 'name')->get();

        return inertia('Admin/DoctorCreate', compact('specialities', 'doctor', 'selected_specialities'));
    }

    /**
     * update
     *
     * @param  mixed  $request
     * @param  mixed  $id
     * @return void
     */
    public function update(Request $request, string $id)
    {
        $doctor = $this->userService->upsert($request->all(), $id);

        return redirect()->route('admin.doctors.index')->with('success', __('Doctor updated successfully.'));
    }

    /**
     * destroy
     *
     * @param  mixed  $id
     * @return void
     */
    public function destroy(string $id)
    {
        try {
            $doctor = \App\Models\Doctor::findOrFail($id);
            $user = $doctor->user;

            $doctor->delete();
            $user->delete();

            return redirect()->route('admin.doctors.index')
                ->with('success', 'Doctor deleted successfully.');
        } catch (Throwable $th) {
            return redirect()->route('admin.doctors.index')
                ->with('error', 'Unable to deleted the doctor!');
        }
    }
}
