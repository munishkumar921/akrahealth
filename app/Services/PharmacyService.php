<?php

namespace App\Services;

use App\Models\Hospital;
use App\Models\Pharmacy;
use App\Traits\SMSTrait;
use App\Traits\UploadFileTrait;
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
    public function list($request)
    {
        $user = auth()->user();
        $hospital = Hospital::where('user_id', $user->id)->first();
        $hospitalId = $hospital?->id;
        $doctor = $user->doctor;

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

        return $query
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('pharmacies.name', 'like', "%{$search}%")
                        ->orWhere('pharmacies.license_number', 'like', "%{$search}%")
                        ->orWhere('pharmacies.city', 'like', "%{$search}%")
                        ->orWhere('pharmacies.pincode', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when(request('is_verified') !== null, function ($query) {
                $query->where('pharmacies.is_verified', request('is_verified'));
            })
            ->when(request('is_active') !== null, function ($query) {
                $query->where('pharmacies.is_active', request('is_active'));
            })
            ->orderBy('pharmacies.created_at', 'desc')
            ->paginate(request('per_page', paginateLimit()))->withQueryString();
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
