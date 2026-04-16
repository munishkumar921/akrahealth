<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SpecialityRequest;
use App\Models\Speciality;
use App\Services\SpecialityService;

class ASpecialityController extends Controller
{
    protected $SpecialityService;

    public function __construct(SpecialityService $SpecialityService)
    {
        // hasAccess('manage admins');
        $this->SpecialityService = $SpecialityService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $specialities = $this->SpecialityService->list(request());

        return inertia('Admin/Manage/SpecialityList', [
            'specialities' => $specialities,
            'filters' => [
                'keyword' => request()->string('keyword')->toString() ?: request()->string('search')->toString(),
                'status' => request()->input('status', ''),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $speciality = new Speciality;

        return inertia('Admin/Manage/SpecialityCreate', compact('speciality'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SpecialityRequest $request)
    {
        $this->SpecialityService->upsert($request->all());

        return redirect()->route('admin.specialities.index')->with('success', 'Speciality saved successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $speciality = Speciality::where('id', $id)->firstOrFail();

        return inertia('Admin/Manage/SpecialityCreate', compact('speciality'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            Speciality::where('id', $id)->delete();

            return redirect()->route('admin.specialities.index')->with('success', 'Speciality deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.specialities.index')->with('error', 'Error deleting speciality: '.$e->getMessage());
        }
    }
}
