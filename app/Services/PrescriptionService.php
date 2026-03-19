<?php

namespace App\Services;

use App\Models\Prescription;

class PrescriptionService
{
    /**
     * list
     *
     * @param  mixed  $request
     * @return void
     */
    public function list($request)
    {
        return Prescription::with(['patient.user', 'doctor.user'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%");
                    $q->orWhereHas('patient.user', function ($qq) use ($search) {
                        $qq->where('name', 'like', "%{$search}%");
                    })->orWhereHas('doctor.user', function ($qq) use ($search) {
                        $qq->where('name', 'like', "%{$search}%");
                    });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(request('per_page', paginateLimit()))->withQueryString();
    }
}
