<?php

namespace App\Services;

use App\Models\Issue;

class IssueService
{
    /**
     * store
     *
     * @param  mixed  $input
     * @return void
     */
    public function store($input)
    {
        Issue::updateOrCreate(
            ['id' => $input['id'] ?? null],
            [
                'patient_id' => auth()->user()->doctor?->selected_patient_id,
                'doctor_id' => auth()->user()->doctor?->id, // ✅ fixed
                'issue' => $input['issue'] ?? null,
                'rcopia_sync' => $input['rcopia_sync'] ?? null,
                'type' => $input['type'] ?? null,
                'reconcile' => $input['reconcile'] ?? null,
                'notes' => $input['notes'] ?? null,
                'date_active' => $input['date_active'] ?? null,
                'date_inactive' => $input['date_inactive'] ?? null,
            ]
        );

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
            Issue::where('id', $id)->update([
                'date_active' => null,
                'date_inactive' => null,
                'date_active' => now(),
            ]);
        } else {
            Issue::where('id', $id)->update([
                'date_active' => null,
                'date_inactive' => now(),
                'date_active' => null,
            ]);
        }
    }
}
