<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\DoctorPatient;
use App\Models\Patient;
use App\Models\SocialHistory;
use App\Models\User;
use App\Traits\EmailTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PatientService extends BaseService
{
    use EmailTrait;

    /**
     * The module name for audit logging.
     */
    protected string $auditModule = 'Patient';

    /**
     * list
     *
     * @param  mixed  $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function list(Request $request)
    {
        $user = auth()->user();
        $doctor = $user->doctor;
        $hospital = $user->hospital; // Admin → hospital

        $query = Patient::query()->with([
            'user',
            'doctorPatients.doctor.hospital',
        ]);

        if ($user->hasRole('Admin') && $hospital) {

            $query->whereHas('doctorPatients.doctor', function ($q) use ($hospital) {
                $q->where('hospital_id', $hospital->id);
            });
        }

        if ($user->hasRole('Doctor') && $doctor) {

            if ($doctor->hospital_id) {
                $query->whereHas('doctorPatients.doctor', function ($q) use ($doctor) {
                    $q->where('hospital_id', $doctor->hospital_id);
                });
            } else {
                $query->whereHas('doctorPatients', function ($q) use ($doctor) {
                    $q->where('doctor_id', $doctor->id);
                });
            }
        }

        /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */
        $query->when($request->filled('search'), function ($q) use ($request) {
            $q->whereHas('user', function ($u) use ($request) {
                $u->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('mobile', 'like', "%{$request->search}%");
            });
        });

        /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
        $patients = $query
            ->latest()
            ->paginate(request('per_page', paginateLimit()))
            ->withQueryString();

        /*
    |--------------------------------------------------------------------------
    | Attach hospital (deterministic)
    |--------------------------------------------------------------------------
    */
        $patients->getCollection()->transform(function ($patient) {

            $dp = $patient->doctorPatients
                ->sortByDesc('created_at')
                ->first();

            if ($patient->user && $dp && $dp->doctor) {
                $patient->user->hospital = $dp->doctor->hospital;
            }

            return $patient;
        });

        return $patients;
    }

    /**
     * Demographic data
     *
     * @return void
     */
    public function demographics()
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();
        $selectedPatientId = $doctor ? $doctor->selected_patient_id : '';

        $patient = Patient::with(['guardian.address', 'address', 'user'])->where('id', $selectedPatientId)->orWhere('user_id', $user->id)->first();

        return $patient;
    }

    /**
     * Update Demographic data
     */
    public function updateDemographics(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'mobile' => 'nullable|string|max:20',
            'address_1' => 'nullable|string|max:255',
            'address_2' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
            'country' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:20',
            'sex' => 'nullable|string|max:20',
            'marital_status' => 'nullable|string|max:50',
            'profile_photo' => 'nullable|image|max:2048',
        ]);

        try {
            DB::transaction(function () use ($validated, $request) {

                // 🔹 Find patient
                $patient = Patient::with('user')->findOrFail($request->id);

                $fullName = trim($validated['first_name'].' '.$validated['last_name']);

                // 🔹 Update patient
                $patient->update([
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'name' => $fullName,
                    'email' => $validated['email'],
                    'mobile' => $validated['mobile'] ?? null,
                    'sex' => $validated['sex'] ?? null,
                    'dob' => $validated['dob'] ?? null,
                    'marital_status' => $validated['marital_status'] ?? null,
                    'address_1' => $validated['address_1'] ?? null,
                    'address_2' => $validated['address_2'] ?? null,
                    'country' => $validated['country'] ?? null,
                    'city' => $validated['city'] ?? null,
                    'state' => $validated['state'] ?? null,
                    'zip' => $validated['zip'] ?? null,
                ]);

                // 🔹 Update linked user (if exists)
                if ($patient->user) {
                    $patient->user->update([
                        'first_name' => $validated['first_name'],
                        'last_name' => $validated['last_name'],
                        'name' => $fullName,
                        'email' => $validated['email'],
                        'mobile' => $validated['mobile'] ?? null,
                        'sex' => $validated['sex'] ?? null,
                        'dob' => $validated['dob'] ?? null,
                        'marital_status' => $validated['marital_status'] ?? null,
                    ]);
                }

                // 🔹 Handle profile photo
                if ($request->hasFile('profile_photo')) {
                    $path = $request->file('profile_photo')
                        ->store('profile_photos', 'public');

                    $patient->update(['photo' => $path]);

                    if ($patient->user) {
                        $patient->user->update([
                            'profile_photo_path' => $path,
                        ]);
                    }
                }
            });

            return back()->with('success', 'Demographics updated successfully');
        } catch (\Throwable $e) {

            Log::error('Demographics update failed', [
                'error' => $e->getMessage(),
                'patient_id' => $request->id,
            ]);

            return back()->with('error', 'Failed to update demographics');
        }
    }

    /**
     * Save Patient data
     *
     * @param  array  $data
     * @param  User  $user
     * @return void
     */
    public function savePatient($data)
    {
        // Check if this is an update or create
        $isUpdate = ! empty($data['id']);
        $oldPatient = $isUpdate ? Patient::find($data['id']) : null;

        $patient = Patient::updateOrCreate(
            [
                'user_id' => $data['user_id'] ?? null,
            ],
            [
                'user_id' => $data['user_id'] ?? null,
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'name' => $data['first_name'].' '.$data['last_name'],
                'email' => $data['email'] ?? null,
                'mobile' => $data['mobile'] ?? null,
                'dob' => $data['dob'] ?? null,
                'address_1' => $data['street_address1'] ?? null,
                'address_2' => $data['street_address2'] ?? null,
                'city' => $data['city'],
                'state' => $data['state'],
                'country' => $data['country'],
                'zip' => $data['zip'],
                'sex' => $data['sex'] ?? null,
                'created_by' => Auth::user()->name ?? '',
                'is_active' => $data['is_active'],
            ]
        );

        // Audit logging
        if ($isUpdate && $oldPatient) {
            $this->logUpdate($oldPatient, $patient, 'Patient updated via UserService');
        } else {
            $this->logCreate($patient, 'New patient created');
        }

        $doctorId = Auth::user()->doctor?->id;
        if ($patient && ! $data['id'] && $doctorId) {
            DoctorPatient::updateOrCreate([
                'patient_id' => $patient->id,
                'doctor_id' => $doctorId,
            ]);
        } elseif ($patient && $data['id'] && $data['doctor_id']) {
            DoctorPatient::updateOrCreate([
                'patient_id' => $patient->id,
                'doctor_id' => $data['doctor_id'],
            ]);
        }

        return $patient;
    }

    /**
     * Register patient to portal
     *
     * @param  Patient  $patient
     * @return void
     */
    public function registerPatientPortal($data)
    {
        $patient = Patient::find($data['patient_id']);
        if (! $patient) {
            return;
        }

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $token = '';
        for ($i = 0; $i < 6; $i++) {
            $token .= $characters[mt_rand(0, strlen($characters) - 1)];
        }
        $patient->update([
            'registration_code' => $token,
        ]);

        $data1 = null;
        $doctorPatient = $patient->doctorPatients->first();
        if ($patient->email && $doctorPatient && $doctorPatient->doctor && $doctorPatient->doctor->hospital) {
            $hospital = $doctorPatient->doctor->hospital;
            $data1 = [
                'practicename' => $hospital->name,
                'url' => route('signup.patient', ['token' => $token]),
                'token' => $token,
            ];
        }
        // Create user account for patient
        if ($data1) {
            $this->sendViewEmail('emails.loginregistrationcode', $data1, $patient->email, 'Patient Portal Registration Code');
        }
    }

    public function storeSocialHistory($data)
    {
        // Handle mental health notes specially - combine the fields
        if (isset($data['psychological_history']) || isset($data['devolepmental_history']) || isset($data['past_medication_trials'])) {
            $mentalHealthParts = [
                $data['psychological_history'] ?? '',
                $data['devolepmental_history'] ?? '',
                $data['past_medication_trials'] ?? '',
            ];
            $data['mental_health_notes'] = implode(' | ', array_filter($mentalHealthParts));

            // Remove the individual fields
            unset($data['psychological_history'], $data['devolepmental_history'], $data['past_medication_trials']);
        }

        $socialHistory = SocialHistory::updateOrCreate(
            ['patient_id' => $data['patient_id']],
            $data
        );

        return $socialHistory;
    }
}
