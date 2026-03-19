<?php

namespace App\Services;

use App\Models\PharmacyOrder;
use App\Traits\SMSTrait;

class PharmacyReportService
{
    use SMSTrait;

    public function list($request)
    {
        return PharmacyOrder::query()
            ->with([
                'pharmacy:id,name',
                'patient.user:id,name,email,mobile',
            ])
            ->leftJoin('pharmacies', 'pharmacy_orders.pharmacy_id', '=', 'pharmacies.id')
            ->select([
                'pharmacy_orders.*',
                'pharmacies.name as pharmacy_name',
            ])
            ->when($request->search, function ($q, $s) {
                $s = trim($s);
                $q->where(function ($qq) use ($s) {
                    $qq->where('pharmacy_orders.uuid', 'like', "%{$s}%")
                        ->orWhere('pharmacy_orders.order_no', 'like', "%{$s}%")
                        ->orWhere('pharmacy_orders.payment_reference', 'like', "%{$s}%")
                        ->orWhereHas('pharmacy', fn ($pp) => $pp->where('name', 'like', "%{$s}%"))
                        ->orWhereHas('patient.user', function ($uu) use ($s) {
                            $uu->where('email', 'like', "%{$s}%")
                                ->orWhere('mobile', 'like', "%{$s}%")
                               // if users.name is plain string, switch to ->orWhere('name', 'like', "%{$s}%")
                                ->orWhere('name->en', 'like', "%{$s}%");
                        });
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->whereIn('pharmacy_orders.status', (array) $request->status)
            )
            ->when($request->filled('payment_status'), fn ($q) => $q->whereIn('pharmacy_orders.payment_status', (array) $request->payment_status)
            )
            ->when($request->filled('pharmacy_id'), fn ($q) => $q->where('pharmacy_orders.pharmacy_id', $request->pharmacy_id)
            )
            ->when($request->filled('date'), fn ($q) => $q->whereDate('pharmacy_orders.created_at', $request->date)
            )
            ->orderByDesc('pharmacy_orders.created_at')
            ->paginate(request('per_page', paginateLimit()))->withQueryString();
    }
}
