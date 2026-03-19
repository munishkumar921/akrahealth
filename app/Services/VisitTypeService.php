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

        $query = VisitType::query()->with(['doctor', 'hospital']);

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
        $query->when($request->filled('search'), function ($q) use ($request) {
            $q->where(function ($sub) use ($request) {
                $sub->where('name', 'like', "%{$request->search}%")
                    ->orWhere('colors', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */
        return $query
            ->orderBy('created_at', 'desc')
            ->paginate(request('per_page', paginateLimit()))
            ->withQueryString();
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
                'hospital_id' => $data['hospital_id'] ?? null,
                'doctor_id' => $data['doctor_id'] ?? $data['provider'] ?? null,
                'currency' => $data['currency'] ?? null,
                'price' => $data['price'] ?? null,
                'duration' => $data['duration'] ?? null,
            ]
        );
    }
}
