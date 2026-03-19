<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use App\Services\ProviderExceptionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProviderExceptionController extends Controller
{
    protected ProviderExceptionService $service;

    public function __construct(ProviderExceptionService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = [
            'keyword' => $request->input('keyword'),
            'hospital_id' => $this->service->getHospitalIdFromAdmin(),
            'per_page' => $request->input('per_page', 10),
            'sort' => $request->input('sort', 'exception_date'),
            'direction' => $request->input('direction', 'desc'),
        ];

        $data = $this->service->getList($filters);

        // Transform data for frontend
        $data->getCollection()->transform(function ($exception) {
            return $this->service->transform($exception);
        });

        $doctors = $this->service->getDoctors($filters['hospital_id']);

        return Inertia::render('Admin/ProviderException/Index', [
            'doctors' => $doctors,
            'keyword' => $filters['keyword'],
            'data' => $data,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'exception_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'title' => 'required|string|max:255',
            'doctor_id' => 'required|exists:doctors,id',
            'reason' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Get hospital ID
        $hospitalId = $this->service->getHospitalIdFromDoctor($validated['doctor_id']);
        $data = [
            'id' => $request->input('id', null),
            'exception_date' => $validated['exception_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'title' => $validated['title'],
            'doctor_id' => $validated['doctor_id'],
            'hospital_id' => $hospitalId,
            'reason' => $validated['reason'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ];

        $this->service->store($data);

        return redirect()->route('admin.provider-exception.index')
            ->with('success', __('Provider exception created successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->service->delete($id);

        return redirect()->route('admin.provider-exception.index')
            ->with('success', __('Provider exception deleted successfully.'));
    }

    /**
     * Toggle the active status of the specified resource.
     */
    public function toggleStatus(Request $request, $id)
    {
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $this->service->toggleStatus($id, $request->is_active);

        return back()->with('success', __('Provider exception status updated successfully.'));
    }
}
