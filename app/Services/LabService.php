<?php

namespace App\Services;

use App\Models\Hospital;
use App\Models\Lab;
use App\Traits\UploadFileTrait;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class LabService
{
    use UploadFileTrait;

    public function list(Request $request): LengthAwarePaginator
    {
        $user = auth()->user();
        $hospital = Hospital::where('user_id', $user->id)->first();
        $hospitalId = $hospital?->id;
        $doctor = $user->doctor;
        $filters = [
            'keyword' => trim((string) $request->input('keyword', '')),
            'status' => $request->input('status', ''),
            'verification' => $request->input('verification', ''),
            'category' => $request->input('category', ''),
        ];

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
        $query->when($filters['keyword'], function ($query, $keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('labs.name', 'like', "%{$keyword}%")
                    ->orWhere('labs.email', 'like', "%{$keyword}%")
                    ->orWhere('labs.mobile', 'like', "%{$keyword}%")
                    ->orWhere('labs.license_number', 'like', "%{$keyword}%")
                    ->orWhere('users.name', 'like', "%{$keyword}%")
                    ->orWhere('users.email', 'like', "%{$keyword}%")
                    ->orWhere('users.mobile', 'like', "%{$keyword}%");
            });
        });

        /*
    |--------------------------------------------------------------------------
    | Status filters
    |--------------------------------------------------------------------------
    */
        $query->when($filters['status'] !== '', function ($q) use ($filters) {
            $q->where('labs.is_active', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
        });

        $query->when($filters['verification'] !== '', function ($q) use ($filters) {
            $q->where('labs.is_verified', filter_var($filters['verification'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
        });

        $query->when($filters['category'] !== '', function ($q) use ($filters) {
            $q->where(function ($categoryQuery) use ($filters) {
                $categoryQuery
                    ->whereJsonContains('labs.categories', $filters['category'])
                    ->orWhere('labs.categories', 'like', '%"'.$filters['category'].'"%');
            });
        });

        /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
        $labs = $query
            ->orderBy('labs.created_at', 'desc')
            ->paginate($request->integer('per_page', paginateLimit()))
            ->withQueryString();

        $labs->getCollection()->transform(function (Lab $lab) {
            $address = $lab->user?->address;

            $lab->display_name = $lab->name ?: $lab->user_name;
            $lab->email = $lab->email ?: $lab->user_email;
            $lab->mobile = $lab->mobile ?: $lab->user_mobile;
            $lab->banner_url = $lab->banner
                ? (str_starts_with($lab->banner, 'http') ? $lab->banner : Storage::url($lab->banner))
                : asset('images/avatar.webp');
            $lab->created_label = optional($lab->created_at)?->format('F d, Y');
            $lab->status_label = $lab->is_active ? 'Active' : 'Inactive';
            $lab->verification_label = $lab->is_verified ? 'Verified' : 'Pending';
            $lab->address_sort = collect([
                $address?->address_1,
                $address?->address_2,
                $address?->city,
                $address?->state,
                $address?->country,
                $address?->zip,
            ])->filter()->implode(', ');

            return $lab;
        });

        return $labs;
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
