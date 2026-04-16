<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\DoctorPatient;
use App\Models\Patient;
use App\Models\SocialHistory;
use App\Models\User;
use App\Traits\EmailTrait;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

    public function listAdminPatients(Request $request): LengthAwarePaginator
    {
        $user = auth()->user();
        $hospitalId = $user->hospital?->id ?? $user->doctor?->hospital_id;
        $filters = $this->normalizeAdminPatientFilters($request);

        $query = Patient::query()->with([
            'user',
            'doctorPatients.doctor.user:id,name',
            'doctorPatients.doctor.hospital:id,name,main_branch_id',
        ]);

        if ($hospitalId) {
            $query->whereHas('doctorPatients.doctor', function ($doctorQuery) use ($hospitalId) {
                $doctorQuery->where('hospital_id', $hospitalId);
            });
        }

        $query->when($filters['keyword'], function ($patientQuery) use ($filters) {
            $keyword = $filters['keyword'];

            $patientQuery->where(function ($inner) use ($keyword) {
                $inner->where('name', 'like', "%{$keyword}%")
                    ->orWhere('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('mobile', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($userQuery) use ($keyword) {
                        $userQuery->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%")
                            ->orWhere('mobile', 'like', "%{$keyword}%");
                    });
            });
        });

        $query->when($filters['status'] !== null, function ($patientQuery) use ($filters) {
            $patientQuery->where('is_active', $filters['status']);
        });

        $query->when($filters['doctor_id'], function ($patientQuery) use ($filters) {
            $patientQuery->whereHas('doctorPatients', function ($doctorPatientQuery) use ($filters) {
                $doctorPatientQuery->where('doctor_id', $filters['doctor_id']);
            });
        });

        $patients = $query
            ->latest()
            ->paginate($request->input('per_page', paginateLimit()))
            ->withQueryString();

        $patients->through(function (Patient $patient) {
            $doctorPatient = $patient->doctorPatients
                ->sortByDesc('created_at')
                ->first();

            $doctor = $doctorPatient?->doctor;
            $branch = $doctor?->hospital;
            $avatarUrl = $patient->photo
                ? (str_starts_with($patient->photo, 'http') ? $patient->photo : Storage::url($patient->photo))
                : ($patient->user?->profile_photo_url ?: asset('images/avatar.webp'));

            return array_merge($patient->toArray(), [
                'display_name' => $patient->name,
                'doctor_name' => $doctor?->name ?? $doctor?->user?->name,
                'doctor_id' => $doctor?->id,
                'branch_name' => $branch?->name,
                'branch_type' => $branch ? ($branch->main_branch_id ? 'Sub Branch' : 'Main Branch') : null,
                'avatar_url' => $avatarUrl,
                'created_label' => dateFormat($patient->created_at),
                'status_label' => $patient->is_active ? 'Active' : 'Inactive',
            ]);
        });

        return $patients;
    }

    public function listDoctorPatients(Request $request): LengthAwarePaginator
    {
        $doctor = auth()->user()?->doctor;
        $filters = $this->normalizeDoctorPatientFilters($request);

        $query = DoctorPatient::query()
            ->where('doctor_id', $doctor?->id)
            ->whereHas('patient')
            ->with(['patient.user']);

        $query->when($filters['keyword'], function ($doctorPatientQuery) use ($filters) {
            $keyword = $filters['keyword'];

            $doctorPatientQuery->whereHas('patient', function ($patientQuery) use ($keyword) {
                $patientQuery->where(function ($inner) use ($keyword) {
                    $inner->where('name', 'like', "%{$keyword}%")
                        ->orWhere('first_name', 'like', "%{$keyword}%")
                        ->orWhere('last_name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%")
                        ->orWhere('mobile', 'like', "%{$keyword}%")
                        ->orWhereHas('user', function ($userQuery) use ($keyword) {
                            $userQuery->where('name', 'like', "%{$keyword}%")
                                ->orWhere('email', 'like', "%{$keyword}%")
                                ->orWhere('mobile', 'like', "%{$keyword}%");
                        });
                });
            });
        });

        $query->when($filters['status'] !== null, function ($doctorPatientQuery) use ($filters) {
            $doctorPatientQuery->whereHas('patient', function ($patientQuery) use ($filters) {
                $patientQuery->where('is_active', $filters['status']);
            });
        });

        $query->when($filters['portal_status'], function ($doctorPatientQuery) use ($filters) {
            $doctorPatientQuery->whereHas('patient', function ($patientQuery) use ($filters) {
                if ($filters['portal_status'] === 'registered') {
                    $patientQuery->whereNotNull('user_id');
                }

                if ($filters['portal_status'] === 'not_registered') {
                    $patientQuery->whereNull('user_id');
                }
            });
        });

        $patients = $query
            ->latest('id')
            ->paginate($request->input('per_page', paginateLimit()))
            ->withQueryString();

        $selectedPatientId = $doctor?->selected_patient_id;

        $patients->through(function (DoctorPatient $doctorPatient) use ($selectedPatientId) {
            $patient = $doctorPatient->patient;
            $user = $patient?->user;

            $avatarPath = $patient?->photo ?: $user?->profile_photo_path;
            $avatarUrl = $avatarPath
                ? (str_starts_with($avatarPath, 'http') ? $avatarPath : Storage::url($avatarPath))
                : ($user?->profile_photo_url ?: asset('images/avatar.webp'));

            return [
                'id' => $patient?->id,
                'display_name' => $patient?->name ?: trim(($patient?->first_name ?? '').' '.($patient?->last_name ?? '')),
                'email' => $patient?->email ?: $user?->email,
                'phone' => $patient?->mobile ?: $user?->mobile,
                'avatar_url' => $avatarUrl,
                'register_to_portal' => ! $user,
                'portal_status_label' => $user ? 'Registered' : 'Not Registered',
                'is_active' => (bool) $patient?->is_active,
                'status_label' => $patient?->is_active ? 'Active' : 'Inactive',
                'created_at' => optional($patient?->created_at)->format('M d, Y'),
                'created_label' => optional($patient?->created_at)->format('M d, Y'),
                'is_selected' => $selectedPatientId === $patient?->id,
            ];
        });

        return $patients;
    }

    protected function normalizeAdminPatientFilters(Request $request): array
    {
        $keyword = trim((string) ($request->input('keyword', $request->input('search', ''))));
        $doctorId = trim((string) $request->input('doctor_id', ''));
        $status = $request->input('status', '');

        return [
            'keyword' => $keyword !== '' ? $keyword : null,
            'doctor_id' => $doctorId !== '' ? $doctorId : null,
            'status' => $status === '' ? null : filter_var($status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
        ];
    }

    protected function normalizeDoctorPatientFilters(Request $request): array
    {
        $keyword = trim((string) ($request->input('keyword', $request->input('search', ''))));
        $status = $request->input('status', '');
        $portalStatus = trim((string) $request->input('portal_status', ''));

        return [
            'keyword' => $keyword !== '' ? $keyword : null,
            'status' => $status === '' ? null : filter_var($status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'portal_status' => in_array($portalStatus, ['registered', 'not_registered'], true) ? $portalStatus : null,
        ];
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
            $patient = DB::transaction(function () use ($validated, $request) {

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

                return $patient->fresh(['user', 'doctorPatients.doctor.user']);
            });

            $notificationService = app(InAppNotificationService::class);
            $doctorUsers = $patient?->doctorPatients
                ->map(fn ($doctorPatient) => $doctorPatient->doctor?->user)
                ->filter();

            $notificationService->notifyUsers(
                $doctorUsers ?? [],
                $notificationService->buildPayload(
                    'Patient profile updated',
                    ($patient?->name ?? 'A patient').' updated demographic information.',
                    'patient_updated',
                    [
                        'recipient_role' => 'Doctor',
                        'patient_id' => $patient?->id,
                        'action_url' => route('doctor.demographics'),
                        'related_model_type' => Patient::class,
                        'related_model_id' => $patient?->id,
                    ]
                )
            );

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

            $hospitalId = Auth::user()->hospital?->id
                ?? Auth::user()->doctor?->hospital_id
                ?? ($data['hospital_id'] ?? null);

            app(InAppNotificationService::class)->notifyAdminsForHospital(
                $hospitalId,
                app(InAppNotificationService::class)->buildPayload(
                    'New patient added',
                    ($patient->name ?? 'A patient').' was added to the system.',
                    'patient_created',
                    [
                        'recipient_role' => 'Admin',
                        'patient_id' => $patient->id,
                        'related_model_type' => Patient::class,
                        'related_model_id' => $patient->id,
                        'action_url' => route('admin.patients.index'),
                    ]
                )
            );
        }

        $doctorId = Auth::user()->doctor?->id ?? null;
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

        $patient = Patient::with(['doctorPatients.doctor.user'])->find($data['patient_id']);
        $doctorUsers = $patient?->doctorPatients
            ->map(fn ($doctorPatient) => $doctorPatient->doctor?->user)
            ->filter();

        app(InAppNotificationService::class)->notifyUsers(
            $doctorUsers ?? [],
            app(InAppNotificationService::class)->buildPayload(
                'Patient history updated',
                ($patient?->name ?? 'A patient').' updated social history information.',
                'patient_updated',
                [
                    'recipient_role' => 'Doctor',
                    'patient_id' => $patient?->id,
                    'related_model_type' => SocialHistory::class,
                    'related_model_id' => $socialHistory->id,
                ]
            )
        );

        return $socialHistory;
    }
}
