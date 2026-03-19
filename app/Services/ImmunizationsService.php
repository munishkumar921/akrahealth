<?php

namespace App\Services;

use App\Models\Immunization;

class ImmunizationsService
{
    /**
     * store
     *
     * @param  mixed  $input
     * @return void
     */
    public function store($input)
    {
        Immunization::updateOrCreate(
            ['id' => $input['id'] ?? null],
            [
                'encounter_id' => $input['encounter_id'] ?? null,
                'patient_id' => auth()->user()->doctor?->selected_patient_id,
                'doctor_id' => auth()->user()->doctor?->id,
                'current_procedural_terminology_id' => null,
                'date' => $input['date'] ?? null,
                'immunization' => $input['immunization'] ?? null,
                'sequence' => $input['sequence'] ?? null,
                'body_site' => $input['body_site'] ?? null,
                'dosage' => $input['dosage'] ?? null,
                'dosage_unit' => $input['dosage_unit'] ?? null,
                'route' => $input['route'] ?? null,
                'elsewhere' => $input['elsewhere'] ?? null,
                'vis' => $input['vis'] ?? null,
                'manufacturer' => $input['manufacturer'] ?? null,
                'expiration' => $input['expiration'] ?? null,
                'reconcile' => $input['action'] ?? null,
            ]
        );

    }
}
