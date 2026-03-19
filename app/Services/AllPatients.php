<?php

namespace App\Services;

use App\Models\DoctorPatient;
use Illuminate\Support\Facades\Auth;

class AllPatients
{
    public function List($request)
    {
        $search = $request->input('search');

        $data = DoctorPatient::query()
            ->where('doctor_id', Auth::user()->doctor?->id)
            ->whereHas('patient', function ($query) use ($search) {

                $query->where('is_active', true);

                // 🔍 Apply search if exists
                if (! empty($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                }
            })
            ->with(['patient.user'])
            ->orderBy('id', 'DESC')
            ->paginate(request('per_page', paginateLimit()))->withQueryString();
        // 🔹 Transform paginated collection
        $data->getCollection()->transform(function ($item) {

            $patient = $item->patient;

            return [
                'id' => $patient->id ?? null,
                'name' => $patient->name ?? null,

                // Prefer patient mobile, fallback to user mobile (null-safe)
                'phone' => $patient->mobile
                    ?? optional($patient->user)->mobile
                    ?? null,

                // Prefer patient email, fallback to user email (null-safe)
                'email' => $patient->email
                    ?? optional($patient->user)->email
                    ?? null,

                // If user exists → already registered → false
                'register_to_portal' => ! $patient->user,

                'is_active' => $patient->is_active ?? null,

                'created_at' => optional($patient->created_at)->format('M d, Y'),
            ];
        });

        return $data;
    }
}
