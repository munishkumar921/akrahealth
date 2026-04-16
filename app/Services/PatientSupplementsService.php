<?php

namespace App\Services;

use App\Models\Encounter;
use App\Models\PatientSupplement;

class PatientSupplementsService
{
    /**
     * store
     *
     * @param  mixed  $input
     * @return void
     */
    public function store($input)
    {
        $notificationService = app(InAppNotificationService::class);
        $data = $input;
        $encounter = Encounter::where('id', $data['encounter_id'])->first();

        $supplement = PatientSupplement::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'encounter_id' => $data['encounter_id'] ?? null,
                'doctor_id' => $encounter->doctor_id,
                'patient_id' => $encounter->patient_id,
                'date_active' => $data['date_active'] ?? null,
                'date_inactive' => $data['date_inactive'] ?? null,
                'date_prescribed' => $data['date_prescribed'] ?? null,
                'supplement' => $data['supplement'] ?? null,
                'dosage' => $data['dosage'] ?? null,
                'dosage_unit' => $data['dosage_unit'] ?? null,
                'sig' => $data['sig'] ?? null,
                'route' => $data['route'] ?? null,
                'frequency' => $data['frequency'] ?? null,
                'instructions' => $data['instructions'] ?? null,
                'quantity' => $data['quantity'] ?? null,
                'reason' => $data['reason'] ?? null,
                'reconcile' => $data['reconcile'] ?? null,
            ]);

        $notificationService->notifyPatient(
            $encounter?->patient_id,
            $notificationService->buildPayload(
                'New supplement added',
                'A supplement has been added or updated in your chart.',
                'supplement_added',
                [
                    'recipient_role' => 'Patient',
                    'patient_id' => $encounter?->patient_id,
                    'doctor_id' => $encounter?->doctor_id,
                    'action_url' => route('patient.supplements'),
                    'related_model_type' => PatientSupplement::class,
                    'related_model_id' => $supplement->id,
                ]
            )
        );

        return $supplement;
    }

    /**
     * status
     *
     * @param  mixed  $id
     * @param  mixed  $type
     * @return void
     */
    public function status(string $id, string $type)
    {
        if ($type == 'active') {
            PatientSupplement::where('id', $id)->update([
                'date_active' => null,
                'date_inactive' => null,
                'date_active' => now(),
            ]);
        } else {
            PatientSupplement::where('id', $id)->update([
                'date_active' => null,
                'date_inactive' => now(),
                'date_active' => null,
            ]);
        }
    }
}
