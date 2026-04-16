<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\FamilyHistoryRequest;
use App\Models\OtherHistory;
use App\Services\OtherHistoryService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Illuminate\Support\Collection;
use Symfony\Component\Yaml\Yaml;

class DFamilyHistoryController extends Controller
{
    protected array $relationshipOptions = [
        'Father',
        'Mother',
        'Brother',
        'Sister',
        'Son',
        'Daughter',
        'Spouse',
        'Partner',
        'Paternal Uncle',
        'Paternal Aunt',
        'Maternal Uncle',
        'Maternal Aunt',
        'Maternal Grandfather',
        'Maternal Grandmother',
        'Paternal Grandfather',
        'Paternal Grandmother',
    ];

    protected array $genderOptions = [
        'Male',
        'Female',
        'Other',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $patientId = auth()->user()->doctor->selected_patient_id;
        $keyword = trim((string) $request->input('keyword', $request->input('search', '')));
        $relationship = trim((string) $request->input('relationship', ''));
        $gender = trim((string) $request->input('gender', ''));
        $perPage = (int) $request->input('per_page', paginateLimit());
        $page = LengthAwarePaginator::resolveCurrentPage();

        $historyEntries = OtherHistory::where('patient_id', $patientId)
            ->orderByDesc('id')
            ->get()
            ->flatMap(function (OtherHistory $history) {
                $items = [];

                if (! empty($history->oh_fh)) {
                    try {
                        $items = Yaml::parse($history->oh_fh) ?: [];
                    } catch (\Exception $e) {
                        $items = [];
                    }
                }

                return collect($items)->map(function ($entry, $index) use ($history) {
                    $normalized = $this->normalizeFamilyHistoryEntry($entry);

                    return [
                        'id' => $history->id,
                        'row_id' => $history->id.'-'.$index,
                        'index' => $index,
                        'name' => $normalized['name'] ?? null,
                        'relationship' => $normalized['relationship'] ?? null,
                        'living_status' => $normalized['living_status'] ?? null,
                        'gender' => $normalized['gender'] ?? null,
                        'dob' => $normalized['dob'] ?? null,
                        'marital_status' => $normalized['marital_status'] ?? null,
                        'mother' => $normalized['mother'] ?? null,
                        'father' => $normalized['father'] ?? null,
                        'medical_history' => $normalized['medical_history'] ?? [],
                        'created_label' => optional($history->created_at)->format('M d, Y'),
                    ];
                });
            })
            ->values();

        $filteredEntries = $historyEntries
            ->when($keyword !== '', function (Collection $collection) use ($keyword) {
                $needle = mb_strtolower($keyword);

                return $collection->filter(function (array $row) use ($needle) {
                    $medicalHistory = is_array($row['medical_history'] ?? null)
                        ? implode(' ', $row['medical_history'])
                        : (string) ($row['medical_history'] ?? '');

                    $haystack = implode(' ', array_filter([
                        $row['name'] ?? null,
                        $row['relationship'] ?? null,
                        $row['living_status'] ?? null,
                        $row['gender'] ?? null,
                        $row['dob'] ?? null,
                        $row['marital_status'] ?? null,
                        $medicalHistory,
                    ]));

                    return str_contains(mb_strtolower($haystack), $needle);
                });
            })
            ->when($relationship !== '', function (Collection $collection) use ($relationship) {
                return $collection->filter(fn (array $row) => strcasecmp((string) ($row['relationship'] ?? ''), $relationship) === 0);
            })
            ->when($gender !== '', function (Collection $collection) use ($gender) {
                return $collection->filter(fn (array $row) => strcasecmp((string) ($row['gender'] ?? ''), $gender) === 0);
            })
            ->values();

        $familyHistories = new LengthAwarePaginator(
            $filteredEntries->forPage($page, $perPage)->values(),
            $filteredEntries->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return Inertia::render('Doctors/Patient/FamilyHistory', [
            'familyHistory' => $familyHistories,
            'filters' => [
                'keyword' => $keyword,
                'relationship' => $relationship,
                'gender' => $gender,
            ],
            'relationshipOptions' => $this->relationshipOptions,
            'genderOptions' => $this->genderOptions,
        ]);
    }

    protected function normalizeFamilyHistoryEntry(array $item): array
    {
        $map = [
            'Name' => 'name',
            'Relationship' => 'relationship',
            'Status' => 'living_status',
            'Gender' => 'gender',
            'Date of Birth' => 'dob',
            'Marital Status' => 'marital_status',
            'Mother' => 'mother',
            'Father' => 'father',
            'Medical' => 'medical_history',
        ];

        $normalized = [];

        foreach ($item as $key => $value) {
            $normalized[$map[$key] ?? $key] = $value;
        }

        if (! isset($normalized['medical_history'])) {
            $normalized['medical_history'] = [];
        }

        if (is_string($normalized['medical_history'])) {
            $normalized['medical_history'] = array_values(array_filter(array_map('trim', explode(',', $normalized['medical_history']))));
        }

        if (! is_array($normalized['medical_history'])) {
            $normalized['medical_history'] = [];
        }

        return $normalized;
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
    public function store(FamilyHistoryRequest $request)
    {
        $obj = new OtherHistoryService;
        $obj->store($request->all());

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
