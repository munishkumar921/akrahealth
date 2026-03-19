<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Billing;
use App\Models\BillingCore;
use App\Models\Encounter;
use App\Models\Invoice;
use Illuminate\Http\Request;

class BillingCodeController extends Controller
{
    /**
     * upsert
     *
     * @param  mixed  $request
     * @return void
     */
    public function upsert(Request $request)
    {
        $data = $request->all();
        $encounter = Encounter::where('id', $data['encounter_id'])->first();

        $billingCode = BillingCore::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'encounter_id' => $data['encounter_id'] ?? null,
                'hospital_id' => $encounter->hospital_id,
                'doctor_id' => $encounter->doctor_id,
                'patient_id' => $encounter->patient_id,
                'cpt' => $data['cpt'] ?? null,
                'cpt_charge' => $data['cpt_charge'] ?? null,
                'unit' => $data['unit'] ?? null,
                'modifier' => $data['modifier'] ?? null,
                'service_start' => $data['service_start'] ?? null,
                'service_end' => $data['service_end'] ?? null,
                'icd_pointer' => $data['icd_pointer'] ?? null,
            ]
        );

        $billing = Billing::updateOrCreate(
            ['encounter_id' => $data['encounter_id'] ?? null],
            [
                'insurance_id_1' => $data['insurance_id_1'] ?? null,
                'insurance_id_2' => $data['insurance_id_2'] ?? null,
            ]
        );

        $appointment = Appointment::where('id', $encounter->appointment_id)->first();
        if ($appointment) {
            $appointment->update([
                'fee_amount' => $data['cpt_charge'] ?? null,
                'total_amount' => $data['cpt_charge'] ?? null,
                'discount' => 0,
            ]);
        }

        /* creating invoice with pending status */
        $lastInvoice = Invoice::latest()->first();
        if ($lastInvoice) {
            $invoice_number = $lastInvoice->invoice_number + 1;
        } else {
            $invoice_number = 1000001;
        }

        Invoice::updateOrCreate(
            [
                'appointment_id' => $encounter->appointment_id,
            ],
            [
                'invoice_number' => $invoice_number,
                'patient_id' => $appointment->patient_id,
                'user_id' => $appointment->created_by,
                'doctor_id' => $appointment->doctor_id,
                'hospital_id' => $appointment?->doctor?->hospital?->id,
                'appointment_id' => $appointment->id,
                'lab_order_id' => null,
                'pharmacy_order_id' => null,
                'subscription_id' => null,
                'amount' => $appointment->fee_amount ?? 0,
                'tax_amount' => 0,
                'discount_amount' => $appointment->discount ?? 0,
                'total_amount' => $appointment->total_amount ?? 0,
                'currency' => $appointment->currency ?? 'INR',
                'status' => 'pending',
                'razorpay_invoice_id' => null,
                'razorpay_payment_id' => null,
                'razorpay_order_id' => null,
                'razorpay_customer_id' => null,
                'payment_method' => 'razorpay',
                'due_date' => now(),
                'paid_at' => now(),
                'sent_at' => now(),
                'viewed_at' => now(),
                'items' => [],
                'customer_details' => [],
                'notes' => '',
                'terms_conditions' => '',
                'created_by' => $appointment->created_by,
                'updated_by' => $appointment->created_by,
            ]
        );

        return response()->json(['success' => true, 'billingCode' => $billingCode, 'billing' => $billing]);
    }

    /**
     * get
     *
     * @param  mixed  $id
     * @return void
     */
    public function get($id)
    {
        $billingCode = BillingCore::where('encounter_id', $id)->first();
        $billing = Billing::where('encounter_id', $id)->first();

        return response()->json(['success' => true, 'billingCode' => $billingCode, 'billing' => $billing]);
    }
}
