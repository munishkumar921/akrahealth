<?php

namespace App\Services;

use App\Models\OtherHistory;
use SoapBox\Formatter\Formatter;
use Symfony\Component\Yaml\Yaml;

class OtherHistoryService
{
    public function store($input)
    {
        if (($input['type'] ?? '') !== 'familyHistory') {
            return;
        }

        $patientId = auth()->user()->doctor->selected_patient_id ?? null;
        if (! $patientId) {
            throw new \Exception('Patient ID not found.');
        }

        $data = [
            'name' => $input['name'] ?? '',
            'relationship' => $input['relationship'] ?? '',
            'living_status' => $input['living_status'] ?? '',
            'gender' => $input['gender'] ?? '',
            'dob' => $input['dob'] ?? '',
            'marital_status' => $input['marital_status'] ?? '',
            'mother' => $input['mother'] ?? '',
            'father' => $input['father'] ?? '',
            'medical_history' => isset($input['medical_history'])
                ? implode("\n", (array) $input['medical_history'])
                : '',
        ];

        $familyHistory = OtherHistory::where('patient_id', $patientId)->first();
        $fh_arr = [];

        /* 🧩 Parse YAML safely */
        if ($familyHistory && ! empty($familyHistory->oh_fh)) {
            try {
                $formatter = Formatter::make($familyHistory->oh_fh, Formatter::YAML);
                $fh_arr = $formatter->toArray();
            } catch (\Exception $e) {
                \Log::error('YAML parsing error in OtherHistoryService', [
                    'error' => $e->getMessage(),
                ]);
                $fh_arr = [];
            }
        }

        /* 🆙 UPDATE OR APPEND */
        $updated = false;
        $inputId = $input['id'] ?? null;

        if ($inputId !== null) {
            foreach ($fh_arr as $index => $item) {
                if (isset($item['id']) && (string) $item['id'] === (string) $inputId) {
                    // ✅ Update existing record
                    $fh_arr[$index] = array_merge($item, $data, ['id' => $item['id']]);
                    $updated = true;
                    break;
                }
            }
        }

        // ➕ Append new record if not updated
        if (! $updated) {
            $data['id'] = collect($fh_arr)->max('id') + 1 ?: 1;
            $fh_arr[] = $data;
        }

        /* 🔄 Save back to YAML */
        $formatter1 = Formatter::make($fh_arr, Formatter::ARR);
        $yamlData = $formatter1->toYaml();

        OtherHistory::updateOrCreate(
            ['patient_id' => $patientId],
            ['oh_fh' => $yamlData]
        );
    }
}
