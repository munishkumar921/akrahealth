<?php

namespace App\Http\Controllers;

use App\Models\Patient;

class NavigationController extends Controller
{
    public function getNavigationCounts()
    {
        $counts = [];
        $user = auth()->user();

        /* Patient counts */
        if ($user->hasRole('Patient')) {

            $patientId = $user?->patient?->id;
            if ($patientId) {
                $counts = [
                    'Medications' => \App\Models\Prescription::where('patient_id', $patientId)->count(),
                    'Conditions' => \App\Models\Issue::where('patient_id', $patientId)->count(),
                    'Supplements' => \App\Models\PatientSupplement::where('patient_id', $patientId)->count(),
                    'Allergies' => \App\Models\Allergy::where('patient_id', $patientId)->count(),
                    'Alerts' => \App\Models\Alert::where('patient_id', $patientId)->count(),
                    'Orders' => \App\Models\Order::where('patient_id', $patientId)->count(),
                    'Encounters' => \App\Models\Encounter::where('patient_id', $patientId)->count(),
                    'Immunizations' => \App\Models\Immunization::where('patient_id', $patientId)->count(),
                ];
            }

            return response()->json($counts);
        }

        /* Doctor counts */
        $patientId = $user?->doctor?->selected_patient_id;
        if ($patientId) {
            $selectedPatient = Patient::with('user')->find($patientId);

            if ($selectedPatient) {
                $counts = [
                    'Medications' => \App\Models\Prescription::where('patient_id', $patientId)->count(),
                    'Conditions' => \App\Models\Issue::where('patient_id', $patientId)->count(),
                    'Supplements' => \App\Models\PatientSupplement::where('patient_id', $patientId)->count(),
                    'Allergies' => \App\Models\Allergy::where('patient_id', $patientId)->count(),
                    'Alerts' => \App\Models\Alert::where('patient_id', $patientId)->count(),
                    'Orders' => \App\Models\Order::where('patient_id', $patientId)->count(),
                    'Encounters' => \App\Models\Encounter::where('patient_id', $patientId)->count(),
                    'Immunizations' => \App\Models\Immunization::where('patient_id', $patientId)->count(),
                ];
            }
        }

        return response()->json($counts);
    }
}
