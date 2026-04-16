<?php

namespace App\Services;

use App\Models\Hospital;
use App\Models\Pharmacy;
use App\Traits\SMSTrait;
use App\Traits\UploadFileTrait;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PharmacyService
{
    use SMSTrait, UploadFileTrait;

    /**
     * list
     *
     * @param  mixed  $request
     * @return void
     */
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
        ];

        $query = Pharmacy::with('user')
            ->select('pharmacies.*')
            ->leftJoin('countries', 'pharmacies.country', '=', 'countries.name')
            ->leftJoin('states', 'pharmacies.state', '=', 'states.name');

        // Hospital-wise access control
        if ($user->hasRole('Admin') && $hospitalId) {
            $query->where('pharmacies.hospital_id', $hospitalId);
        } elseif ($doctor && $doctor->hospital_id) {
            $query->where('pharmacies.hospital_id', $doctor->hospital_id);
        } elseif (! $user->hasRole('Super Admin')) {
            // Restrict access if no hospital association found and not a Super Admin
            $query->whereRaw('1 = 0');
        }

        $pharmacies = $query
            ->when($filters['keyword'], function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('pharmacies.name', 'like', "%{$keyword}%")
                        ->orWhere('pharmacies.license_number', 'like', "%{$keyword}%")
                        ->orWhere('pharmacies.address', 'like', "%{$keyword}%")
                        ->orWhere('pharmacies.city', 'like', "%{$keyword}%")
                        ->orWhere('pharmacies.pincode', 'like', "%{$keyword}%")
                        ->orWhere('pharmacies.mobile', 'like', "%{$keyword}%")
                        ->orWhere('pharmacies.email', 'like', "%{$keyword}%")
                        ->orWhereHas('user', function ($uq) use ($keyword) {
                            $uq->where('name', 'like', "%{$keyword}%")
                                ->orWhere('email', 'like', "%{$keyword}%")
                                ->orWhere('mobile', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($filters['verification'] !== '', function ($query) use ($filters) {
                $query->where('pharmacies.is_verified', filter_var($filters['verification'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
            })
            ->when($filters['status'] !== '', function ($query) use ($filters) {
                $query->where('pharmacies.is_active', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
            })
            ->orderBy('pharmacies.created_at', 'desc')
            ->paginate($request->integer('per_page', paginateLimit()))
            ->withQueryString();

        $pharmacies->getCollection()->transform(function (Pharmacy $pharmacy) {
            $pharmacy->display_name = $pharmacy->name;
            $pharmacy->contact_name = trim((string) ($pharmacy->user?->name ?? $pharmacy->contact_person ?? ''));
            $pharmacy->contact_email = $pharmacy->email ?: $pharmacy->user?->email;
            $pharmacy->contact_mobile = $pharmacy->mobile ?: $pharmacy->user?->mobile;
            $pharmacy->banner_url = $pharmacy->banner
                ? (str_starts_with($pharmacy->banner, 'http') ? $pharmacy->banner : Storage::url($pharmacy->banner))
                : asset('images/avatar.webp');
            $pharmacy->address_sort = collect([
                $pharmacy->address,
                $pharmacy->city,
                $pharmacy->state,
                $pharmacy->country,
                $pharmacy->pincode,
            ])->filter()->implode(', ');
            $pharmacy->created_label = optional($pharmacy->created_at)?->format('F d, Y');
            $pharmacy->status_label = $pharmacy->is_active ? 'Active' : 'Inactive';
            $pharmacy->verification_label = $pharmacy->is_verified ? 'Verified' : 'Pending';

            return $pharmacy;
        });

        return $pharmacies;
    }

    /**
     * upsert
     *
     * @param  mixed  $data
     * @return void
     */
    public function upsert(array $data)
    {
        $pharmacy = DB::transaction(function () use ($data) {

            $banner = null;
            if (request()->hasFile('banner') && request()->file('banner')->isValid()) {
                $banner = $this->uploadPublic(request()->file('banner'));
            }

            $lat_lng = getLatLong($data['address']['en'] ?? null);

            $pharmacy = Pharmacy::where('id', $data['id'] ?? null)->first();

            if (! $pharmacy) {
                $pharmacy = new Pharmacy;
            }

            $pharmacy->user_id = $data['user_id'] ?? null;
            $pharmacy->country = $data['country'] ?? null;
            $pharmacy->state = $data['state'] ?? null;
            $pharmacy->name = $data['pharmacy_name'];
            $pharmacy->license_number = $data['license_number'];
            $pharmacy->address = $data['street_address1'] ?? null;
            $pharmacy->city = $data['city'] ?? [];
            $pharmacy->pincode = $data['zip'] ?? null;
            $pharmacy->latitude = $lat_lng['lat'] ?? null;
            $pharmacy->longitude = $lat_lng['lng'] ?? null;
            $pharmacy->contact_person = $data['first_name'] ?? null;
            $pharmacy->mobile = $data['pharmacy_mobile'];
            $pharmacy->email = $data['pharmacy_email'];
            $pharmacy->hospital_id = auth()->user()->doctor?->hospital_id ?? auth()->user()->hospital?->id ?? null;
            $pharmacy->opening_time = $data['opening_time'] ?? null;
            $pharmacy->closing_time = $data['closing_time'] ?? null;
            $pharmacy->is_verified = $data['is_verified'] ?? false;
            $pharmacy->about = $data['about'] ?? null;
            if ($banner) {
                $pharmacy->banner = $banner;
            }
            $pharmacy->is_active = $data['is_active'] ?? false;
            $pharmacy->license = $data['license'] ?? null;
            $pharmacy->gst_license = $data['gst_license'] ?? null;
            $pharmacy->store_front_photo = $data['store_front_photo'] ?? null;
            $pharmacy->owner_id_proof = $data['owner_id_proof'] ?? null;
            $pharmacy->working_hours = $data['working_hours'] ?? null;
            $pharmacy->save();

            return $pharmacy;
        });
    }
}
