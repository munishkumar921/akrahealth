<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LabTestCategory;
use App\Services\LabTestCategoryService;
use Illuminate\Http\Request;

class ALabTestCategoryController extends Controller
{
    public $labTestCategory;

    public function __construct(LabTestCategoryService $labTestCategory)
    {
        $this->labTestCategory = $labTestCategory;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = $this->labTestCategory->list(request());
        $request = request();
        $keyword = $request->get('keyword') ?? '';

        return inertia('Admin/Labs/LabTestCategoriesList', compact('categories', 'request', 'keyword'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $category = new LabTestCategory;

        return inertia('Admin/Manage/LabTestCategoryCreate', compact('category'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->labTestCategory->upsert($request->all());

        return redirect()->route('admin.lab-test-categories.index')->with('success', 'Lab test category saved successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = LabTestCategory::where('id', $id)->firstOrFail();

        return inertia('Admin/Manage/LabTestCategoryCreate', compact('category'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        LabTestCategory::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Lab test category deleted successfully.');
    }
}
