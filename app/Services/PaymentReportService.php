<?php

namespace App\Services;

use App\Models\SubscriptionPayment;
use App\Traits\SMSTrait;
use Illuminate\Support\Facades\DB;

class PaymentReportService
{
    use SMSTrait;

    /**
     * list
     *
     * @param  mixed  $request
     * @return void
     */
    public function list($request)
    {
        return SubscriptionPayment::with([
            'doctor.user',
            'subscriptionPlan:id,title',
        ])
            ->when($request->search, function ($q) use ($request) {
                $s = trim($request->search);
                $q->where(function ($qq) use ($s) {
                    $qq->where('uuid', 'like', "%{$s}%")
                        ->orWhere('razorpay_order_id', 'like', "%{$s}%")
                        ->orWhere('razorpay_payment_id', 'like', "%{$s}%")
                        ->orWhereHas('doctor.user', function ($u) use ($s) {
                            $u->where('email', 'like', "%{$s}%")
                                ->orWhere('mobile', 'like', "%{$s}%")
                                ->orWhere('name', 'like', "%{$s}%");
                        });
                });
            })
    // status filter: captured / failed / refunded / created
            ->when($request->filled('status'), fn ($q) => $q->whereIn('status', (array) $request->status)
            )
    // optional filters
            ->when($request->filled('doctor_id'), fn ($q) => $q->where('doctor_id', $request->doctor_id)
            )
            ->when($request->filled('subscription_plan_id'), fn ($q) => $q->where('subscription_plan_id', $request->subscription_plan_id)
            )
            ->when($request->filled('currency'), fn ($q) => $q->where('currency', $request->currency)
            )
    // date filters (supports single day or range)
            ->when($request->filled('date'), fn ($q) => $q->whereDate('created_at', $request->date)
            )
            ->when($request->filled('start_date') && $request->filled('end_date'), function ($q) use ($request) {
                $q->whereBetween(DB::raw('DATE(created_at)'), [
                    $request->start_date, $request->end_date,
                ]);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(request('per_page', paginateLimit()))->withQueryString();

    }
}
