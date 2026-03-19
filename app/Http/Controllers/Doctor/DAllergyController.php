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
        $allergies = Allergy::where('patient_id', auth()->user()->doctor->selected_patient_id ?? null);

        if ($request->has('search')) {

            $keyword = $request->get('search');
            $allergies = $allergies->where('allergies_medicine', 'Like', '%'.$keyword.'%');
        }
        $allergies = $allergies->paginate(request('per_page', paginateLimit()))->withQueryString();
        $encounters = Encounter::where('patient_id', auth()->user()->doctor->selected_patient_id ?? null)->latest()->first();

        return Inertia::render('Doctors/Patient/Allergies', [
            'allergies' => $allergies,
            'encounters' => $encounters,
            'keyword' => $request->get('search'),
            'route' => config('route'),
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
