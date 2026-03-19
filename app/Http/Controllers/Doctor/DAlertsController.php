<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Services\AlertsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class DAlertsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $patientId = auth()->user()->doctor->selected_patient_id ?? null;

        $alertsQuery = Alert::with('doctor')
            ->where('patient_id', $patientId);

        $keyword = $request->get('keyword', '');

        // 🔍 Keyword search
        if (! empty($keyword)) {
            $alertsQuery->where(function ($q) use ($keyword) {
                $q->where('alert', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('description', 'LIKE', '%'.$keyword.'%');
            });
        }

        // 📌 Type filter
        $type = $request->get('type', 'active');

        if ($type === 'active') {
            $alertsQuery->where('date_active', '<=', now()->addWeeks(2))
                ->whereNull('date_complete')
                ->where(function ($q) {
                    $q->whereNull('why_not_complete')
                        ->orWhere('why_not_complete', '');
                });
        }

        if ($type === 'completed') {
            $alertsQuery->whereNotNull('date_complete');
        }

        if ($type === 'inactive') {
            $alertsQuery->whereNull('date_complete')
                ->whereNotNull('why_not_complete')
                ->where('why_not_complete', '!=', '');
        }

        if ($type === 'pending') {
            $alertsQuery->where('date_active', '>', now()->addWeeks(2))
                ->whereNull('date_complete')
                ->where(function ($q) {
                    $q->whereNull('why_not_complete')
                        ->orWhere('why_not_complete', '');
                });
        }

        if ($type === 'results') {
            $alertsQuery->whereNull('date_complete')
                ->where(function ($q) {
                    $q->whereNull('why_not_complete')
                        ->orWhere('why_not_complete', '');
                })
                ->where(function ($query) {
                    $query->where('alert', '=', 'Laboratory results pending')
                        ->orWhere('alert', '=', 'Radiology results pending')
                        ->orWhere('alert', '=', 'Cardiopulmonary results pending');
                });
        }

        // 📄 Pagination
        $alerts = $alertsQuery->paginate(request('per_page', paginateLimit()))->withQueryString();

        // 🔄 Transform data safely
        $alerts->getCollection()->transform(function ($alert) {
            return [
                'id' => $alert->id,
                'date' => $alert->date_complete ?? $alert->date_active,
                'date_active' => $alert->date_active,
                'date_complete' => $alert->date_complete,
                'alert' => $alert->alert,
                'description' => $alert->description,
                'message_sent' => $alert->message_sent,
                'why_not_complete' => $alert->why_not_complete,
                'doctor' => $alert->doctor,
            ];
        });

        return Inertia::render('Doctors/Patient/Alerts/Index', [
            'alerts' => $alerts,
            'keyword' => $keyword,
            'currentTab' => $type,
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
    public function store(Request $request, AlertsService $obj): RedirectResponse
    {
        $obj->store($request->all());

        return Redirect::route('doctor.alerts.index')->with('success', 'The new allergies has been saved successfully.');
    }

    /**
     * AllergyStatus
     *
     * @param  mixed  $obj
     * @param  mixed  $slug
     * @param  mixed  $type
     */
    public function alertStatus(Request $request, AlertsService $obj): RedirectResponse
    {
        $obj->status($request->all());

        return Redirect::back()->with('success', 'The Alert status has been updated successfully.');
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
    public function update(Request $request, AlertsService $obj, string $id)
    {
        $input['id'] = $id;
        $request->merge($input);
        $obj->store($request->all());

        return Redirect::back()->with('success', 'Alert updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        Alert::where('id', $id)->delete();

        return Redirect::back()->with('success', 'The selected Alert has been deleted successfully.');
    }
}
