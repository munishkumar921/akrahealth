<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LabRequest;
use App\Models\Country;
use App\Models\Lab;
use App\Models\LabTestCategory;
use App\Models\Speciality;
use App\Models\State;
use App\Services\LabService;
use App\Services\UserService;
use Illuminate\Support\Facades\Redirect;

class ALabController extends Controller
{
    protected $labService;

    protected $userService;

    /**
     * __construct
     *
     * @param  mixed  $labService
     * @return void
     */
    public function __construct(LabService $labService, UserService $userService)
    {

        $this->labService = $labService;
        $this->userService = $userService;
        $this->userService->role = 'Lab';

    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $labs = $this->labService->list(request());
        $request = request();
        $keyword = $request->get('keyword') ?? '';
        $labCategory = LabTestCategory::select('id', 'name', 'is_active')->where('is_active', true)->get();

        return inertia('Admin/Labs/LabList', [
            'labs' => $labs,
            'request' => $request,
            'keyword' => $keyword,
            'labCategory' => $labCategory,

        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $lab = new Lab;
        $specialities = Speciality::select('id', 'name', 'is_active')->where('type', 'Laboratory')->where('is_active', true)->get();

        return inertia('Admin/Labs/LabCreate', [
            'lab' => $lab,
            'states' => State::select('id', 'name')->get(),
            'countries' => Country::select('id', 'name')->get(),
            'specialities' => $specialities,
        ]);
    }

    /**
     * Show the form for edit resource.
     */
    public function edit(string $id)
    {
        $lab = Lab::with(['user', 'country', 'state', 'specialities'])->where('id', $id)->firstOrFail();
        $specialities = Speciality::select('id', 'name', 'is_active')->where('type', 'Laboratory')->where('is_active', true)->get();

        return inertia('Admin/Labs/LabCreate', compact('lab', 'specialities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LabRequest $request)
    {
        $user = $this->userService->upsert($request->all());

        return Redirect::route('admin.labs.index')->with('success', 'Lab created successfully!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LabRequest $request, string $id)
    {
        $data = $request->all();

        $userData = $data;
        $userData['id'] = $request->user_id; // Pass the User ID as 'id' for UserService
        $this->userService->upsert($userData);

        $labData = $data;
        $labData['id'] = $id; // Pass the Lab ID as 'id' for LabService
        $this->labService->saveLab($labData);

        return Redirect::route('admin.labs.index')->with('success', 'Lab updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Lab::where('id', $id)->delete();

        return Redirect::route('admin.labs.index')->with('success', 'Lab deleted successfully!');
    }
}
