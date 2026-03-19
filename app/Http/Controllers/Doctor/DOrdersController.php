<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\LabOrderRequest;
use App\Models\Encounter;
use App\Models\Insurance;
use App\Models\Lab;
use App\Models\LabTestCategory;
use App\Models\Order;
use App\Models\Speciality;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DOrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $doctor = auth()->user()->doctor;
        if (! $doctor) {
            abort(403, 'Unauthorized Access');
        }

        $orders = Order::with('patient.user', 'doctor.user', 'encounter')->where('doctor_id', $doctor->id)->where('patient_id', $doctor->selected_patient_id)->get();
        $patientId = $doctor->selected_patient_id ?? null;
        $patient = $patientId ? \App\Models\Patient::with('user')->find($patientId) : null;

        $data = [
            'doctorId' => $doctor->id,
            'patientId' => $patientId,
            'specialties' => Speciality::select('id', 'name')->orderBy('name', 'ASC')->get(),
        ];

        return Inertia::render('Doctors/Patient/Orders/Index', [
            'orders' => $orders,
            'patient' => $patient,
            'data' => $data,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $type = $request->query('type', 'labs'); // default to labs

        $doctorId = auth()->user()->doctor->id ?? null;
        $patientId = auth()->user()->doctor->selected_patient_id ?? null;

        $encounterId = Encounter::where('patient_id', $patientId)
            ->where('doctor_id', $doctorId)
            ->whereDate('created_at', Carbon::today('America/New_York'))
            ->latest()
            ->value('id');
        if (! $encounterId) {
            return redirect()->route('doctor.encounters.create')->with('error', 'Creating an encounter first to add a prescription.');
        }
        $insurances = Insurance::orderBy('insurance_company', 'ASC')->where('patient_id', $patientId)->get();
        $labCategory = LabTestCategory::select('id', 'name')->orderBy('name', 'ASC')->get();
        $labs = Lab::select('id', 'name')->where('hospital_id', auth()->user()->doctor?->hospital_id)->where('is_active', true)->orderBy('name', 'ASC')->get();
        $radiologies = Lab::where('hospital_id', auth()->user()->doctor?->hospital_id)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('categories', 'like', '%Radiology%');
            })
            ->orderBy('name', 'ASC')
            ->get();
        $cardiopulmonary = Lab::where('hospital_id', auth()->user()->doctor?->hospital_id)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('categories', 'like', '%Cardiology%')
                    ->orWhere('categories', 'like', '%Cardiopulmonary%');
            })
            ->orderBy('name', 'ASC')
            ->get();

        $imaging = Lab::where('hospital_id', auth()->user()->doctor?->hospital_id)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('categories', 'like', '%Imaging%');
            })
            ->orderBy('name', 'ASC')
            ->get();

        $referral = Lab::where('hospital_id', auth()->user()->doctor?->hospital_id)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('categories', 'like', '%Referral%');
            })
            ->orderBy('name', 'ASC')
            ->get();

        if ($type == 'labs') {
            return Inertia::render('Doctors/Patient/Orders/CreateLabs', [
                'data' => [
                    'labs' => $labs,
                    'doctorId' => $doctorId,
                    'patientId' => $patientId,
                    'encounterId' => $encounterId,
                    'insurances' => $insurances,
                    'labCategory' => $labCategory,
                ],
            ]);
        } elseif ($type == 'imaging') {
            return Inertia::render('Doctors/Patient/Orders/CreateImaging', [
                'data' => [
                    'imaging' => $radiologies,
                    'doctorId' => $doctorId,
                    'patientId' => $patientId,
                    'encounterId' => $encounterId,
                    'insurances' => $insurances,
                    'labCategory' => $labCategory,
                ],
            ]);
        } elseif ($type == 'cardiopulmonary') {
            return Inertia::render('Doctors/Patient/Orders/CreateCardiopulmonary', [
                'data' => [
                    'cardiopulmonary' => $cardiopulmonary,
                    'doctorId' => $doctorId,
                    'patientId' => $patientId,
                    'encounterId' => $encounterId,
                    'insurances' => $insurances,
                    'labCategory' => $labCategory,
                ],
            ]);
        } elseif ($type == 'referrals') {
            return Inertia::render('Doctors/Patient/Orders/CreateReferrals', [
                'data' => [
                    'referral' => $referral,
                    'labCategory' => $labCategory,
                    'doctorId' => $doctorId,
                    'patientId' => $patientId,
                    'encounterId' => $encounterId,
                    'insurances' => $insurances,
                ],
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LabOrderRequest $request, OrderService $obj)
    {
        $order = $obj->upsertOrder($request->all());
        if ($order) {
            return redirect()->route('doctor.orders.index');
        }

        return redirect()->back()->with('error', 'Something went wrong');
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
        $order = Order::findOrFail($id);
        $doctorId = auth()->user()->doctor->id ?? null;
        $patientId = $order->patient_id;
        $labs = Lab::where('hospital_id', auth()->user()->doctor->hospital_id)->get();
        $insurances = Insurance::orderBy('insurance_company', 'ASC')->where('patient_id', $patientId)->get();

        $data = [
            'doctorId' => $doctorId,
            'patientId' => $patientId,
            'encounterId' => $order->encounter_id,
            'insurances' => $insurances,
            'order' => $order,
            'labs' => $labs,
        ];

        if ($order->labs) {
            return Inertia::render('Doctors/Patient/Orders/CreateLabs', ['data' => $data]);
        } elseif ($order->radiology) {
            return Inertia::render('Doctors/Patient/Orders/CreateImaging', ['data' => $data]);
        } elseif ($order->cp) {
            return Inertia::render('Doctors/Patient/Orders/CreateCardiopulmonary', ['data' => $data]);
        } elseif ($order->referrals) {
            $specialties = Speciality::select('id', 'name')->orderBy('name', 'ASC')->get();
            $data['specialties'] = $specialties;

            return Inertia::render('Doctors/Patient/Orders/CreateReferrals', [
                'data' => $data,
            ]);
        }

        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LabOrderRequest $request, string $id, OrderService $obj)
    {
        $validatedData = $request->validated();
        $validatedData['id'] = $id; // Add ID for updateOrCreate
        $order = $obj->upsertOrder($validatedData);

        return redirect()->route('doctor.orders.index')->with('success', 'Order updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return redirect()->back()->with('success', 'Order deleted successfully');
    }

    public function complete(string $id)
    {
        $order = Order::findOrFail($id);
        $order->update(['is_completed' => 1]);

        return redirect()->back()->with('success', 'Order marked as completed');
    }
}
