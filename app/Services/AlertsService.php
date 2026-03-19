<?php

namespace App\Services;

use App\Models\Alert;

use function Symfony\Component\Clock\now;

class AlertsService
{
    /**
     * store
     *
     * @param  mixed  $input
     * @return void
     */
    public function store($input)
    {
        try {
            Alert::updateOrCreate(
                ['id' => $input['id'] ?? null],
                [
                    'patient_id' => auth()->user()->doctor?->selected_patient_id,
                    'doctor_id' => auth()->user()->doctor?->id,
                    'hospital_id' => auth()->user()->doctor?->hospital_id,
                    'alert' => $input['alert'],
                    'description' => $input['description'] ?? null,
                    'date_active' => $input['date_active'] ?? null,
                    'message_sent' => $input['message_sent'] ?? null,
                ]
            );
        } catch (\Exception $e) {
            // Handle the error, for example by logging or returning a response
            \Log::error('Error updating or creating alert: '.$e->getMessage());

            return response()->json(['error' => 'Failed to update or create alert'], 500);
        }
    }

    /**
     * status
     *
     * @param  mixed  $slug
     * @param  mixed  $type
     * @return void
     */
    public function status($input)
    {
        $alert = Alert::findOrFail($input['id']);
        if ($input['type'] === 'completed') {
            $alert->update([
                'date_complete' => now(),
                'why_not_complete' => null,
                'date_active' => null,
            ]);
        } elseif ($input['type'] === 'inactive') {
            $alert->update([
                'date_complete' => null,
                'why_not_complete' => $input['why_not_complete'],
            ]);
        } elseif ($input['type'] === 'active') {
            $alert->update([
                'why_not_complete' => null,
                'date_active' => now(),
                'date_complete' => null,
            ]);
        }
    }
}
