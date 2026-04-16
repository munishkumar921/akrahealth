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
        $selectedPatientId = auth()->user()->doctor->selected_patient_id ?? null;
        $keyword = trim((string) $request->input('keyword', $request->input('search', '')));
        $status = trim((string) $request->input('status', ''));
        $route = trim((string) $request->input('route_name', ''));

        $supplements = PatientSupplement::where('patient_id', $selectedPatientId);

        if ($keyword !== '') {
            $supplements = $supplements->where(function ($query) use ($keyword) {
                $query->where('supplement', 'like', '%' . $keyword . '%')
                    ->orWhere('dosage', 'like', '%' . $keyword . '%')
                    ->orWhere('dosage_unit', 'like', '%' . $keyword . '%')
                    ->orWhere('sig', 'like', '%' . $keyword . '%')
                    ->orWhere('route', 'like', '%' . $keyword . '%')
                    ->orWhere('frequency', 'like', '%' . $keyword . '%')
                    ->orWhere('instructions', 'like', '%' . $keyword . '%')
                    ->orWhere('reason', 'like', '%' . $keyword . '%');
            });
        }

        if ($status === 'active') {
            $supplements->whereNull('date_inactive');
        }

        if ($status === 'inactive') {
            $supplements->whereNotNull('date_inactive');
        }

        if ($route !== '') {
            $supplements->where('route', $route);
        }

        $encounters = Encounter::where('patient_id', $selectedPatientId)
            ->whereDate('encounter_date_of_service', today())
            ->latest()
            ->first();

        $supplements = $supplements
            ->orderByDesc('id')
            ->paginate(request('per_page', paginateLimit()))
            ->withQueryString();

        $supplements->through(function (PatientSupplement $supplement) {
            return array_merge($supplement->toArray(), [
                'status_label' => $supplement->date_inactive ? 'Inactive' : 'Active',
                'active_date_label' => $supplement->date_active?->format('d M, Y') ?? '-',
                'inactive_date_label' => $supplement->date_inactive?->format('d M, Y') ?? '-',
            ]);
        });

        $routeOptions = PatientSupplement::where('patient_id', $selectedPatientId)
            ->whereNotNull('route')
            ->where('route', '!=', '')
            ->distinct()
            ->orderBy('route')
            ->pluck('route')
            ->values();

        return Inertia::render('Doctors/Patient/Supplements/Supplements', [
            'supplements' => $supplements,
            'encounters' => $encounters,
            'routeOptions' => $routeOptions,
            'filters' => [
                'keyword' => $keyword,
                'status' => $status,
                'route_name' => $route,
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
