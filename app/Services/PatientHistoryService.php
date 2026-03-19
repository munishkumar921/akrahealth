<?php

namespace App\Services;

use App\Models\Allergy;
use App\Models\Encounter;
use App\Models\Immunization;
use App\Models\Issue;
use App\Models\Medication;
use App\Models\Message;
use App\Models\Order;

class PatientHistoryService
{
    public function getTimeline($selectedPatientId)
    {
        $timeline = [];

        // Get encounters with related data
        $encounters = Encounter::with(['prescriptions', 'supplements', 'doctor.user', 'assessment'])
            ->where('patient_id', $selectedPatientId)
            ->orderBy('encounter_date', 'desc')
            ->get();

        foreach ($encounters as $encounter) {

            $date = $encounter->encounter_date ?? $encounter->created_at;

            $assessmentDescription = null;

            if ($encounter->assessment) {

                $assessmentInfo = $encounter->assessment;
                $assessmentArr = $this->array_assessment();

                $lines = [];

                // Dynamic assessment_1 to assessment_12
                for ($i = 1; $i <= 12; $i++) {
                    $col = "assessment_{$i}";
                    if (! empty($assessmentInfo->{$col})) {
                        $lines[] = $assessmentInfo->{$col};
                    }
                }

                // Structured fields
                foreach ($assessmentArr as $key => $labels) {

                    $value = $assessmentInfo->{$key} ?? null;

                    if (! empty($value)) {

                        $label = ($encounter->encounter_template === 'standardmtm')
                            ? $labels['standardmtm']
                            : $labels['standard'];

                        $lines[] = $label.': '.$value;
                    }
                }

                if (! empty($lines)) {
                    $assessmentDescription = "Assessment:\n\n".implode("\n\n", $lines);
                }
            }

            $timeline[] = [
                'date' => $date,
                'title' => 'Encounter: '.($encounter->chief_complaint ?: 'Visit'),
                'description' => $assessmentDescription ?? 'Encounter without assessment',
                'icon' => 'fa-solid fa-stethoscope',
                'iconColor' => 'bg-green-500 text-white',
                'type' => 'encounter',
                'id' => $encounter->id,
                'url' => auth()->user()->doctor
                    ? route('doctor.encounters.show', $encounter->id)
                    : route('patient.encounters.show', $encounter->id),
            ];

            // Prescriptions
            foreach ($encounter->prescriptions as $prescription) {
                if ($prescription->medication) {
                    $encounterDate = $encounter->encounter_date;
                    $timeline[] = [
                        'date' => $encounterDate ? $encounterDate : $encounter->created_at,
                        'title' => 'Prescribed Medication',
                        'description' => "{$prescription->medication} {$prescription->dosage}{$prescription->dosage_unit}, {$prescription->route}, {$prescription->frequency} for ".($prescription->reason ?: 'treatment'),
                        'icon' => 'fa-solid fa-eyedropper',
                        'iconColor' => 'bg-yellow-500 text-white',
                        'type' => 'prescription',
                        'id' => $prescription->id,
                        'url' => auth()->user()->doctor ? route('doctor.encounters.show', $encounter->id) : route('patient.encounters.show', $encounter->id),
                    ];
                }
            }

            // Supplements
            foreach ($encounter->supplements as $supplement) {
                if ($supplement->supplement) {
                    $encounterDate = $encounter->encounter_date;
                    $timeline[] = [
                        'date' => $encounterDate ? $encounterDate : $encounter->created_at->format('Y-m-d'),
                        'title' => 'Prescribed Supplement',
                        'description' => "{$supplement->supplement} {$supplement->dosage}{$supplement->dosage_unit}, {$supplement->route}, {$supplement->frequency}",
                        'icon' => 'fa-solid fa-capsules',
                        'iconColor' => 'bg-primary text-white',
                        'type' => 'supplement',
                        'id' => $supplement->id,
                        'url' => auth()->user()->doctor ? route('doctor.supplements.index') : route('patient.supplements'),
                    ];
                }
            }
        }

        // Medications
        $medications = Medication::where('patient_id', $selectedPatientId)
            ->orderBy('date_active', 'desc')
            ->get();

        foreach ($medications as $medication) {

            if ($medication->date_inactive) {
                $timeline[] = [
                    'date' => $medication->date_inactive,
                    'title' => 'Medication Stopped',
                    'description' => "{$medication->medication} {$medication->dosage}{$medication->dosage_unit}, {$medication->route}, {$medication->frequency}",
                    'icon' => 'fa-solid fa-flask',
                    'iconColor' => 'bg-red-500 text-white',
                    'type' => 'medication_stopped',
                    'id' => $medication->id,
                ];
            } elseif ($medication->date_active) {
                $timeline[] = [
                    'date' => $medication->date_active,
                    'title' => 'Medication Started',
                    'description' => "{$medication->medication} {$medication->dosage}{$medication->dosage_unit}, {$medication->route}, {$medication->frequency}",
                    'icon' => 'fa-solid fa-pills',
                    'iconColor' => 'bg-green-500 text-white',
                    'type' => 'medication_started',
                    'id' => $medication->id,
                    'url' => auth()->user()->doctor ? route('doctor.medications.index') : route('patient.medications'),

                ];
            }
        }

        // Issues
        $issues = Issue::where('patient_id', $selectedPatientId)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($issues as $issue) {
            $title = match ($issue->type) {
                'Problem' => 'New Problem',
                'MedicalHistory' => 'New Medical Event',
                'SurgicalHistory' => 'New Surgical Event',
                default => 'Issue',
            };

            // Determine icon color based on issue type
            $issueColor = match ($issue->type) {
                'Problem' => 'bg-orange-500 text-white',
                'MedicalHistory' => 'bg-red-500 text-white',
                'SurgicalHistory' => 'bg-indigo-500 text-white',
                default => 'bg-red-500 text-white',
            };

            // Skip if date_active is empty or invalid
            $dateActive = $issue->date_active;
            if (! $dateActive) {
                continue;
            }

            $timeline[] = [
                'date' => $dateActive,
                'title' => $title,
                'description' => $issue->issue,
                'icon' => 'fa-solid fa-notes-medical',
                'iconColor' => $issueColor,
                'type' => 'issue',
                'id' => $issue->id,
                'url' => auth()->user()->doctor ? route('doctor.conditions.index') : route('patient.conditions'),
            ];
        }

        // Immunizations
        $immunizations = Immunization::where('patient_id', $selectedPatientId)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($immunizations as $immunization) {
            // Skip if date is empty or invalid

            $timeline[] = [
                'date' => $immunization->date,
                'title' => 'Immunization Given',
                'description' => "{$immunization->immunization} ({$immunization->route})",
                'icon' => 'fa-solid fa-syringe',
                'iconColor' => 'bg-yellow-500 text-white',
                'type' => 'immunization',
                'id' => $immunization->id,
                'url' => auth()->user()->doctor ? route('doctor.immunizations.index') : route('patient.immunizations'),
            ];
        }

        // Allergies
        $allergies = Allergy::where('patient_id', $selectedPatientId)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($allergies as $allergy) {

            $timeline[] = [
                'date' => $allergy->date_active ?: $allergy->date_inactive,
                'title' => 'Allergy Update',
                'description' => "{$allergy->note} {$allergy->allergies_medicine}, {$allergy->allergies_reaction}, {$allergy->allergies_severity}",
                'icon' => 'fa-solid fa-allergies',
                'iconColor' => 'bg-yellow-500 text-white',
                'type' => 'allergy',
                'id' => $allergy->id,
                'url' => auth()->user()->doctor ? route('doctor.allergies.index') : route('patient.allergies'),
            ];
        }

        // Lab orders
        $labOrders = Order::where('patient_id', $selectedPatientId)
            ->whereNotNull('labs')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($labOrders as $order) {
            $timeline[] = [
                'date' => $order->created_at,
                'title' => 'Lab Order',
                'description' => $order->labs ?: 'Laboratory test ordered',
                'icon' => 'fa-solid fa-flask',
                'iconColor' => 'bg-red-500 text-white',
                'type' => 'lab_order',
                'id' => $order->id,
                'url' => auth()->user()->doctor ? route('doctor.orders.index') : route('patient.orders'),
            ];
        }

        // Radiology orders
        $radiologyOrders = Order::where('patient_id', $selectedPatientId)
            ->whereNotNull('radiology')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($radiologyOrders as $order) {
            $timeline[] = [
                'date' => $order->created_at,
                'title' => 'Radiology Order',
                'description' => $order->radiology ?: 'Imaging study ordered',
                'icon' => 'fa-solid fa-x-ray',
                'iconColor' => 'bg-indigo-500 text-white',
                'type' => 'radiology_order',
                'id' => $order->id,
                'url' => auth()->user()->doctor ? route('doctor.orders.index') : route('patient.orders'),
            ];
        }
        // Messages
        $messages = Message::where('patient_id', $selectedPatientId)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($messages as $message) {
            $timeline[] = [
                'date' => $message->created_at->format('Y-m-d'),
                'title' => 'Message',
                'description' => substr($message->message, 0, 500),
                'icon' => 'fa-solid fa-message',
                'iconColor' => 'bg-blue-500 text-white',
                'id' => $message->id,
                'url' => auth()->user()->doctor ? route('doctor.messages.index') : route('patient.messages'),
            ];
        }

        // Sort newest first - filter out invalid dates first
        $timeline = array_filter($timeline, function ($item) {
            return ! empty($item['date']) && is_string($item['date']) && strlen($item['date']) >= 4;
        });

        usort($timeline, fn ($a, $b) => strtotime($b['date']) <=> strtotime($a['date']));

        return $timeline;
    }

    protected function array_assessment()
    {
        return [
            'assessment_other' => [
                'standardmtm' => 'SOAP Note',
                'standard' => 'Additional Diagnoses',
            ],
            'assessment_ddx' => [
                'standardmtm' => 'MAP2',
                'standard' => 'Differential Diagnoses Considered',
            ],
            'assessment_notes' => [
                'standardmtm' => 'Pharmacist Note',
                'standard' => 'Assessment Discussion',
            ],
        ];
    }
}
