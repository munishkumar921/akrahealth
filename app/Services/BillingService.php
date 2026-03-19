<?php

namespace App\Services;

use App\Models\BillingCore;
use App\Models\Encounter;

class BillingService
{
    /**
     * store
     *
     * @param  mixed  $input
     * @return void
     */
    public function store($input)
    {
        $billingCore = BillingCore::updateOrCreate(
            ['id' => $input['id'] ?? null],
            [
                'encounter_id' => $input['encounter_id'],
                'patient_id' => $input['patient_id'],
                'hospital_id' => $input['hospital_id'],
                'other_billing_id' => $input['other_billing_id'],
                'dos_f' => $input['dos_f'],
                'payment' => $input['payment'],
                'payment_type' => $input['payment_type'],
            ]
        );

        /* update appointment fee in the appointment module */
        $encounter = Encounter::with(['appointment'])->where('id', $input['encounter_id'])->first();
        if ($encounter && $encounter->appointment && $billingCore->cpt_charge) {
            $encounter->appointment->update([
                'fee_amount' => $billingCore->cpt_charge - $input['payment'],
                'total_amount' => $billingCore->cpt_charge - $input['payment'],
            ]);
        }
    }
}
