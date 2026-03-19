<?php

namespace App\Services;

use App\Models\Hospital;
use App\Models\Lab;
use App\Traits\UploadFileTrait;
use Illuminate\Support\Facades\DB;

class LabService
{
    use UploadFileTrait;

    public function list($request)
    {
        $user = auth()->user();
        $hospital = Hospital::where('user_id', $user->id)->first();
        $hospitalId = $hospital?->id;
        $doctor = $user->doctor;

        $query = Lab::query()
            ->with(['user', 'user.address'])
            ->join('users', 'labs.user_id', '=', 'users.id')
            ->select(
                'labs.*',
                'users.name as user_name',
                'users.email as user_email',
                'users.mobile as user_mobile',
            );

        /*
    |--------------------------------------------------------------------------
    | Hospital-wise access
    |--------------------------------------------------------------------------
    */

        // ✅ Hospital Admin
        if ($user->hasRole('Admin') && $hospitalId) {
            $query->where('labs.hospital_id', $hospitalId);
        }

        // ✅ Doctor (optional)
        elseif ($doctor && $doctor->hospital_id) {
            $query->where('labs.hospital_id', $doctor->hospital_id);
        }

        // ❌ No access
        else {
            $query->whereRaw('1 = 0');
        }

        /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */
        $query->when(request('search'), function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('labs.name', 'like', "%{$search}%")
                    ->orWhere('labs.license_number', 'like', "%{$search}%")
                    ->orWhere('labs.city', 'like', "%{$search}%")
                    ->orWhere('labs.pincode', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            });
        });

        /*
    |--------------------------------------------------------------------------
    | Status filters
    |--------------------------------------------------------------------------
    */
        $query->when(
            request('is_verified') !== null,
            fn ($q) => $q->where('labs.is_verified', request('is_verified'))
        );

        $query->when(
            request('is_active') !== null,
            fn ($q) => $q->where('labs.is_active', request('is_active'))
        );

        /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
        return $query
            ->orderBy('labs.created_at', 'desc')
            ->paginate(request('per_page', paginateLimit()))
            ->withQueryString();
    }

    /**
     * saveLab
     *
     * @param  mixed  $data
     * @return void
     */
    public function saveLab(array $data)
    {
        $lab = DB::transaction(function () use ($data) {

            $banner = null;

            // Handle banner file upload from FormData
            if (isset($data['banner']) && $data['banner'] instanceof \Illuminate\Http\UploadedFile) {
                $banner = $this->uploadPublic($data['banner']);
            } elseif (request()->hasFile('banner') && request()->file('banner')->isValid()) {
                $banner = $this->uploadPublic(request()->file('banner'));
            } elseif (! empty($data['banner']) && is_string($data['banner'])) {
                // Banner is already a string path/name, keep it
                $banner = $data['banner'];
            }

            $lat_lng = getLatLong($data['street_address1'] ?? null);

            $lab = Lab::updateOrCreate(
                ['id' => $data['id'] ?? null],
                [
                    'user_id' => $data['user_id'] ?? null,
                    'name' => $data['lab_name'] ?? $data['name'] ?? null,
                    'email' => $data['lab_email'] ?? $data['email'] ?? null,
                    'mobile' => $data['lab_mobile'] ?? $data['mobile'] ?? null,
                    'license_number' => $data['license_number'] ?? null,
                    'banner' => $banner,
                    'is_verified' => $data['is_verified'] ?? 0,
                    'is_active' => $data['is_active'] ?? 1,
                    'hospital_id' => auth()->user()->doctor?->hospital_id ?? auth()->user()->hospital?->id ?? null,
                    'about' => $data['about'] ?? null,
                    'opening_time' => $data['opening_time'] ?? null,
                    'closing_time' => $data['closing_time'] ?? null,
                    'sample_pickup_supported' => $data['pickup'] ?? 0,
                    'website' => $data['website'] ?? null,
                    'categories' => ! empty($data['categories']) && is_array($data['categories'])
                    ? $data['categories']
                    : null,

                ]
            );

            return $lab;
        });
    }
}
