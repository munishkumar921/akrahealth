<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientSupplementsRequest;
use App\Models\Encounter;
use App\Models\PatientSupplement;
use App\Services\PatientSupplementsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class DSupplementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $supplements = PatientSupplement::where('patient_id', auth()->user()->doctor->selected_patient_id ?? null);
        if ($request->has('search')) {
            $keyword = $request->get('search');
            $supplements = $supplements->where('supplement', 'Like', '%'.$keyword.'%');
        }
        $encounters = Encounter::where('patient_id', auth()->user()->doctor->selected_patient_id ?? null)->whereDate('encounter_date_of_service', today())->latest()->first();
        $supplements = $supplements->paginate(request('per_page', paginateLimit()))->withQueryString();

        return Inertia::render('Doctors/Patient/Supplements/Supplements', [
            'supplements' => $supplements,
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
    public function store(PatientSupplementsRequest $request, PatientSupplementsService $obj): RedirectResponse
    {
        if (! $request->has('encounter_id') || empty($request->encounter_id)) {
            return redirect()->route('doctor.encounters.create')->with('error', 'Please select an encounter first.');
        }
        $obj->store($request->all());

        return Redirect::route('doctor.supplements.index')->with('success', 'The new supplement has been saved successfully.');
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
     * supplementStatus
     *
     * @param  mixed  $obj
     * @param  mixed  $id
     * @param  mixed  $type
     */
    public function supplementStatus(PatientSupplementsService $obj, string $id, string $type): RedirectResponse
    {
        $obj->status($id, $type);

        return back()->with('success', 'The new supplement has been updated successfully.');
    }

    /**
     * destroy
     *
     * @param  mixed  $id
     */
    public function destroy(string $id): RedirectResponse
    {
        PatientSupplement::where('id', $id)->delete();

        return Redirect::route('doctor.supplements.index')->with('success', 'The selected supplement has been deleted successfully.');
    }
}
