<?php

namespace App\Services;

use App\Models\VisitType;
use Illuminate\Http\Request;

class VisitTypeService
{
    /**
     * Create a new class instance.
     */
    public function list(Request $request)
    {
        $user = auth()->user();
        $hospital = $user->hospital;
        $doctor = $user->doctor;

        $query = VisitType::query()->with(['doctor.user', 'hospital']);

        /*
        |--------------------------------------------------------------------------
        | Hospital-wise access
        |--------------------------------------------------------------------------
        */

        // ✅ Hospital Admin
        if ($user->hasRole('Admin') && $hospital) {
            $query->where('hospital_id', $hospital->id);
        }

        // ✅ Doctor
        elseif ($doctor && $doctor->hospital_id) {
            $query->where('hospital_id', $doctor->hospital_id);
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
        $query->when($request->filled('keyword'), function ($q) use ($request) {
            $q->where(function ($sub) use ($request) {
                $sub->where('name', 'like', "%{$request->keyword}%")
                    ->orWhere('colors', 'like', "%{$request->keyword}%")
                    ->orWhere('description', 'like', "%{$request->keyword}%")
                    ->orWhere('currency', 'like', "%{$request->keyword}%")
                    ->orWhere('price', 'like', "%{$request->keyword}%")
                    ->orWhere('duration', 'like', "%{$request->keyword}%")
                    ->orWhereHas('doctor.user', function ($doctorQuery) use ($request) {
                        $doctorQuery->where('name', 'like', "%{$request->keyword}%");
                    });
            });
        });

        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('is_active', $request->status === 'true');
        });

        $query->when($request->filled('doctor_id'), function ($q) use ($request) {
            $q->where('doctor_id', $request->doctor_id);
        });

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */
        return $query
            ->orderBy('created_at', 'desc')
            ->paginate(request('per_page', paginateLimit()))
            ->withQueryString()
            ->through(function ($visitType) {
                return [
                    'id' => $visitType->id,
                    'name' => $visitType->name,
                    'description' => $visitType->description,
                    'colors' => $visitType->colors,
                    'is_active' => (bool) $visitType->is_active,
                    'currency' => $visitType->currency,
                    'price' => $visitType->price,
                    'duration' => $visitType->duration,
                    'provider_name' => $visitType->doctor?->user?->name ?? 'All Providers',
                    'status_label' => $visitType->is_active ? 'Active' : 'Inactive',
                    'created_label' => $visitType->created_at?->format('Y-m-d'),
                ];
            });
    }

    public function upsert($data)
    {
        return VisitType::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'name' => $data['name'],
                'description' => $data['description'],
                'colors' => $data['colors'],
                'is_active' => $data['is_active'],
                'hospital_id' => auth()->user()->doctor->hospital_id,
                'doctor_id' => auth()->user()->doctor->id, // will be removed
                'currency' => $data['currency'] ?? null,
                'price' => $data['price'] ?? null,
                'duration' => $data['duration'] ?? null,
            ]
        );
    }
}
