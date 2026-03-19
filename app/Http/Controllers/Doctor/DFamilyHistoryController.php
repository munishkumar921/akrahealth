<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\OtherHistory;
use App\Services\OtherHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Symfony\Component\Yaml\Yaml;

class DFamilyHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $keyword = $request->get('search', '');

        $patientId = auth()->user()->doctor->selected_patient_id;
        $doctorId = auth()->user()->doctor->id ?? null;

        // Paginate and filter
        $familyHistories = OtherHistory::where('patient_id', $patientId)
            ->when($keyword, function ($query, $keyword) {
                $query->where('oh_fh', 'like', "%{$keyword}%");
            })
            ->orderByDesc('id')
            ->paginate(request('per_page', paginateLimit()))->withQueryString();    // Convert YAML data to arrays
        $familyHistories->getCollection()->transform(function ($history) {
            if (! empty($history->oh_fh)) {
                try {
                    $history->oh_fh = Yaml::parse($history->oh_fh);
                } catch (\Exception $e) {
                    $history->oh_fh = [];
                }
            } else {
                $history->oh_fh = [];
            }

            return $history;
        });

        return Inertia::render('Doctors/Patient/FamilyHistory', [
            'familyHistory' => $familyHistories,
            'keyword' => $keyword,
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
    public function store(Request $request)
    {
        $obj = new OtherHistoryService;
        $obj->store(request()->all());

        return redirect()->route('doctor.family-history.index')->with('success', 'Family History saved Successfully');

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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            OtherHistory::where('id', $id)->delete();

            return Redirect::route('doctor.family-history.index')->with('success', 'The selected Family History has been deleted successfully.');
        } catch (\Exception) {
            return Redirect::back()->with('error', 'An error occurred while deleting the Family History.');

        }
    }
}
