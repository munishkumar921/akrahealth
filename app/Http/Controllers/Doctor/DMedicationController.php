<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicationRequest;
use App\Models\Encounter;
use App\Models\Medication;
use App\Models\Pharmacy;
use App\Models\Prescription;
use App\Services\MedicationsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class DMedicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $medication = Prescription::where('patient_id', auth()->user()->doctor->selected_patient_id ?? null);
        $keyword = '';
        if ($request->has('search')) {
            $keyword = $request->get('search');
            $medication = $medication->where('medication', 'Like', '%'.$keyword.'%');
        }
        $encounters = Encounter::where('patient_id', auth()->user()->doctor->selected_patient_id ?? null)->latest()->first();

        $medications = $medication->paginate(request('per_page', paginateLimit()))->withQueryString();
        $pharmacies = Pharmacy::where('is_active', true)
            ->where('is_verified', true)
            ->select(['id', 'name'])
            ->orderBy('name', 'ASC')
            ->get();

        return Inertia::render('Doctors/Patient/Medications/Medications', compact('medications', 'keyword', 'encounters', 'pharmacies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MedicationRequest $request, MedicationsService $obj): RedirectResponse
    {
        if (! $request->has('encounter_id') || empty($request->encounter_id)) {
            return redirect()->route('doctor.encounters.create')->with('error', 'Please select an encounter first.');
        }
        $obj->store($request->all());

        return Redirect::route('doctor.medications.index')->with('success', 'The new medication has been saved successfully.');
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
     * medicationStatus
     *
     * @param  mixed  $obj
     * @param  mixed  $id
     * @param  mixed  $type
     */
    public function medicationStatus(MedicationsService $obj, string $id, string $type): RedirectResponse
    {
        $obj->status($id, $type);

        return Redirect::route('doctor.medications.index')->with('success', 'The medication status has been updated successfully.');
    }

    public function reconcileMedication(MedicationsService $obj, string $id): RedirectResponse
    {
        // try{
        $obj->reconcileMedications($id);

        return Redirect::route('doctor.medications.index')->with('success', 'Medication reconciliation completed successfully.');
        // }catch(\Exception $e){
        //     return Redirect::back()->with('error', 'An error occurred while reconciling the medication.');

        // }
    }

    /**
     * destroy
     *
     * @param  mixed  $id
     */
    public function destroy(string $id): RedirectResponse
    {
        Prescription::where('id', $id)->delete();

        return Redirect::route('doctor.medications.index')->with('success', 'The selected medication has been deleted successfully.');
    }
}
