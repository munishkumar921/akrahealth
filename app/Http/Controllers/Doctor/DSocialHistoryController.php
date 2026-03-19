<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\SocialHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DSocialHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $doctor = Doctor::where('user_id', Auth::id())->first();
        $patientId = $doctor->selected_patient_id ?? null;

        $socialHistory = null;
        if ($patientId) {
            $socialHistory = SocialHistory::where('patient_id', $patientId)->first();
        }

        return Inertia::render('Doctors/Patient/SocialHistory/SocialHistory', [
            'socialHistory' => $socialHistory,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'social_history' => 'nullable|string',
            'sexually_active' => 'nullable|boolean',
            'diet' => 'nullable|string',
            'physical_activity' => 'nullable|string',
            'employment' => 'nullable|string',
            'alcohol_use' => 'nullable|string',
            'tobacco_use' => 'nullable|boolean',
            'tobacco_use_details' => 'nullable|string',
            'illicit_drug_use' => 'nullable|string',
            'psychological_history' => 'nullable|string',
            'developmental_history' => 'nullable|string',
            'devolepmental_history' => 'nullable|string',
            'past_medication_trials' => 'nullable|string',
        ]);

        $doctor = Doctor::where('user_id', auth()->id())->first();

        if (! $doctor) {
            return back()->with('error', 'Doctor not found.');
        }

        if (! $doctor->selected_patient_id) {
            return back()->with('error', 'No patient selected.');
        }

        try {
            DB::transaction(function () use ($validated, $doctor) {

                $clean = fn ($value) => is_string($value) && trim($value) === '' ? null : $value;

                $socialHistory = SocialHistory::firstOrNew([
                    'patient_id' => $doctor->selected_patient_id,
                ]);

                $socialHistory->doctor_id = $doctor->id;

                // Update only submitted fields so one modal update does not clear other sections
                if (array_key_exists('social_history', $validated)) {
                    $socialHistory->social_history = $clean($validated['social_history']);
                }
                if (array_key_exists('sexually_active', $validated)) {
                    $socialHistory->sexually_active = $validated['sexually_active'];
                }
                if (array_key_exists('diet', $validated)) {
                    $socialHistory->diet = $clean($validated['diet']);
                }
                if (array_key_exists('physical_activity', $validated)) {
                    $socialHistory->physical_activity = $clean($validated['physical_activity']);
                }
                if (array_key_exists('employment', $validated)) {
                    $socialHistory->employment = $clean($validated['employment']);
                }
                if (array_key_exists('alcohol_use', $validated)) {
                    $socialHistory->alcohol_use = $clean($validated['alcohol_use']);
                }
                if (array_key_exists('tobacco_use', $validated)) {
                    $socialHistory->tobacco_use = $validated['tobacco_use'];
                }
                if (array_key_exists('tobacco_use_details', $validated)) {
                    $socialHistory->tobacco_use_details = $clean($validated['tobacco_use_details']);
                }
                if (array_key_exists('illicit_drug_use', $validated)) {
                    $socialHistory->drug_use = $clean($validated['illicit_drug_use']);
                }

                // Preserve and update mental health parts by position
                $existingMental = explode(' | ', (string) ($socialHistory->mental_health_notes ?? ''));
                $psychologicalHistory = $existingMental[0] ?? '';
                $developmentalHistory = $existingMental[1] ?? '';
                $pastMedicationTrials = $existingMental[2] ?? '';

                if (array_key_exists('psychological_history', $validated)) {
                    $psychologicalHistory = (string) ($clean($validated['psychological_history']) ?? '');
                }

                if (array_key_exists('developmental_history', $validated) || array_key_exists('devolepmental_history', $validated)) {
                    $developmentalHistory = (string) ($clean($validated['developmental_history'] ?? $validated['devolepmental_history'] ?? null) ?? '');
                }

                if (array_key_exists('past_medication_trials', $validated)) {
                    $pastMedicationTrials = (string) ($clean($validated['past_medication_trials']) ?? '');
                }

                if (
                    array_key_exists('psychological_history', $validated)
                    || array_key_exists('developmental_history', $validated)
                    || array_key_exists('devolepmental_history', $validated)
                    || array_key_exists('past_medication_trials', $validated)
                ) {
                    $socialHistory->mental_health_notes = implode(' | ', [
                        $psychologicalHistory,
                        $developmentalHistory,
                        $pastMedicationTrials,
                    ]);
                }

                $socialHistory->save();
            });

            return back()->with('success', 'Social history saved successfully.');

        } catch (\Throwable $e) {

            return back()->with('error', 'Failed to save social history.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
