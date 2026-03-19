<?php

namespace App\Services;

use App\Mail\UserCredentialsMail;
use App\Mail\UserVerificationMail;
use App\Models\Address;
use App\Models\User;
use App\Models\UserVerify;
use App\Traits\EmailTrait;
use App\Traits\UploadFileTrait;
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

    /*
    * Upsert a user record
    */
    public function upsert($data)
    {
        // Check if this is an update or create
        $isUpdate = ! empty($data['user_id'] ?? $data['id'] ?? null);
        $userId = $data['user_id'] ?? $data['id'] ?? null;
        $oldUser = $isUpdate ? User::find($userId) : null;

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
                $this->queueMailable(new UserVerificationMail(['token' => $token]), $user->email);
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

        // if ($user && !empty($data['password'])) {
        //      \Log::info($data['password']);
        //     $mailData = [
        //         'name' => $user->name,
        //         'email' => $user->email,
        //         'password' => $data['password'], // Send plain password
        //     ];
        //     try {
        //         $this->sendMailable(new UserCredentialsMail($mailData), $user->email);
        //      } catch (\Throwable $e) {
        //         \Log::error('Failed to send user credentials email: ' . $e->getMessage());
        //     }
        // }
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
