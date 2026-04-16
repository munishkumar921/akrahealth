<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\User;
use App\Models\UserVerify;
use App\Traits\EmailTrait;
use App\Traits\UploadFileTrait;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class UserService extends BaseService
{
    use EmailTrait;

    /**
     * Use the UploadFileTrait for file uploads
     */
    use UploadFileTrait;

    /**
     * The module name for audit logging.
     */
    protected string $auditModule = 'User';

    /**
     * The role of the user being managed by this service.
     *
     * @var int
     */
    public $role;

    /**
     * list
     *
     * @param  mixed  $request
     * @return void
     */
    public function list($request)
    {
        $users = User::query()
            ->role($this->role)
            ->when($request->filled('search'), function ($query) use ($request) {
                $s = trim($request->search);

                $query->where(function ($q) use ($s) {
                    $q->where('name', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%")
                        ->orWhere('mobile', 'like', "%{$s}%");
                })->orderBy('name', 'DESC');
            })
            ->when(
                $request->filled('filterActive') || $request->filled('filterInactive'),
                function ($q) use ($request) {
                    $filterActive = $request->boolean('filterActive');
                    $filterInactive = $request->boolean('filterInactive');

                    // If both are checked or both are unchecked, show all
                    if ($filterActive && $filterInactive) {
                        return $q;
                    }

                    // If only Active is checked
                    if ($filterActive) {
                        return $q->where('is_active', 1);
                    }

                    // If only Inactive is checked
                    if ($filterInactive) {
                        return $q->where('is_active', 0);
                    }

                    return $q;
                }
            )
            ->when(
                $request->filled('date'),
                fn ($q) => $q->whereDate('created_at', $request->date)
            )

            ->when(! $request->filled('search'), fn ($q) => $q->orderBy('created_at', 'desc'))
            ->paginate(request('per_page', paginateLimit()))->withQueryString();
        $users->getCollection()->transform(function ($user) {
            $user->created = dateFormat($user->created_at);

            return $user;
        });

        return $users;
    }

    public function listAdminUsers(Request $request, Collection $hospitalIds): LengthAwarePaginator
    {
        $filters = $this->normalizeAdminUserFilters($request);

        $users = User::query()
            ->with(['roles', 'doctor.hospital', 'doctor.specialities:name', 'address', 'userSkills', 'hospital'])
            ->whereHas('roles', function ($query) use ($filters) {
                $query->whereIn('name', ['Doctor', 'Virtual Assistant', 'Biller']);

                if ($filters['role']) {
                    $query->where('name', $filters['role']);
                }
            })
            ->where(function ($query) use ($hospitalIds) {
                $query->whereHas('doctor', function ($doctorQuery) use ($hospitalIds) {
                    $doctorQuery->whereNotNull('user_id')->whereIn('hospital_id', $hospitalIds);
                })->orWhereIn('hospital_id', $hospitalIds);
            })
            ->when($filters['keyword'], function ($query) use ($filters) {
                $keyword = $filters['keyword'];

                $query->where(function ($inner) use ($keyword) {
                    $inner->where('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%")
                        ->orWhere('mobile', 'like', "%{$keyword}%")
                        ->orWhere('first_name', 'like', "%{$keyword}%")
                        ->orWhere('last_name', 'like', "%{$keyword}%")
                        ->orWhereHas('doctor', function ($doctorQuery) use ($keyword) {
                            $doctorQuery->where('name', 'like', "%{$keyword}%")
                                ->orWhere('first_name', 'like', "%{$keyword}%")
                                ->orWhere('last_name', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($filters['status'] !== null, function ($query) use ($filters) {
                $query->where('is_active', $filters['status']);
            })
            ->when($filters['branch_id'], function ($query) use ($filters) {
                $query->where(function ($inner) use ($filters) {
                    $inner->whereHas('doctor', function ($doctorQuery) use ($filters) {
                        $doctorQuery->where('hospital_id', $filters['branch_id']);
                    })->orWhere('hospital_id', $filters['branch_id']);
                });
            })
            ->when($filters['speciality'], function ($query) use ($filters) {
                $query->whereHas('doctor.specialities', function ($specialityQuery) use ($filters) {
                    $specialityQuery->where('name', $filters['speciality']);
                });
            })
            ->get()
            ->map(fn (User $user) => $this->transformAdminUserRecord($user));

        $doctors = Doctor::query()
            ->with(['hospital', 'specialities', 'user.address', 'user.roles'])
            ->whereNull('user_id')
            ->whereIn('hospital_id', $hospitalIds)
            ->when($filters['role'] && $filters['role'] !== 'Doctor', function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->when($filters['keyword'], function ($query) use ($filters) {
                $keyword = $filters['keyword'];

                $query->where(function ($inner) use ($keyword) {
                    $inner->where('name', 'like', "%{$keyword}%")
                        ->orWhere('first_name', 'like', "%{$keyword}%")
                        ->orWhere('last_name', 'like', "%{$keyword}%");
                });
            })
            ->when($filters['status'] !== null, function ($query) use ($filters) {
                $query->where('is_active', $filters['status']);
            })
            ->when($filters['branch_id'], function ($query) use ($filters) {
                $query->where('hospital_id', $filters['branch_id']);
            })
            ->when($filters['speciality'], function ($query) use ($filters) {
                $query->whereHas('specialities', function ($specialityQuery) use ($filters) {
                    $specialityQuery->where('name', $filters['speciality']);
                });
            })
            ->get()
            ->map(fn (Doctor $doctor) => $this->transformAdminDoctorRecord($doctor));

        $mergedItems = $users
            ->merge($doctors)
            ->sort(function (array $a, array $b) use ($filters) {
                $sortBy = $filters['sort'] ?? 'name';
                $direction = $filters['direction'] ?? 'asc';

                $valueA = $this->normalizeAdminUserSortValue(data_get($a, $sortBy));
                $valueB = $this->normalizeAdminUserSortValue(data_get($b, $sortBy));

                $comparison = $valueA <=> $valueB;

                return $direction === 'desc' ? -$comparison : $comparison;
            })
            ->values();

        $perPage = (int) $request->input('per_page', paginateLimit());
        $page = LengthAwarePaginator::resolveCurrentPage();
        $offset = ($page - 1) * $perPage;
        $paginatedItems = $mergedItems->slice($offset, $perPage)->values();

        return new LengthAwarePaginator(
            $paginatedItems,
            $mergedItems->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    protected function normalizeAdminUserFilters(Request $request): array
    {
        $keyword = trim((string) ($request->input('keyword', $request->input('Keyword', ''))));
        $role = trim((string) $request->input('role', ''));
        $branchId = trim((string) $request->input('branch_id', ''));
        $speciality = trim((string) $request->input('speciality', ''));
        $status = $request->input('status', '');
        $sort = trim((string) $request->input('sort', 'name'));
        $direction = strtolower((string) $request->input('direction', 'asc'));

        return [
            'keyword' => $keyword !== '' ? $keyword : null,
            'role' => $role !== '' ? $role : null,
            'branch_id' => $branchId !== '' ? $branchId : null,
            'speciality' => $speciality !== '' ? $speciality : null,
            'status' => $status === '' ? null : filter_var($status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'sort' => in_array($sort, ['name', 'email', 'branch_name', 'role_name', 'is_active', 'created_at'], true) ? $sort : 'name',
            'direction' => $direction === 'desc' ? 'desc' : 'asc',
        ];
    }

    protected function transformAdminUserRecord(User $user): array
    {
        $doctor = $user->doctor;
        $branch = $doctor?->hospital ?? ($user->hospital_id ? Hospital::query()->select('id', 'name', 'main_branch_id')->find($user->hospital_id) : null);
        $specialities = $doctor?->specialities?->pluck('name')->filter()->values() ?? collect();
        $roles = $user->roles
            ->filter(fn ($role) => in_array($role->name, ['Doctor', 'Virtual Assistant', 'Biller'], true))
            ->values();

        return [
            ...$user->toArray(),
            'table' => 'users',
            'entity_type' => 'user',
            'display_name' => $doctor?->name ?: $user->name,
            'branch_id' => $branch?->id,
            'branch_name' => $branch?->name,
            'branch_type' => $branch ? ($branch->main_branch_id ? 'Sub Branch' : 'Main Branch') : null,
            'role_name' => $roles->pluck('name')->implode(', '),
            'primary_role' => $roles->first()?->name ?? 'Doctor',
            'speciality_names' => $specialities->all(),
            'speciality_label' => $specialities->implode(', '),
            'created_label' => dateFormat($user->created_at),
            'status_label' => $user->is_active ? 'Active' : 'Inactive',
            'profile_photo_url' => $user->profile_photo_url ?: $doctor?->profile_photo_url,
        ];
    }

    protected function transformAdminDoctorRecord(Doctor $doctor): array
    {
        $branch = $doctor->hospital;
        $specialities = $doctor->specialities?->pluck('name')->filter()->values() ?? collect();
        $doctorArray = $doctor->toArray();

        return array_merge($doctorArray, [
            'table' => 'doctors',
            'entity_type' => 'doctor',
            'display_name' => $doctor->name,
            'email' => $doctor->email ?? null,
            'mobile' => $doctor->mobile ?? null,
            'roles' => [
                ['name' => 'Doctor'],
            ],
            'doctor' => $doctorArray,
            'address' => $doctor->user?->address?->toArray(),
            'branch_id' => $branch?->id,
            'branch_name' => $branch?->name,
            'branch_type' => $branch ? ($branch->main_branch_id ? 'Sub Branch' : 'Main Branch') : null,
            'role_name' => 'Doctor',
            'primary_role' => 'Doctor',
            'speciality_names' => $specialities->all(),
            'speciality_label' => $specialities->implode(', '),
            'created_label' => dateFormat($doctor->created_at),
            'status_label' => $doctor->is_active ? 'Active' : 'Inactive',
            'profile_photo_url' => $doctor->profile_photo_url,
        ]);
    }

    protected function normalizeAdminUserSortValue($value)
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->getTimestamp();
        }

        $dateValue = strtotime((string) $value);
        if ($dateValue !== false && preg_match('/\d{4}-\d{2}-\d{2}|\w{3}\s+\d{1,2},\s+\d{4}/', (string) $value)) {
            return $dateValue;
        }

        return strtolower((string) $value);
    }

    /*
    * Upsert a user record
    */
    public function upsert($data)
    {
        // Check if this is an update or create
        $isUpdate = ! empty($data['user_id'] ?? $data['id'] ?? null);
        $userId = $data['user_id'] ?? $data['id'] ?? null;
        $oldUser = $isUpdate ? User::find($userId) : null;
        $oldRoleNames = $oldUser?->roles()->pluck('name')->values()->all() ?? [];

        $user = DB::transaction(function () use ($data) {
            $input = is_array($data) ? $data : [];

            $roles = $this->role;
            if (is_string($roles) && str_contains($roles, ',')) {
                $roles = array_map('trim', explode(',', $roles));
            }
            $roles = is_array($roles) ? $roles : [$roles];
            $isDoctorRole = in_array('Doctor', $roles, true);

            $baseName = $input['practice_name'] ?? $data['name'] ?? trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? ''));
            if ($isDoctorRole && ! empty($baseName) && stripos($baseName, 'Dr.') !== 0) {
                $baseName = 'Dr. '.$baseName;
            }

            $updateData = [
                'name' => $baseName,
                'first_name' => $input['practice_name'] ?? $data['first_name'] ?? null,
                'last_name' => $input['practice_name'] ?? $data['last_name'] ?? null,
                'email' => $input['practice_email'] ?? $data['email'],
                'mobile' => $data['mobile'],
                'subscription_plan_id' => $data['subscription_plan_id'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ];

            // Handle profile photo upload
            try {
                if (request()->hasFile('profile_photo') && request()->file('profile_photo')->isValid()) {
                    $updateData['profile_photo_path'] = $this->uploadPublic(request()->file('profile_photo'), 'profile-photos');
                } elseif (request()->hasFile('profile_photo_path') && request()->file('profile_photo_path')->isValid()) {
                    $updateData['profile_photo_path'] = $this->uploadPublic(request()->file('profile_photo_path'), 'profile-photos');
                }
            } catch (\Illuminate\Validation\ValidationException $e) {
                throw $e; // Re-throw validation exceptions to be handled by the controller
            } catch (\Exception $e) {
                \Log::error('Profile photo upload failed: '.$e->getMessage());
                // Continue without profile photo if upload fails
            }

            if (request()->filled('profile_picture')) {
                $updateData['profile_photo_path'] = request()->input('profile_picture');
            }

            if (isset($data['password'])) {
                $updateData['password'] = bcrypt($data['password']);
            }

            $updateData['sex'] = $data['sex'] ?? null;
            if ($roles[0] == 'Biller' || $roles[0] == 'Virtual Assistant') {
                $updateData = array_merge($updateData, ['hospital_id' => $data['hospitalId'] ?? null]);
            }

            $user = User::updateOrCreate(
                ['id' => $data['user_id'] ?? $data['id'] ?? null],

                $updateData
            );
            $user->syncRoles($roles);
            $user->syncPermissions($data['permissions'] ?? []);
            if (in_array('Doctor', $roles)) {
                $data['is_verified'] = 1;
                $data['is_active'] = 1;
                $data['appointment_slot_duration'] = 0;
                $data['hospital_address'] = $data['street_address1'] ?? null;

                // Handle practice_logo file upload
                if (request()->hasFile('practice_logo') && request()->file('practice_logo')->isValid()) {
                    $data['practice_logo'] = $this->uploadPublic(request()->file('practice_logo'), 'practice-logos');
                } elseif (request()->filled('practice_logo') && ! request()->hasFile('practice_logo')) {
                    // Handle when practice_logo is a string path (existing logo)
                    $data['practice_logo'] = request()->input('practice_logo');
                }
            }
            $data['name'] = ($data['first_name'] ?? '').' '.($data['last_name'] ?? '');
            $data['hospitalId'] = $data['hospitalId'] ?? null;

            if (is_array($roles) && in_array('Admin', $roles)) {
                $hospital = (new HospitalService)->saveHospital($data, $user);
                $data['hospitalId'] = $hospital->id; // ✅ works
            }

            if (in_array('Doctor', $roles)) {
                (new DoctorService)->saveDoctor($data, $user);
            }

            if (in_array('Patient', $roles)) {
                $data['user_id'] = $user->id;
                (new PatientService)->savePatient($data);
            }
            if (in_array('Lab', $roles)) {
                $data['user_id'] = $user->id;
                (new LabService)->saveLab($data);
            }
            if (in_array('Pharmacy', $roles)) {
                $data['user_id'] = $user->id;
                (new PharmacyService)->upsert($data);
            }

            Address::updateOrCreate(
                ['user_id' => $user->id],
                [

                    'address_1' => $data['street_address1'] ?? null,
                    'address_2' => $data['street_address2'] ?? null,
                    'city' => $data['city'] ?? null,
                    'state' => $data['state'] ?? null,
                    'country' => $data['country'] ?? null,
                    'zip' => $data['zip'] ?? null,
                ]
            );

            return $user;
        });

        // // Audit logging
        if ($user) {
            if ($isUpdate && $oldUser) {
                $this->logUpdate($oldUser, $user, 'User updated via UserService');
            } else {

                if (is_array($this->role)) {
                    $role = implode(',', $this->role);
                } else {
                    $role = $this->role;
                }

                $this->logCreate($user, 'New user created with role: '.($role ?? 'Unknown'));
            }
        }

        // Send verification email for new users (after successful transaction)
        if ($user && ! empty($data['password'])) {
            $token = Str::random(40);
            UserVerify::create([
                'user_id' => $user->id,
                'token' => $token,
            ]);
            try {
                app(EmailNotificationService::class)->queueVerificationEmail($user, $token);
            } catch (\Throwable $e) {
                \Log::error('Failed to send user verification email: '.$e->getMessage());
            }

            // Store password in cache for UserCredentialsMail after email verification
            try {
                \Illuminate\Support\Facades\Cache::put(
                    'signup_password_'.$user->id,
                    encrypt($data['password']),
                    3600 // 1 hour expiry
                );
            } catch (\Throwable $e) {
                \Log::error('Failed to cache signup password: '.$e->getMessage());
            }
        }

        if ($user && ! $isUpdate) {
            $hospitalId = $user->hospital_id
                ?: $user->doctor?->hospital_id
                ?: Hospital::where('user_id', $user->id)->value('id')
                ?: (auth()->user()?->hospital?->id ?? auth()->user()?->doctor?->hospital_id);

            app(EmailNotificationService::class)->queueUserAddedAdminAlert(
                $user->fresh('roles', 'doctor'),
                $hospitalId,
                is_array($this->role) ? implode(', ', $this->role) : (string) $this->role
            );
        }

        if ($user) {
            $notificationService = app(InAppNotificationService::class);
            $newRoleNames = $user->roles()->pluck('name')->values()->all();
            $roleLabel = implode(', ', $newRoleNames ?: ['User']);

            if (! $isUpdate) {
                $notificationService->notifySuperAdmins(
                    $notificationService->buildPayload(
                        'New user registered',
                        "{$user->name} registered on the platform as {$roleLabel}.",
                        'platform_user_registered',
                        [
                            'related_model_type' => User::class,
                            'related_model_id' => $user->id,
                            'meta' => [
                                'email' => $user->email,
                                'roles' => $newRoleNames,
                            ],
                        ]
                    )
                );

                if (in_array('SuperAdmin', $newRoleNames, true)) {
                    $notificationService->notifySuperAdmins(
                        $notificationService->buildPayload(
                            'Super Admin created',
                            "{$user->name} was created with Super Admin access.",
                            'superadmin_created',
                            [
                                'related_model_type' => User::class,
                                'related_model_id' => $user->id,
                            ]
                        )
                    );
                }
            } elseif ($oldRoleNames !== $newRoleNames && (
                array_intersect($oldRoleNames, ['Admin', 'SuperAdmin']) ||
                array_intersect($newRoleNames, ['Admin', 'SuperAdmin'])
            )) {
                $notificationService->notifySuperAdmins(
                    $notificationService->buildPayload(
                        'Privileged role updated',
                        "{$user->name}'s privileged roles changed from ".(implode(', ', $oldRoleNames ?: ['None'])).' to '.(implode(', ', $newRoleNames ?: ['None'])).'.',
                        'privileged_role_changed',
                        [
                            'related_model_type' => User::class,
                            'related_model_id' => $user->id,
                            'meta' => [
                                'old_roles' => $oldRoleNames,
                                'new_roles' => $newRoleNames,
                            ],
                        ]
                    )
                );
            }
        }

        return $user;
    }

    /**
     * Get the permissions for the admin role.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPermissions()
    {
        return Permission::where('guard_name', 'web')->orderBy('id', 'asc')->pluck('name');
    }
}
