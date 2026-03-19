<?php

namespace App\Services;

use App\Models\LabOrder;

class LabOrderService
{
    /**
     * list
     *
     * @param  mixed  $request
     * @return void
     */
    public function list($request)
    {
        $orders = LabOrder::with(['appointment.patient.user', 'appointment.doctor.user', 'lab'])
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas(
                    'appointment.patient.user',
                    fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                )->orWhereHas(
                    'appointment.doctor.user',
                    fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                )->orWhereHas(
                    'lab',
                    fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                );
            })
            ->when(
                $request->status,
                fn ($q) => $q->where('status', $request->status)
            )
            ->when(
                $request->date,
                fn ($q) => $q->whereDate('scheduled_at', $request->date)
            )
            ->orderBy('scheduled_at', 'desc')
            ->paginate(request('per_page', paginateLimit()))->withQueryString();

        return $orders;
    }
}
