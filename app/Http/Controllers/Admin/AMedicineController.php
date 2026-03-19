<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Services\MedicineService;
use Illuminate\Http\Request;

class AMedicineController extends Controller
{
    public $medicineService;

    public function __construct(MedicineService $medicineService)
    {
        $this->medicineService = $medicineService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $medicines = $this->medicineService->list(request());
        $request = request();
        $keyword = $request->get('keyword') ?? '';

        return inertia('Admin/Manage/MedicinesList', compact('medicines', 'request', 'keyword'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $medicine = new Medicine;
        $data = $this->medicineService->getFormData();

        return inertia('Admin/Manage/MedicineCreate', compact('medicine', 'data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->medicineService->upsert($request->all());

        return redirect()->route('admin.medicines.index')->with('success', 'Medicine saved successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $medicine = Medicine::where('id', $id)->firstOrFail();
        $data = $this->medicineService->getFormData();

        return inertia('Admin/Manage/MedicineCreate', compact('medicine', 'data'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            Medicine::where('id', $id)->delete();

            return redirect()->route('admin.medicines.index')->with('success', 'Medicine deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.medicines.index')->with('error', 'Error deleting medicine: '.$e->getMessage());
        }
    }
}
