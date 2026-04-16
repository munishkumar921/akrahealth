<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\AllergiesRequest;
use App\Models\Allergy;
use App\Models\Encounter;
use App\Services\AllergiesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class DAllergyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $selectedPatientId = auth()->user()->doctor->selected_patient_id ?? null;
        $keyword = trim((string) $request->input('keyword', $request->input('search', '')));
        $status = trim((string) $request->input('status', ''));
        $severity = trim((string) $request->input('severity', ''));

        $allergies = Allergy::where('patient_id', $selectedPatientId);

        if ($keyword !== '') {
            $allergies = $allergies->where(function ($query) use ($keyword) {
                $query->where('allergies_medicine', 'like', '%'.$keyword.'%')
                    ->orWhere('allergies_reaction', 'like', '%'.$keyword.'%')
                    ->orWhere('allergies_severity', 'like', '%'.$keyword.'%')
                    ->orWhere('notes', 'like', '%'.$keyword.'%')
                    ->orWhere('rcopia_sync', 'like', '%'.$keyword.'%')
                    ->orWhere('medicine_ndcid', 'like', '%'.$keyword.'%');
            });
        }

        if ($status === 'active') {
            $allergies->whereNull('date_inactive');
        }

        if ($status === 'inactive') {
            $allergies->whereNotNull('date_inactive');
        }

        if ($severity !== '') {
            $allergies->where('allergies_severity', $severity);
        }

        $allergies = $allergies
            ->orderByDesc('id')
            ->paginate(request('per_page', paginateLimit()))
            ->withQueryString();

        $allergies->through(function (Allergy $allergy) {
            return array_merge($allergy->toArray(), [
                'status_label' => $allergy->date_inactive ? 'Inactive' : 'Active',
                'active_date_label' => $allergy->date_active ?: '-',
            ]);
        });

        $encounters = Encounter::where('patient_id', $selectedPatientId)->latest()->first();
        $severityOptions = Allergy::where('patient_id', $selectedPatientId)
            ->whereNotNull('allergies_severity')
            ->where('allergies_severity', '!=', '')
            ->distinct()
            ->orderBy('allergies_severity')
            ->pluck('allergies_severity')
            ->values();

        return Inertia::render('Doctors/Patient/Allergies', [
            'allergies' => $allergies,
            'encounters' => $encounters,
            'filters' => [
                'keyword' => $keyword,
                'status' => $status,
                'severity' => $severity,
            ],
            'severityOptions' => $severityOptions,
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
    public function store(AllergiesRequest $request, AllergiesService $obj): RedirectResponse
    {
        if (! $request->has('encounter_id') || empty($request->encounter_id)) {
            return redirect()->route('doctor.encounters.create')->with('error', 'Please select an encounter first.');
        }
        try {
            $obj->store($request->all());

            return Redirect::route('doctor.allergies.index')->with('success', 'The new allergies has been saved successfully.');
        } catch (\Exception $e) {
            return Redirect::back()->with('error', 'An error occurred while saving the allergies.');
        }

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
     * AllergyStatus
     *
     * @param  mixed  $obj
     * @param  mixed  $id
     * @param  mixed  $type
     */
    public function allergyStatus(AllergiesService $obj, string $id, string $type): RedirectResponse
    {

        try {
            $obj->status($id, $type);

            return Redirect::route('doctor.allergies.index')->with('success', 'The new Allergy has been updated successfully.');
        } catch (\Exception $e) {
            return Redirect::back()->with('error', 'An error occurred while updating the Allergy.');
        }
    }

    /**
     * destroy
     *
     * @param  mixed  $id
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            Allergy::where('id', $id)->delete();

            return Redirect::route('doctor.allergies.index')->with('success', 'The selected Allergy has been deleted successfully.');
        } catch (\Exception $e) {
            return Redirect::back()->with('error', 'An error occurred while deleting the Allergy.');
        }

    }
}
