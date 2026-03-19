<?php

namespace App\Services;

use App\Models\Allergy;

class AllergiesService
{
    /**
     * store
     *
     * @param  mixed  $input
     * @return void
     */
    public function store($input)
    {
        Allergy::updateOrCreate(
            ['id' => $input['id'] ?? null],
            [
                'encounter_id' => $input['encounter_id'] ?? null,
                'patient_id' => auth()->user()->doctor?->selected_patient_id,
                'doctor_id' => auth()->user()->doctor?->id,
                'allergies_medicine' => $input['allergies_medicine'] ?? null,
                'allergies_reaction' => $input['allergies_reaction'] ?? null,
                'allergies_severity' => $input['allergies_severity'] ?? null,
                'rcopia_sync' => $input['rcopia_sync'] ?? null,
                'medicine_ndcid' => $input['medicine_ndcid'] ?? null,
                'notes' => $input['notes'] ?? null,
                'reconcile' => 'y' ?? null,
                'date_active' => $input['date_active'] ?? null,
            ]
        );
    }

    /**
     * status
     *
     * @param  mixed  $slug
     * @param  mixed  $type
     * @return void
     */
    public function status(string $slug, string $type)
    {
        if ($type == 'active') {
            Allergy::where('slug', $slug)->update([
                'date_active' => null,
                'date_inactive' => null,
                'date_active' => now(),
            ]);
        } else {
            Allergy::where('slug', $slug)->update([
                'date_active' => null,
                'date_inactive' => now(),
                'date_active' => null,
            ]);
        }
    }
}
