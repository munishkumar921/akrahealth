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
        $selectedPatientId = auth()->user()->doctor->selected_patient_id ?? null;
        $keyword = trim((string) $request->input('keyword', $request->input('search', '')));
        $status = trim((string) $request->input('status', ''));
        $prescriptionStatus = trim((string) $request->input('prescription_status', ''));

        $medication = Prescription::where('patient_id', $selectedPatientId);

        if ($keyword !== '') {
            $medication = $medication->where(function ($query) use ($keyword) {
                $query->where('medication', 'like', '%'.$keyword.'%')
                    ->orWhere('dosage', 'like', '%'.$keyword.'%')
                    ->orWhere('dosage_unit', 'like', '%'.$keyword.'%')
                    ->orWhere('route', 'like', '%'.$keyword.'%')
                    ->orWhere('frequency', 'like', '%'.$keyword.'%')
                    ->orWhere('reason', 'like', '%'.$keyword.'%')
                    ->orWhere('prescription', 'like', '%'.$keyword.'%')
                    ->orWhere('rcopia_sync', 'like', '%'.$keyword.'%');
            });
        }

        if ($status === 'active') {
            $medication->whereNull('date_inactive');
        }

        if ($status === 'inactive') {
            $medication->whereNotNull('date_inactive');
        }

        if ($prescriptionStatus !== '') {
            $medication->where('prescription', $prescriptionStatus);
        }

        $encounters = Encounter::where('patient_id', $selectedPatientId)->latest()->first();

        $medications = $medication
            ->orderByDesc('id')
            ->paginate(request('per_page', paginateLimit()))
            ->withQueryString();

        $medications->through(function (Prescription $prescription) {
            return array_merge($prescription->toArray(), [
                'status_label' => $prescription->date_inactive ? 'Inactive' : 'Active',
                'active_date_label' => $prescription->date_active ?: '-',
                'inactive_date_label' => $prescription->date_inactive ?: '-',
                'due_date_label' => $prescription->due_date ?: '-',
                'prescription_label' => $prescription->prescription ?: 'N/A',
            ]);
        });

        $prescriptionStatuses = Prescription::where('patient_id', $selectedPatientId)
            ->whereNotNull('prescription')
            ->where('prescription', '!=', '')
            ->distinct()
            ->orderBy('prescription')
            ->pluck('prescription')
            ->values();

        $pharmacies = Pharmacy::where('is_active', true)
            ->where('is_verified', true)
            ->select(['id', 'name'])
            ->orderBy('name', 'ASC')
            ->get();

        return Inertia::render('Doctors/Patient/Medications/Medications', [
            'medications' => $medications,
            'encounters' => $encounters,
            'pharmacies' => $pharmacies,
            'prescriptionStatuses' => $prescriptionStatuses,
            'filters' => [
                'keyword' => $keyword,
                'status' => $status,
                'prescription_status' => $prescriptionStatus,
            ],
        ]);
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
