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
        $request = request();
        $keyword = $request->get('keyword') ?? '';
        $categories = LabTestCategory::select(
            ['id', 'name']
        )->orderBy('name', 'asc')->get();

        return inertia('Admin/Labs/LabTestsList', compact('tests', 'request', 'keyword', 'categories'));
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
