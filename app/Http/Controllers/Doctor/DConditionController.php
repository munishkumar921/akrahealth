<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConditionRequest;
use App\Models\Encounter;
use App\Models\Issue;
use App\Services\ConditionService;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class DConditionController extends Controller
{
    /**
     * conditions
     */
    public function index(Request $request): Response
    {
        $selectedPatientId = auth()->user()->doctor->selected_patient_id ?? null;

        // If no patient is selected, return empty data
        if (! $selectedPatientId) {
            return Inertia::render('Doctors/Patient/Conditions', [
                'keyword' => $request->get('search'),
                'issues' => [
                    'data' => [],
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 15,
                    'total' => 0,
                    'links' => [],
                ],
                'encounters' => null,
            ]);
        }

        $issues = Issue::where('patient_id', $selectedPatientId);
        if ($request->has('search')) {
            $keyword = $request->get('search');

            $issues = $issues->where(function ($query) use ($keyword) {
                $query->where('issue', 'like', '%'.$keyword.'%')
                    ->orWhere('type', 'like', '%'.$keyword.'%')
                    ->orWhere('notes', 'like', '%'.$keyword.'%')
                    ->orWhere('reconcile', 'like', '%'.$keyword.'%')
                    ->orWhere('rcopia_sync', 'like', '%'.$keyword.'%');
            });
        }
        $encounters = Encounter::where('patient_id', auth()->user()->doctor->selected_patient_id ?? null)->latest()->first();
        $data = $issues->paginate(request('per_page', paginateLimit()))->withQueryString();

        return Inertia::render('Doctors/Patient/Conditions', [
            'keyword' => $request->get('search'),
            'issues' => $data,
            'encounters' => $encounters,

        ]);
    }

    /**
     * moveCondition
     *
     * @param  mixed  $id
     * @param  mixed  $type
     */
    public function moveCondition(ConditionService $obj, string $id, string $type): RedirectResponse
    {
        try {
            $obj->moveCondition($id, $type);

            return Redirect::route('doctor.conditions.index')->with('success', 'The new condition has been updated successfully.');
        } catch (\Exception $e) {
            return Redirect::back()->with('error', 'An error occurred while updating the condition.');
        }
    }

    /**
     * conditionStatus
     *
     * @param  mixed  $obj
     * @param  mixed  $id
     * @param  mixed  $type
     */
    public function conditionStatus(ConditionService $obj, string $id, string $type): RedirectResponse
    {
        try {
            $obj->status($id, $type);

            return Redirect::route('doctor.conditions.index')->with('success', 'The new condition has been updated successfully.');
        } catch (\Exception $e) {
            return Redirect::back()->with('error', 'An error occurred while updating the condition.');

        }
    }

    /**
     * store
     *
     * @param  mixed  $request
     */
    public function store(ConditionRequest $request, ConditionService $obj): RedirectResponse
    {
        if (! auth()->user()->doctor->selected_patient_id) {
            return Redirect::back()->with('error', 'Please select a patient first.');
        }
        try {
            $obj->store($request->all());

            return Redirect::route('doctor.conditions.index')->with('success', 'The new condition has been saved successfully.');
        } catch (\Exception $e) {
            Log::error('Error saving condition: '.$e->getMessage());

            return Redirect::back()->with('error', 'An error occurred while saving the condition.');
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
            Issue::where('id', $id)->delete();

            return Redirect::route('doctor.conditions.index')->with('success', 'The selected condition has been deleted successfully.');
        } catch (\Exception) {
            return Redirect::back()->with('error', 'An error occurred while deleting the condition.');

        }
    }
}
