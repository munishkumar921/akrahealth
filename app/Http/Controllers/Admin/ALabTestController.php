<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LabTest;
use App\Models\LabTestCategory;
use App\Services\LabTestService;
use Illuminate\Http\Request;

class ALabTestController extends Controller
{
    public $labTestService;

    public function __construct(LabTestService $labTestService)
    {
        $this->labTestService = $labTestService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tests = $this->labTestService->list(request());
        $categories = LabTestCategory::select(
            ['id', 'name']
        )->orderBy('name', 'asc')->get();
        $sampleTypes = $this->labTestService->getSampleTypes();

        return inertia('Admin/Labs/LabTestsList', [
            'tests' => $tests,
            'categories' => $categories,
            'sampleTypes' => $sampleTypes,
            'filters' => [
                'keyword' => request()->string('keyword')->toString() ?: request()->string('search')->toString(),
                'status' => request()->input('status', ''),
                'category_id' => request()->input('category_id', ''),
                'sample_type' => request()->input('sample_type', ''),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $test = new LabTest;
        $categories = LabTestCategory::select(
            ['id', 'name']
        )->orderBy('name', 'asc')->get();

        return inertia('Admin/Manage/LabTestCreate', compact('test', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'hospital_id' => ['nullable'],
            'lab_test_category_id' => ['required'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'sample_type' => ['nullable', 'string'],
            'fasting_required' => ['nullable'],
            'report_time' => ['nullable'],
            "instructions" => ['required', 'string', 'max:5000'],
            "price" => ['nullable', 'numeric'],
            "discount" => ['nullable', 'numeric'],
            "final_price" => ['nullable', 'numeric'],
            "currency" => ['nullable', 'string', 'max:3'],
            "is_home_collection_available" => ['nullable'],
            "is_active" => ['nullable'],
        ]);
        $this->labTestService->upsert($request->all());
        return redirect()->route('admin.lab-tests.index')->with('success', 'Lab test saved successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        LabTest::where('id', $id)->delete();

        return redirect()->route('admin.lab-tests.index')->with('success', 'Lab test saved successfully.');
    }

    public function statusUpdate(Request $request)
    {
        $input = $request->all();

        $this->labTestService->statusUpdate($input);

        return redirect()->back()->with('success', 'Lab test status updated successfully.');
    }
}
