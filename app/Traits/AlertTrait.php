<?php

namespace App\Traits;

use App\Models\Alert;
use App\Models\Issue;
use App\Models\Medication;

trait AlertTrait
{
    /**
     * Create MTM (Medication Therapy Management) alert
     */
    protected function add_mtm_alert($patientId, $type)
    {
        $hospital = auth()->user()->doctor->hospital;
        $hospital_id = $hospital ? $hospital->id : null;
        // Validate alert type
        if (! in_array($type, ['issues', 'medications'])) {
            return false;
        }

        // Fetch active items based on type
        if ($type === 'issues') {
            $activeItem = Issue::query()
                ->where('patient_id', $patientId)
                ->whereInNull('date_inactive')
                ->first();
        }

        if ($type === 'medications') {
            $activeItem = Medication::query()
                ->where('patient_id', $patientId)
                ->whereInNull('date_inactive')
                ->whereInNull('due_date')
                ->first();
        }

        if (! $activeItem) {
            return false; // nothing active → no alert needed
        }

        // Check if MTM alert already exists
        $existing = Alert::query()
            ->where('pid', $patientId)
            ->where('hospital_id', $hospital_id)
            ->where('alert', 'Medication Therapy Management')
            ->whereInNull('date_complete')
            ->whereInNull('why_not_complete', '')
            ->first();

        if ($existing) {
            return true; // Alert already exists → do not duplicate
        }

        // Insert new alert
        Alert::create([
            'alert' => 'Medication Therapy Management',
            'description' => 'Medication therapy management is needed due to more than 2 active medications or issues.',
            'date_active' => now(),
            'date_complete' => '',
            'why_not_complete' => '',
            'patient_id' => $patientId,
            'hospital_id' => $hospital_id,
        ]);

        return true;
    }
}
