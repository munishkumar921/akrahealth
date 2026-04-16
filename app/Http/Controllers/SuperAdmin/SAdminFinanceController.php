<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SAdminFinanceController extends Controller
{
    public function financedashboard(Request $request)
    {
        $filters = [
            'keyword' => trim((string) $request->input('keyword', '')),
            'currency' => trim((string) $request->input('currency', '')),
            'frequency' => trim((string) $request->input('frequency', '')),
            'status' => trim((string) $request->input('status', '')),
            'payment_status' => trim((string) $request->input('payment_status', '')),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
        ];

        $baseQuery = UserSubscription::query()
            ->with(['user', 'subscriptionPlan'])
            ->when($filters['keyword'] !== '', function ($query) use ($filters) {
                $keyword = $filters['keyword'];
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('razorpay_order_id', 'like', "%{$keyword}%")
                        ->orWhere('razorpay_subscription_id', 'like', "%{$keyword}%")
                        ->orWhereHas('user', function ($userQuery) use ($keyword) {
                            $userQuery->where('name', 'like', "%{$keyword}%")
                                ->orWhere('email', 'like', "%{$keyword}%")
                                ->orWhere('mobile', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('subscriptionPlan', function ($planQuery) use ($keyword) {
                            $planQuery->where('title', 'like', "%{$keyword}%")
                                ->orWhere('frequency', 'like', "%{$keyword}%")
                                ->orWhere('currency', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($filters['currency'] !== '', function ($query) use ($filters) {
                $query->where(function ($subQuery) use ($filters) {
                    $subQuery->where('currency', $filters['currency'])
                        ->orWhereHas('subscriptionPlan', fn ($planQuery) => $planQuery->where('currency', $filters['currency']));
                });
            })
            ->when($filters['frequency'] !== '', fn ($query) => $query->whereHas('subscriptionPlan', fn ($planQuery) => $planQuery->where('frequency', $filters['frequency'])))
            ->when($filters['status'] !== '', fn ($query) => $query->where('status', $filters['status']))
            ->when($filters['payment_status'] !== '', fn ($query) => $query->where('payment_status', $filters['payment_status']))
            ->when($filters['date_from'] !== '', fn ($query) => $query->whereDate('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'] !== '', fn ($query) => $query->whereDate('created_at', '<=', $filters['date_to']));

        $paidQuery = (clone $baseQuery)->where('payment_status', 'paid');
        $pendingQuery = (clone $baseQuery)->where('payment_status', 'pending');

        $metrics = [
            'total_revenue' => (float) $paidQuery->sum('amount'),
            'successful_payments' => (clone $paidQuery)->count(),
            'active_subscribers' => (clone $baseQuery)->where('status', 'active')->count(),
            'pending_collections' => (clone $pendingQuery)->count(),
            'cancelled_subscribers' => (clone $baseQuery)->where('status', 'cancelled')->count(),
            'trial_subscribers' => (clone $baseQuery)
                ->where('status', 'active')
                ->where('payment_status', 'pending')
                ->count(),
        ];

        $chartStart = $filters['date_from'] !== ''
            ? Carbon::parse($filters['date_from'])->startOfMonth()
            : now()->startOfYear();
        $chartEnd = $filters['date_to'] !== ''
            ? Carbon::parse($filters['date_to'])->endOfMonth()
            : now()->endOfMonth();

        if ($chartEnd->lt($chartStart)) {
            [$chartStart, $chartEnd] = [$chartEnd->copy()->startOfMonth(), $chartStart->copy()->endOfMonth()];
        }

        $period = [];
        $cursor = $chartStart->copy();
        while ($cursor->lte($chartEnd) && count($period) < 24) {
            $period[] = $cursor->copy();
            $cursor->addMonth();
        }

        $monthlyRevenue = (clone $baseQuery)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as period, COALESCE(SUM(CASE WHEN payment_status = "paid" THEN amount ELSE 0 END), 0) as revenue')
            ->whereBetween('created_at', [$chartStart->copy()->startOfDay(), $chartEnd->copy()->endOfDay()])
            ->groupBy('period')
            ->pluck('revenue', 'period');

        $monthlySubscribers = (clone $baseQuery)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as period, COUNT(*) as total')
            ->whereBetween('created_at', [$chartStart->copy()->startOfDay(), $chartEnd->copy()->endOfDay()])
            ->groupBy('period')
            ->pluck('total', 'period');

        $paymentMix = (clone $baseQuery)
            ->select('payment_status', DB::raw('COUNT(*) as total'))
            ->groupBy('payment_status')
            ->pluck('total', 'payment_status');

        $topPlans = (clone $baseQuery)
            ->select('subscription_plan_id', DB::raw('COUNT(*) as subscribers'), DB::raw('COALESCE(SUM(CASE WHEN payment_status = "paid" THEN amount ELSE 0 END), 0) as revenue'))
            ->whereNotNull('subscription_plan_id')
            ->groupBy('subscription_plan_id')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                $plan = SubscriptionPlan::find($row->subscription_plan_id);

                return [
                    'id' => $row->subscription_plan_id,
                    'title' => $plan?->title ?? 'Unknown plan',
                    'frequency' => $plan?->frequency ?? 'N/A',
                    'currency' => $plan?->currency ?? 'INR',
                    'subscribers' => (int) $row->subscribers,
                    'revenue' => (float) $row->revenue,
                ];
            })
            ->values();

        $recentTransactions = (clone $baseQuery)
            ->orderByDesc('created_at')
            ->limit(6)
            ->get()
            ->map(function (UserSubscription $subscription) {
                return [
                    'id' => $subscription->id,
                    'user' => $subscription->user?->name ?? 'Unknown user',
                    'email' => $subscription->user?->email ?? 'N/A',
                    'plan_name' => $subscription->subscriptionPlan?->title ?? 'N/A',
                    'status' => ucfirst((string) ($subscription->status ?: 'pending')),
                    'payment_status' => ucfirst((string) ($subscription->payment_status ?: 'pending')),
                    'gateway' => $this->getGatewayName($subscription),
                    'amount' => (float) ($subscription->amount ?? 0),
                    'currency' => $subscription->currency ?: ($subscription->subscriptionPlan?->currency ?? 'INR'),
                    'created_label' => optional($subscription->created_at)->format('M d, Y h:i A'),
                    'order_reference' => $subscription->razorpay_order_id ?: ($subscription->razorpay_subscription_id ?: $subscription->id),
                ];
            })
            ->values();

        return Inertia::render('SAdmin/finance/Dashboard', [
            'metrics' => $metrics,
            'charts' => [
                'labels' => collect($period)->map(fn (Carbon $date) => $date->format('M Y'))->toArray(),
                'revenue' => collect($period)->map(fn (Carbon $date) => (float) ($monthlyRevenue[$date->format('Y-m')] ?? 0))->toArray(),
                'subscribers' => collect($period)->map(fn (Carbon $date) => (int) ($monthlySubscribers[$date->format('Y-m')] ?? 0))->toArray(),
                'payment_mix_labels' => collect($paymentMix)->keys()->map(fn ($status) => ucfirst((string) $status))->values()->toArray(),
                'payment_mix_values' => collect($paymentMix)->values()->map(fn ($value) => (int) $value)->toArray(),
            ],
            'topPlans' => $topPlans,
            'recentTransactions' => $recentTransactions,
            'filters' => $filters,
            'currencies' => UserSubscription::query()
                ->select('currency')
                ->whereNotNull('currency')
                ->distinct()
                ->orderBy('currency')
                ->pluck('currency')
                ->filter()
                ->values(),
            'frequencies' => SubscriptionPlan::query()
                ->select('frequency')
                ->whereNotNull('frequency')
                ->distinct()
                ->orderBy('frequency')
                ->pluck('frequency')
                ->filter()
                ->values(),
            'statuses' => UserSubscription::query()
                ->select('status')
                ->whereNotNull('status')
                ->distinct()
                ->orderBy('status')
                ->pluck('status')
                ->filter()
                ->values(),
            'paymentStatuses' => UserSubscription::query()
                ->select('payment_status')
                ->whereNotNull('payment_status')
                ->distinct()
                ->orderBy('payment_status')
                ->pluck('payment_status')
                ->filter()
                ->values(),
            'rangeLabel' => $chartStart->format('M d, Y').' - '.$chartEnd->format('M d, Y'),
        ]);
    }

    public function transaction(Request $request)
    {
        $filters = [
            'keyword' => trim((string) $request->input('keyword', $request->input('search', ''))),
            'gateway' => trim((string) $request->input('gateway', '')),
            'status' => trim((string) $request->input('status', '')),
            'payment_status' => trim((string) $request->input('payment_status', '')),
            'currency' => trim((string) $request->input('currency', '')),
            'plan_id' => trim((string) $request->input('plan_id', '')),
            'frequency' => trim((string) $request->input('frequency', '')),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
        ];

        $sort = (string) $request->input('sort', 'created_at');
        $direction = strtolower((string) $request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['created_at', 'amount', 'status', 'payment_status', 'gateway', 'user_name', 'plan_name'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'created_at';

        $query = UserSubscription::query()
            ->with(['user', 'subscriptionPlan'])
            ->when($filters['keyword'] !== '', function ($builder) use ($filters) {
                $keyword = $filters['keyword'];
                $builder->where(function ($query) use ($keyword) {
                    $query->where('razorpay_order_id', 'like', "%{$keyword}%")
                        ->orWhere('razorpay_subscription_id', 'like', "%{$keyword}%")
                        ->orWhere('payment_link_id', 'like', "%{$keyword}%")
                        ->orWhereHas('user', function ($userQuery) use ($keyword) {
                            $userQuery->where('name', 'like', "%{$keyword}%")
                                ->orWhere('email', 'like', "%{$keyword}%")
                                ->orWhere('mobile', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('subscriptionPlan', function ($planQuery) use ($keyword) {
                            $planQuery->where('title', 'like', "%{$keyword}%")
                                ->orWhere('frequency', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($filters['gateway'] !== '', function ($builder) use ($filters) {
                if ($filters['gateway'] === 'razorpay') {
                    $builder->where(function ($query) {
                        $query->whereNotNull('razorpay_subscription_id')
                            ->orWhereNotNull('razorpay_order_id');
                    });
                } elseif ($filters['gateway'] === 'manual') {
                    $builder->whereNull('razorpay_subscription_id')
                        ->whereNull('razorpay_order_id');
                }
            })
            ->when($filters['status'] !== '', fn ($builder) => $builder->where('status', $filters['status']))
            ->when($filters['payment_status'] !== '', fn ($builder) => $builder->where('payment_status', $filters['payment_status']))
            ->when($filters['currency'] !== '', function ($builder) use ($filters) {
                $builder->where(function ($query) use ($filters) {
                    $query->where('currency', $filters['currency'])
                        ->orWhereHas('subscriptionPlan', fn ($planQuery) => $planQuery->where('currency', $filters['currency']));
                });
            })
            ->when($filters['plan_id'] !== '', fn ($builder) => $builder->where('subscription_plan_id', $filters['plan_id']))
            ->when($filters['frequency'] !== '', fn ($builder) => $builder->whereHas('subscriptionPlan', fn ($planQuery) => $planQuery->where('frequency', $filters['frequency'])))
            ->when($filters['date_from'] !== '', fn ($builder) => $builder->whereDate('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'] !== '', fn ($builder) => $builder->whereDate('created_at', '<=', $filters['date_to']))
            ->when($sort === 'user_name', function ($builder) use ($direction) {
                $builder->orderBy(
                    DB::table('users')
                        ->select('name')
                        ->whereColumn('users.id', 'user_subscriptions.user_id')
                        ->limit(1),
                    $direction
                );
            })
            ->when($sort === 'plan_name', function ($builder) use ($direction) {
                $builder->orderBy(
                    SubscriptionPlan::select('title')
                        ->whereColumn('subscription_plans.id', 'user_subscriptions.subscription_plan_id')
                        ->limit(1),
                    $direction
                );
            })
            ->when($sort === 'gateway', function ($builder) use ($direction) {
                $builder->orderByRaw(
                    'CASE WHEN razorpay_subscription_id IS NOT NULL OR razorpay_order_id IS NOT NULL THEN "Razorpay" ELSE "Manual" END '.$direction
                );
            })
            ->when(! in_array($sort, ['user_name', 'plan_name', 'gateway'], true), fn ($builder) => $builder->orderBy($sort, $direction));

        $metrics = [
            'total_transactions' => (clone $query)->count(),
            'successful_transactions' => (clone $query)->where('payment_status', 'paid')->count(),
            'total_revenue' => (float) (clone $query)->where('payment_status', 'paid')->sum('amount'),
            'average_ticket' => (float) (clone $query)->where('payment_status', 'paid')->avg('amount'),
        ];

        $transactions = $query
            ->paginate((int) $request->input('per_page', paginateLimit()))
            ->withQueryString();

        $transactions->getCollection()->transform(function (UserSubscription $subscription) {
            $currency = $subscription->currency ?: ($subscription->subscriptionPlan?->currency ?? 'INR');

            return [
                'id' => $subscription->id,
                'user' => $subscription->user?->name ?? 'Unknown user',
                'email' => $subscription->user?->email ?? 'N/A',
                'mobile' => $subscription->user?->mobile ?? 'N/A',
                'profile_photo_url' => $subscription->user?->profile_photo_url,
                'status' => ucfirst((string) ($subscription->status ?: 'pending')),
                'payment_status' => ucfirst((string) ($subscription->payment_status ?: 'pending')),
                'plan_name' => $subscription->subscriptionPlan?->title ?? 'N/A',
                'plan_id' => $subscription->subscription_plan_id,
                'amount' => (float) ($subscription->amount ?? 0),
                'amount_label' => $currency.' '.number_format((float) ($subscription->amount ?? 0), 2),
                'currency' => $currency,
                'order_id' => $subscription->razorpay_order_id ?? $subscription->razorpay_subscription_id ?? $subscription->payment_link_id ?? $subscription->id,
                'gateway' => $this->getGatewayName($subscription),
                'gateway_label' => $this->getGatewayName($subscription),
                'created_on' => optional($subscription->created_at)->toDateTimeString(),
                'created_label' => optional($subscription->created_at)->format('M d, Y h:i A'),
                'subscribed_on' => $subscription->start_date?->format('M d, Y') ?? 'N/A',
                'frequency' => ucfirst((string) ($subscription->subscriptionPlan?->frequency ?? 'N/A')),
            ];
        });

        return Inertia::render('SAdmin/finance/Transaction', [
            'transactions' => $transactions,
            'metrics' => $metrics,
            'filters' => $filters,
            'currencies' => UserSubscription::query()
                ->select('currency')
                ->whereNotNull('currency')
                ->distinct()
                ->orderBy('currency')
                ->pluck('currency')
                ->filter()
                ->values(),
            'plans' => SubscriptionPlan::query()
                ->orderBy('title')
                ->get(['id', 'title']),
            'frequencies' => SubscriptionPlan::query()
                ->select('frequency')
                ->whereNotNull('frequency')
                ->distinct()
                ->orderBy('frequency')
                ->pluck('frequency')
                ->filter()
                ->values(),
            'statuses' => UserSubscription::query()
                ->select('status')
                ->whereNotNull('status')
                ->distinct()
                ->orderBy('status')
                ->pluck('status')
                ->filter()
                ->values(),
            'paymentStatuses' => UserSubscription::query()
                ->select('payment_status')
                ->whereNotNull('payment_status')
                ->distinct()
                ->orderBy('payment_status')
                ->pluck('payment_status')
                ->filter()
                ->values(),
        ]);
    }

    public function subscribers(Request $request)
    {
        $filters = [
            'keyword' => trim((string) $request->input('keyword', $request->input('search', ''))),
            'status' => trim((string) $request->input('status', '')),
            'payment_status' => trim((string) $request->input('payment_status', '')),
            'currency' => trim((string) $request->input('currency', '')),
            'plan_id' => trim((string) $request->input('plan_id', '')),
            'frequency' => trim((string) $request->input('frequency', '')),
            'gateway' => trim((string) $request->input('gateway', '')),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
        ];

        $sort = (string) $request->input('sort', 'created_at');
        $direction = strtolower((string) $request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['created_at', 'start_date', 'end_date', 'status', 'payment_status', 'amount', 'user_name', 'plan_name'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'created_at';

        $query = UserSubscription::query()
            ->with(['user', 'subscriptionPlan'])
            ->when($filters['keyword'] !== '', function ($builder) use ($filters) {
                $keyword = $filters['keyword'];
                $builder->where(function ($query) use ($keyword) {
                    $query->where('razorpay_subscription_id', 'like', "%{$keyword}%")
                        ->orWhere('razorpay_order_id', 'like', "%{$keyword}%")
                        ->orWhere('payment_link_id', 'like', "%{$keyword}%")
                        ->orWhereHas('user', function ($userQuery) use ($keyword) {
                            $userQuery->where('name', 'like', "%{$keyword}%")
                                ->orWhere('email', 'like', "%{$keyword}%")
                                ->orWhere('mobile', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('subscriptionPlan', function ($planQuery) use ($keyword) {
                            $planQuery->where('title', 'like', "%{$keyword}%")
                                ->orWhere('frequency', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($filters['status'] !== '', fn ($builder) => $builder->where('status', $filters['status']))
            ->when($filters['payment_status'] !== '', fn ($builder) => $builder->where('payment_status', $filters['payment_status']))
            ->when($filters['currency'] !== '', function ($builder) use ($filters) {
                $builder->where(function ($query) use ($filters) {
                    $query->where('currency', $filters['currency'])
                        ->orWhereHas('subscriptionPlan', fn ($planQuery) => $planQuery->where('currency', $filters['currency']));
                });
            })
            ->when($filters['plan_id'] !== '', fn ($builder) => $builder->where('subscription_plan_id', $filters['plan_id']))
            ->when($filters['frequency'] !== '', fn ($builder) => $builder->whereHas('subscriptionPlan', fn ($planQuery) => $planQuery->where('frequency', $filters['frequency'])))
            ->when($filters['gateway'] !== '', function ($builder) use ($filters) {
                if ($filters['gateway'] === 'razorpay') {
                    $builder->where(function ($query) {
                        $query->whereNotNull('razorpay_subscription_id')
                            ->orWhereNotNull('razorpay_order_id');
                    });
                } elseif ($filters['gateway'] === 'manual') {
                    $builder->whereNull('razorpay_subscription_id')
                        ->whereNull('razorpay_order_id');
                }
            })
            ->when($filters['date_from'] !== '', fn ($builder) => $builder->whereDate('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'] !== '', fn ($builder) => $builder->whereDate('created_at', '<=', $filters['date_to']))
            ->when($sort === 'user_name', function ($builder) use ($direction) {
                $builder->orderBy(
                    DB::table('users')
                        ->select('name')
                        ->whereColumn('users.id', 'user_subscriptions.user_id')
                        ->limit(1),
                    $direction
                );
            })
            ->when($sort === 'plan_name', function ($builder) use ($direction) {
                $builder->orderBy(
                    SubscriptionPlan::select('title')
                        ->whereColumn('subscription_plans.id', 'user_subscriptions.subscription_plan_id')
                        ->limit(1),
                    $direction
                );
            })
            ->when(! in_array($sort, ['user_name', 'plan_name'], true), fn ($builder) => $builder->orderBy($sort, $direction));

        $metrics = [
            'total_subscribers' => (clone $query)->count(),
            'active_subscribers' => (clone $query)->where('status', 'active')->count(),
            'trial_subscribers' => (clone $query)->where('status', 'active')->where('payment_status', 'pending')->count(),
            'cancelled_subscribers' => (clone $query)->where('status', 'cancelled')->count(),
        ];

        $subscriptions = $query
            ->paginate((int) $request->input('per_page', paginateLimit()))
            ->withQueryString();

        $subscriptions->getCollection()->transform(function (UserSubscription $subscription) {
            return [
                'id' => $subscription->id,
                'user' => $subscription->user?->name ?? 'Unknown user',
                'email' => $subscription->user?->email ?? 'N/A',
                'mobile' => $subscription->user?->mobile ?? 'N/A',
                'profile_photo_url' => $subscription->user?->profile_photo_url,
                'status' => ucfirst((string) ($subscription->status ?: 'pending')),
                'payment_status' => ucfirst((string) ($subscription->payment_status ?: 'pending')),
                'plan_name' => $subscription->subscriptionPlan?->title ?? 'N/A',
                'plan_id' => $subscription->subscription_plan_id,
                'subscribed_on' => $subscription->start_date?->format('M d, Y') ?? 'N/A',
                'subscription_id' => $subscription->razorpay_subscription_id ?? $subscription->id,
                'pricing_plan' => $subscription->formatted_price ?? 'N/A',
                'next_payment' => $subscription->end_date?->format('M d, Y') ?? 'N/A',
                'gateway' => $this->getGatewayName($subscription),
                'gateway_label' => $this->getGatewayName($subscription),
                'currency' => $subscription->currency ?: ($subscription->subscriptionPlan?->currency ?? 'INR'),
                'amount' => (float) ($subscription->amount ?? 0),
                'created_label' => optional($subscription->created_at)->format('M d, Y h:i A'),
            ];
        });

        return Inertia::render('SAdmin/finance/Subscribers', [
            'subscribers' => $subscriptions,
            'metrics' => $metrics,
            'filters' => $filters,
            'plans' => SubscriptionPlan::query()
                ->orderBy('title')
                ->get(['id', 'title']),
            'frequencies' => SubscriptionPlan::query()
                ->select('frequency')
                ->whereNotNull('frequency')
                ->distinct()
                ->orderBy('frequency')
                ->pluck('frequency')
                ->filter()
                ->values(),
            'currencies' => UserSubscription::query()
                ->select('currency')
                ->whereNotNull('currency')
                ->distinct()
                ->orderBy('currency')
                ->pluck('currency')
                ->filter()
                ->values(),
            'statuses' => UserSubscription::query()
                ->select('status')
                ->whereNotNull('status')
                ->distinct()
                ->orderBy('status')
                ->pluck('status')
                ->filter()
                ->values(),
            'paymentStatuses' => UserSubscription::query()
                ->select('payment_status')
                ->whereNotNull('payment_status')
                ->distinct()
                ->orderBy('payment_status')
                ->pluck('payment_status')
                ->filter()
                ->values(),
        ]);
    }

    private function getGatewayName($subscription)
    {
        // Determine gateway based on available IDs
        if ($subscription->razorpay_subscription_id) {
            return 'Razorpay';
        }

        // Add logic for other gateways if needed
        return 'N/A';
    }

    public function payment()
    {
        return Inertia::render('SAdmin/finance/Payment');
    }

    public function invoice()
    {
        return Inertia::render('SAdmin/finance/Invoice');
    }
}
