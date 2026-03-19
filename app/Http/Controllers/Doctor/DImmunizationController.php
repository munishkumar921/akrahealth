<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImmunizationsRequest;
use App\Models\Encounter;
use App\Models\Immunization;
use App\Services\ImmunizationsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class DImmunizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $immunization = Immunization::where('patient_id', auth()->user()->doctor->selected_patient_id ?? null);
        if ($request->has('search')) {
            $keyword = $request->get('search');
            $immunization = $immunization->where('immunization', 'Like', '%'.$keyword.'%');
        }
        $immunizations = $immunization->paginate(request('per_page', paginateLimit()))->withQueryString();
        $encounters = Encounter::where('patient_id', auth()->user()->doctor->selected_patient_id ?? null)->latest()->first();

        return Inertia::render('Doctors/Patient/Immunizations/Immunizations', [
            'immunizations' => $immunizations,
            'encounters' => $encounters,
            'keyword' => $request->get('search'),
            'immunizationRoute' => config('route'),
            'bodyside' => config('bodyside'),
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
    public function store(ImmunizationsRequest $request, ImmunizationsService $obj): RedirectResponse
    {
        if (! $request->has('encounter_id') || empty($request->encounter_id)) {
            return redirect()->route('doctor.encounters.create')->with('error', 'Please select an encounter first.');
        }

        $obj->store($request->all());

        return Redirect::route('doctor.immunizations.index')->with('success', 'The new immunization has been saved successfully.');

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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            Immunization::where('id', $id)->delete();

            return Redirect::route('doctor.immunizations.index')->with('success', 'The selected immunization has been deleted successfully.');
        } catch (\Exception $e) {
            return Redirect::back()->with('error', 'An error occurred while deleting the immunization.');
        }

    }
}
