<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Insurance;

class InsuranceService
{
    public function store($input)
    {

        $addedInsurance = Address::updateOrCreate(
            [
                'id' => $input['address_id'] ?? null,
            ],
            [
                'phone' => $input['phone'] ?? null,
                'email' => $input['email'] ?? null,
                'address_1' => $input['address'] ?? null,
                'city' => $input['city'] ?? null,
                'state' => $input['state'] ?? null,
                'zip' => $input['pincode'] ?? null,
            ]);

        Insurance::updateOrCreate([
            'id' => $input['id'] ?? null,
        ], [
            'plan_name' => $input['facility'], // Using facility as plan_name for now
            'insurance_company' => $input['facility'], // Using facility as insurance_company for now
            'address_id' => $addedInsurance->id,
            'patient_id' => auth()->user()->doctor?->selected_patient_id,

        ]);

    }
}
