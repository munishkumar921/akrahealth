<?php

namespace App\Services;

use App\Models\Encounter;
use App\Models\Medication;
use App\Models\Prescription;

class MedicationsService
{
    /**
     * store
     *
     * @param  mixed  $input
     * @return void
     */
    public function store($input)
    {
        $encounter = Encounter::where('id', $input['encounter_id'])->first();
        $due_date = null;
        $days = null;
        if (! empty($input['date_active']) && isset($input['days'])) {
            $due_date = date('Y-m-d H:i:s', strtotime($input['date_active']) + ((int) $input['days'] * 86400));
        }
        if (isset($input['days'])) {
            $days = (int) $input['days'];
        }

        Prescription::updateOrCreate(
            ['id' => $input['id'] ?? null],
            [

                'encounter_id' => $input['encounter_id'] ?? null,
                'doctor_id' => $encounter->doctor_id,
                'patient_id' => $encounter->patient_id,
                'prescription' => $input['prescriptions'] ?? null,
                'medication' => $input['medication'] ?? null,
                'dosage' => $input['dosage'] ?? null,
                'dosage_unit' => $input['dosage_unit'] ?? null,
                'sig' => $input['sig'] ?? null,
                'route' => $input['route'] ?? null,
                'frequency' => $input['frequency'] ?? null,
                'instructions' => $input['instructions'] ?? null,
                'quantity' => $input['quantity'] ?? null,
                'refill' => $input['refill'] ?? null,
                'reason' => $input['reason'] ?? null,
                'date_inactive' => $input['date_inactive'] ?? null,
                'date_active' => $input['date_active'] ?? null,
                'pharmacy_id' => $input['pharmacy_id'] ?? null,
                'date_old' => $input['date_old'] ?? null,
                'days' => $days ?? null,
                'provider' => auth()->user()->doctor->name ?? null,
                'due_date' => $due_date ?? null,

            ]);
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
            Prescription::where('id', $id)->update([
                'date_active' => null,
                'date_inactive' => null,
                'date_active' => now(),
            ]);
        } else {
            Prescription::where('id', $id)->update([
                'date_active' => null,
                'date_inactive' => now(),
                'date_active' => null,
            ]);
        }
    }

    public function reconcileMedications($id)
    {
        // Build the updated medication plan
        $this->planBuild($id);

        // Fetch prescription record
        $rxRow = Prescription::find($id);
        if (! $rxRow) {
            return response()->json(['error' => 'Prescription not found'], 404);
        }

        $medication = $rxRow->medication;

        // Find the latest active medication entry marked old
        $row1 = Prescription::where('patient_id', $rxRow->patient_id)
            ->where('medication', $medication)
            ->where('date_inactive', null)
            ->where('date_old', '!=', null)
            ->orderByDesc('date_old')
            ->first();

        if ($row1) {
            Prescription::where('patient_id', $rxRow->patient_id)
                ->where('id', $row1->id)
                ->update([
                    'date_old' => null,
                    'rcopia_sync' => 'nd1',
                ]);

        }
    }

    public function planBuild($id)
    {
        $rxRow = Prescription::with(['encounter', 'doctor', 'patient'])->find($id);

        if (! $rxRow) {
            return response()->json(['error' => 'Prescription not found'], 404);
        }

        // Build medication instructions
        $instructions = $rxRow->sig
            ? ($rxRow->sig.', '.$rxRow->route.', '.$rxRow->frequency)
            : $rxRow->instructions;

        // Build text summary
        $text = '';
        if ($rxRow->encounter_id) {
            $text = "{$rxRow->medication} {$rxRow->dosage} {$rxRow->dosage_unit}, {$instructions} for {$rxRow->reason}";
        }

        // ---------- MEDICATIONS ----------
        $rxRxArr = [];
        $rxOrdersSummaryText = '';
        $rxPrescribeTextArr = [];
        $rxEieTextArr = [];
        $rxInactivateTextArr = [];
        $rxReactivateTextArr = [];

        $rx_col = 'rxReactivateTextArr';
        ${$rx_col}[] = $text;
        if ($rxRow->rx) {
            $rxRowParts = explode("\n\n", $rxRow->rx);

            foreach ($rxRowParts as $part) {
                if (str_contains($part, 'PRESCRIBED MEDICATIONS:')) {
                    $arr = array_filter(explode("\n", str_replace('PRESCRIBED MEDICATIONS:  ', '', $part)));
                    $rxPrescribeTextArr = array_merge($rxPrescribeTextArr, $arr);
                }
                if (str_contains($part, 'ENTERED MEDICATIONS IN ERROR:')) {
                    $arr = array_filter(explode("\n", str_replace('ENTERED MEDICATIONS IN ERROR:  ', '', $part)));
                    $rxEieTextArr = array_merge($rxEieTextArr, $arr);
                }
                if (str_contains($part, 'DISCONTINUED MEDICATIONS:')) {
                    $arr = array_filter(explode("\n", str_replace('DISCONTINUED MEDICATIONS:  ', '', $part)));
                    $rxInactivateTextArr = array_merge($rxInactivateTextArr, $arr);
                }
                if (str_contains($part, 'REINSTATED MEDICATIONS:')) {
                    $arr = array_filter(explode("\n", str_replace('REINSTATED MEDICATIONS:  ', '', $part)));
                    $rxReactivateTextArr = array_merge($rxReactivateTextArr, $arr);
                }
            }
        }

        // Build medication text blocks
        if (count($rxPrescribeTextArr) > 0) {
            array_unshift($rxPrescribeTextArr, 'PRESCRIBED MEDICATIONS:  ');
            $rxRxArr[] = implode("\n", $rxPrescribeTextArr);
        }
        if (count($rxEieTextArr) > 0) {
            array_unshift($rxEieTextArr, 'ENTERED MEDICATIONS IN ERROR:  ');
            $rxRxArr[] = implode("\n", $rxEieTextArr);
        }
        if (count($rxInactivateTextArr) > 0) {
            array_unshift($rxInactivateTextArr, 'DISCONTINUED MEDICATIONS:  ');
            $rxRxArr[] = implode("\n", $rxInactivateTextArr);
        }
        if (count($rxReactivateTextArr) > 0) {
            array_unshift($rxReactivateTextArr, 'REINSTATED MEDICATIONS:  ');
            $rxRxArr[] = implode("\n", $rxReactivateTextArr);
        }

        $rxRx = implode("\n\n", $rxRxArr);
        $rxOrdersSummaryText = $rxRx;

        // Prepare update data
        $rxData = [
            'encounter_id' => $rxRow->encounter_id,
            'patient_id' => $rxRow->patient_id,
            'provider' => $rxRow->doctor->user->name ?? '',
            'rx' => $rxRx,
            'orders_summary' => $rxOrdersSummaryText,
        ];
        // Update existing prescription
        Prescription::where('id', $id)->update($rxData);

        return true;
    }
}
