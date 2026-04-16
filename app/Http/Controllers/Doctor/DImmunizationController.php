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
        $selectedPatientId = auth()->user()->doctor->selected_patient_id ?? null;
        $keyword = trim((string) $request->input('keyword', $request->input('search', '')));
        $routeName = trim((string) $request->input('route_name', ''));
        $bodySite = trim((string) $request->input('body_site', ''));

        $immunization = Immunization::where('patient_id', $selectedPatientId);

        if ($keyword !== '') {
            $immunization = $immunization->where(function ($query) use ($keyword) {
                $query->where('immunization', 'like', '%'.$keyword.'%')
                    ->orWhere('dosage', 'like', '%'.$keyword.'%')
                    ->orWhere('dosage_unit', 'like', '%'.$keyword.'%')
                    ->orWhere('sequence', 'like', '%'.$keyword.'%')
                    ->orWhere('route', 'like', '%'.$keyword.'%')
                    ->orWhere('body_site', 'like', '%'.$keyword.'%')
                    ->orWhere('manufacturer', 'like', '%'.$keyword.'%')
                    ->orWhere('brand', 'like', '%'.$keyword.'%')
                    ->orWhere('cvx_code', 'like', '%'.$keyword.'%');
            });
        }

        if ($routeName !== '') {
            $immunization->where('route', $routeName);
        }

        if ($bodySite !== '') {
            $immunization->where('body_site', $bodySite);
        }

        $immunizations = $immunization
            ->orderByDesc('id')
            ->paginate(request('per_page', paginateLimit()))
            ->withQueryString();
        $encounters = Encounter::where('patient_id', $selectedPatientId)->latest()->first();

        return Inertia::render('Doctors/Patient/Immunizations/Immunizations', [
            'immunizations' => $immunizations,
            'encounters' => $encounters,
            'filters' => [
                'keyword' => $keyword,
                'route_name' => $routeName,
                'body_site' => $bodySite,
            ],
            'routeOptions' => config('route'),
            'bodySiteOptions' => config('bodyside'),
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
