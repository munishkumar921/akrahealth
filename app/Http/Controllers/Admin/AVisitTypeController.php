<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\VisitType;
use App\Services\VisitTypeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AVisitTypeController extends Controller
{
    protected $VisitTypeService;

    public function __construct(VisitTypeService $VisitTypeService)
    {
        // hasAccess('manage admins');
        $this->VisitTypeService = $VisitTypeService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $request = request();
        $keyword = $request->get('keyword') ?? '';
        $visittypes = $this->VisitTypeService->list(request());

        $doctors = [];
        $authUser = auth()->user();

        $hospital = Hospital::where('user_id', $authUser->id)->firstOrFail();
        $mainHospitalId = $hospital->main_branch_id ?? $hospital->id;
        $hospitalIds = Hospital::where('id', $mainHospitalId)
            ->orWhere('main_branch_id', $mainHospitalId)
            ->pluck('id');
        $doctors = Doctor::with('user')
            ->whereIn('hospital_id', $hospitalIds)
            ->get();

        return Inertia::render('Admin/VisitTypeList', compact('visittypes', 'request', 'keyword', 'doctors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $doctors = Doctor::with('user', 'hospital')->get();

        return Inertia::render('Admin/Appointment/VisitTypeCreate', compact('doctors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->VisitTypeService->upsert($request->all());

        return redirect()->route('admin.visit-types.index')->with('success', 'Visit Type saved successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $visittype = VisitType::where('id', $id)->firstOrFail();

        $doctors = Doctor::with('user', 'hospital')->get();

        return Inertia::render('Admin/Appointment/VisitTypeCreate', compact('visittype', 'doctors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $visitType = VisitType::findOrFail($id);
        $visitType->update($request->only(['is_active']));

        return redirect()->route('admin.visit-types.index')->with('success', 'Visit Type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            VisitType::where('id', $id)->delete();

            return redirect()->route('admin.visit-types.index')->with('success', 'Visit Type deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.visit-types.index')->with('error', 'Error deleting visit type: '.$e->getMessage());
        }
    }
}
