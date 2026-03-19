<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\BillingCore;
use App\Models\Encounter;
use App\Models\PatientNote;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DBillingController extends Controller
{
    public function index(Request $request)
    {
        $type = $request['type'];
        $patientId = $request->patient_id ?? auth()->user()->doctor->selected_patient_id ?? null;

        if (! $patientId) {
            return Inertia::render('Doctors/Patient/Billing/Index', [
                'billingData' => [
                    'encounters' => [],
                    'misc_bills' => [],
                    'bluebutton_data' => [],
                    'error' => 'No patient selected',
                ],
            ]);
        }
        if ($type == 'misc') {
            $result = $this->getMiscBills($patientId);
        } else {
            $result = $this->getEncounterBills($patientId);
        }
        $notes = PatientNote::where('patient_id', '=', $patientId)->where('hospital_id', '=', auth()->user()->doctor->hospital_id)->first();

        return Inertia::render('Doctors/Patient/Billing/Index', [
            'billingData' => [
                'encounters' => $type != 'misc' ? $result : [],
                'misc_bills' => $type == 'misc' ? $result : [],
                'bluebutton_data' => $type != 'misc' ? $result : [],
                'type' => $type,
                'patient_id' => $patientId,

            ],
            'notes' => $notes,
            'keyword' => $request->get('keyword'),
        ]);
    }

    /**
     * Get miscellaneous bills for patient
     */
    private function getMiscBills($patientId)
    {
        $hospitalId = auth()->user()->doctor->hospital_id;

        $others = BillingCore::where('patient_id', $patientId)
            ->where('encounter_id', '0')
            ->where('payment', '0')
            ->where('hospital_id', $hospitalId)
            ->orderBy('dos_f', 'desc')
            ->get();

        $result = [];

        foreach ($others as $other) {
            $charge = $other->cpt_charge * $other->unit;

            // Get total payments for this billing item
            $payment = BillingCore::where('other_billing_id', $other->other_billing_id)
                ->sum('payment');

            $result[] = [
                'id' => $other->id,
                'patient_id' => $other->patient_id,
                'hospital_id' => $other->hospital_id,
                'date' => \Carbon\Carbon::parse($other->dos_f)->format('Y-m-d'),
                'reason' => $other->reason,
                'balance' => $charge - $payment,
                'unit' => $other->unit,
                'charges' => $other->cpt_charge,
                'total_charge' => $charge,
                'total_payment' => $payment,
                'type' => 'misc',
            ];
        }

        return $result;
    }

    /**
     * Get encounter bills for patient
     */
    private function getEncounterBills($patientId)
    {
        $encounters = Encounter::where('patient_id', $patientId)->get();
        $result = [];
        foreach ($encounters as $encounter) {
            $billingCores = BillingCore::where('encounter_id', $encounter->id)->get();
            $charge = 0;
            $payment = 0;

            foreach ($billingCores as $row) {
                $cptCharge = floatval($row->cpt_charge) ?? 0;
                $unit = intval($row->unit) ?? 0;
                $rowPayment = floatval($row->payment) ?? 0;

                $charge += $cptCharge * $unit;
                $payment += $rowPayment;

                $result[] = [
                    'id' => $row->id,
                    'encounter_id' => $encounter->id,
                    'date' => \Carbon\Carbon::parse($encounter->encounter_DOS)->format('Y-m-d'),
                    'reason' => $encounter->chief_complaint ?? 'Unknown',
                    'balance' => $charge - $payment,
                    'charges' => $charge,
                    'payments' => $payment,
                    'encounter_type' => $encounter->encounter_type ?? 'Unknown',
                    'provider' => $encounter->doctor->user->name ?? 'Unknown',
                    'type' => 'encounter',
                ];
            }
        }

        return $result;
    }

    public function store(Request $request, BillingService $obj)
    {
        $this->validate($request, [
            'dos_f' => 'required|date',
            'payment' => 'numeric|required',
        ]);
        $obj->store($request->all());

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function billing_payment_history($id, Request $request)
    {
        $keyword = $request->get('keyword', '');

        $paymentHistory = DB::table('billing_cores')
            ->where('encounter_id', $id)
            ->where('payment', '!=', '0')
            ->where(function ($query) use ($keyword) {
                if ($keyword) {
                    $query->where('dos_f', 'like', "%$keyword%")
                        ->orWhere('reason', 'like', "%$keyword%");
                }
            })
            ->orderBy('dos_f', 'desc')
            ->get();

        return Inertia::render('Doctors/Patient/Billing/PaymentHistory', [
            'paymentHistory' => $paymentHistory,
            'encounter' => Encounter::find($id),
            'search' => $keyword,
        ]);
    }

    public function billing_notes_update(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'notes' => 'required|string',
        ]);

        $hospital_id = auth()->user()->doctor->hospital_id;
        $patientId = auth()->user()->doctor->selected_patient_id ?? null;

        // Find existing note
        $note = PatientNote::where('patient_id', $patientId)
            ->where('hospital_id', $hospital_id)
            ->first();

        if ($note) {
            // Update
            $note->billing_note = $validated['notes'];
            $note->patient_id = $patientId;
            $note->save();
        } else {
            // Create if not exists
            $note = PatientNote::create([
                'patient_id' => $patientId,
                'hospital_id' => $hospital_id,
                'billing_note' => $validated['notes'],
                'imm_note' => '',
            ]);
        }

        return back()->with('success', 'Billing notes updated successfully.');
    }

    public function delete(Request $request, $id)
    {
        // Find the billing record by ID
        $billingRecord = BillingCore::find($id);
        if ($billingRecord) {
            // Delete the record
            $billingRecord->delete();

            return back()->with('success', 'Billing record deleted successfully.');
        } else {
            return back()->with('error', 'Billing record not found.');
        }
    }
}
