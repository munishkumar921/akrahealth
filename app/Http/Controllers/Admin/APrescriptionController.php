<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Services\PrescriptionService;
use Illuminate\Http\Request;

class APrescriptionController extends Controller
{
    protected $prescriptionService;

    /**
     * __construct
     *
     * @param  mixed  $prescriptionService
     * @return void
     */
    public function __construct(PrescriptionService $prescriptionService)
    {
        $this->prescriptionService = $prescriptionService;
    }

    /**
     * index
     *
     * @param  mixed  $request
     * @return void
     */
    public function index(Request $request)
    {
        $prescriptions = $this->prescriptionService->list(request());
        $request = request();
        $keyword = $request->get('keyword') ?? '';

        return inertia('Admin/PrescriptionList', compact('prescriptions', 'request', 'keyword'));
    }

    /**
     * destroy
     *
     * @param  mixed  $id
     * @return void
     */
    public function destroy(string $id)
    {
        Prescription::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Prescription deleted successfully.');
    }
}
