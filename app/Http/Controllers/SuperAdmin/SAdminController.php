<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Roles;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SAdminController extends Controller
{
    protected $userService;

    /**
     * __construct
     *
     * @param  mixed  $userService
     * @return void
     */
    public function __construct(UserService $userService)
    {
        // hasAccess('manage admins');
        $this->userService = $userService;
        $this->userService->role = 'SuperAdmin';
    }

    public function dashboard()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $startOfYear = $now->copy()->startOfYear();

        $newUsersCurrentMonth = User::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        $newUsersPastMonth = User::whereBetween('created_at', [
            $startOfMonth->copy()->subMonth()->startOfMonth(),
            $startOfMonth->copy()->subMonth()->endOfMonth(),
        ])->count();

        $newSubscribersCurrentMonth = UserSubscription::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        $newSubscribersPastMonth = UserSubscription::whereBetween('created_at', [
            $startOfMonth->copy()->subMonth()->startOfMonth(),
            $startOfMonth->copy()->subMonth()->endOfMonth(),
        ])->count();

        $incomeCurrentMonth = (float) UserSubscription::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $incomePastMonth = (float) UserSubscription::where('payment_status', 'paid')
            ->whereBetween('created_at', [
                $startOfMonth->copy()->subMonth()->startOfMonth(),
                $startOfMonth->copy()->subMonth()->endOfMonth(),
            ])
            ->sum('amount');

        $transactionsCurrentMonth = UserSubscription::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        $transactionsPastMonth = UserSubscription::where('payment_status', 'paid')
            ->whereBetween('created_at', [
                $startOfMonth->copy()->subMonth()->startOfMonth(),
                $startOfMonth->copy()->subMonth()->endOfMonth(),
            ])
            ->count();

        $totalIncomeCurrentYear = (float) UserSubscription::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startOfYear, $endOfMonth])
            ->sum('amount');

        $totalNewUsersCurrentYear = User::whereBetween('created_at', [$startOfYear, $endOfMonth])->count();
        $activeSubscribers = UserSubscription::where('status', 'active')->count();
        $plansLive = SubscriptionPlan::where('status', true)->count();
        $trialEndingSoon = UserSubscription::whereDate('end_date', '>=', $now->toDateString())
            ->whereDate('end_date', '<=', $now->copy()->addDays(14)->toDateString())
            ->count();

        $latestRegistrations = User::with('roles')
            ->latest()
            ->take(5)
            ->get()
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name ?: trim(($user->first_name ?? '').' '.($user->last_name ?? '')),
                    'email' => $user->email,
                    'group' => strtolower($user->roles->first()?->name ?? 'user'),
                    'status' => $user->is_active ? 'active' : 'inactive',
                    'created_at' => optional($user->created_at)->format('M d, Y h:i A'),
                    'profile_photo_url' => $user->profile_photo_url,
                ];
            });

        $latestTransactions = UserSubscription::with(['user', 'subscriptionPlan'])
            ->where('payment_status', 'paid')
            ->latest()
            ->take(5)
            ->get()
            ->map(function (UserSubscription $subscription) {
                return [
                    'id' => $subscription->id,
                    'name' => $subscription->user?->name ?: trim(($subscription->user?->first_name ?? '').' '.($subscription->user?->last_name ?? '')),
                    'email' => $subscription->user?->email,
                    'status' => ucfirst($subscription->payment_status ?: $subscription->status ?: 'pending'),
                    'price' => number_format((float) ($subscription->amount ?? 0), 2, '.', ''),
                    'currency' => $subscription->currency ?: $subscription->subscriptionPlan?->currency ?: 'USD',
                    'gateway' => $subscription->razorpay_subscription_id ? 'Razorpay' : 'N/A',
                    'created_at' => optional($subscription->created_at)->format('M d, Y h:i A'),
                    'profile_photo_url' => $subscription->user?->profile_photo_url,
                    'plan_name' => $subscription->subscriptionPlan?->title,
                ];
            });

        $monthlyUsers = collect(range(1, $endOfMonth->day))->map(function ($day) use ($now) {
            $date = $now->copy()->startOfMonth()->day($day);

            return User::whereDate('created_at', $date->toDateString())->count();
        })->values();

        $months = collect(range(1, 12))->map(function ($month) use ($now) {
            return Carbon::create($now->year, $month, 1)->format('M');
        })->values();

        $yearlyUsers = collect(range(1, 12))->map(function ($month) use ($now) {
            return User::whereYear('created_at', $now->year)
                ->whereMonth('created_at', $month)
                ->count();
        })->values();

        $yearlyIncome = collect(range(1, 12))->map(function ($month) use ($now) {
            return (float) UserSubscription::where('payment_status', 'paid')
                ->whereYear('created_at', $now->year)
                ->whereMonth('created_at', $month)
                ->sum('amount');
        })->values();

        $topPlans = UserSubscription::query()
            ->select('subscription_plan_id', DB::raw('COUNT(*) as subscriptions_count'), DB::raw('SUM(amount) as revenue'))
            ->with('subscriptionPlan')
            ->groupBy('subscription_plan_id')
            ->orderByDesc('subscriptions_count')
            ->take(4)
            ->get()
            ->map(function (UserSubscription $subscription) {
                return [
                    'plan_name' => $subscription->subscriptionPlan?->title ?? 'Unknown Plan',
                    'subscriptions_count' => (int) $subscription->subscriptions_count,
                    'revenue' => (float) ($subscription->revenue ?? 0),
                    'currency' => $subscription->subscriptionPlan?->currency ?? 'USD',
                ];
            });

        return Inertia::render('SAdmin/Dashboard', [
            'metrics' => [
                'new_users_current_month' => $newUsersCurrentMonth,
                'new_users_past_month' => $newUsersPastMonth,
                'new_subscribers_current_month' => $newSubscribersCurrentMonth,
                'new_subscribers_past_month' => $newSubscribersPastMonth,
                'income_current_month' => $incomeCurrentMonth,
                'income_past_month' => $incomePastMonth,
                'transactions_current_month' => $transactionsCurrentMonth,
                'transactions_past_month' => $transactionsPastMonth,
                'total_income_current_year' => $totalIncomeCurrentYear,
                'total_new_users_current_year' => $totalNewUsersCurrentYear,
                'active_subscribers' => $activeSubscribers,
                'plans_live' => $plansLive,
                'trial_ending_soon' => $trialEndingSoon,
            ],
            'charts' => [
                'monthly_new_users' => $monthlyUsers,
                'yearly_users' => $yearlyUsers,
                'yearly_income' => $yearlyIncome,
                'month_labels' => $months,
                'daily_labels' => collect(range(1, $endOfMonth->day))->values(),
            ],
            'latestRegistrations' => $latestRegistrations,
            'latestTransactions' => $latestTransactions,
            'topPlans' => $topPlans,
            'currencySymbol' => '$',
        ]);
    }

    public function logViewer()
    {
        return Inertia::render('SAdmin/Logs/Index', [
            'logViewerUrl' => route('log-viewer.index'),
        ]);
    }

    /**
     * Roles & Permissions Management Page
     *
     * @return \Inertia\Response
     */
    public function rolespermission(Request $request)
    {
        $keyword = trim((string) $request->get('keyword', ''));
        $status = $request->get('status', 'all');
        $perPage = (int) $request->get('per_page', 10);
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['name', 'guard_name', 'created_at', 'is_active'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $query = Roles::query()
            ->when($keyword !== '', function ($builder) use ($keyword) {
                $builder->where(function ($inner) use ($keyword) {
                    $inner->where('name', 'like', "%{$keyword}%")
                        ->orWhere('guard_name', 'like', "%{$keyword}%");
                });
            })
            ->when($status === 'active', fn ($builder) => $builder->where('is_active', true))
            ->when($status === 'inactive', fn ($builder) => $builder->where('is_active', false))
            ->orderBy($sort, $direction);

        $roles = (clone $query)
            ->paginate($perPage)
            ->through(function (Roles $role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'guard_name' => $role->guard_name,
                    'is_active' => (bool) $role->is_active,
                    'created_at' => optional($role->created_at)->format('M d, Y h:i A'),
                    'created_at_raw' => optional($role->created_at)?->toISOString(),
                ];
            })
            ->withQueryString();

        $metricsQuery = Roles::query();

        return Inertia::render('SAdmin/RolesPermission', [
            'roles' => $roles,
            'filters' => [
                'keyword' => $keyword,
                'status' => $status,
                'per_page' => $perPage,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'metrics' => [
                'total_roles' => (clone $metricsQuery)->count(),
                'active_roles' => (clone $metricsQuery)->where('is_active', true)->count(),
                'inactive_roles' => (clone $metricsQuery)->where('is_active', false)->count(),
                'latest_created_at' => optional((clone $metricsQuery)->latest('created_at')->first()?->created_at)->format('M d, Y h:i A'),
            ],
        ]);
    }

    /**
     * API: List all roles
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiRolesList(Request $request)
    {
        try {
            $keyword = $request->get('keyword', '');

            $query = Roles::query();

            if ($keyword) {
                $query->where('name', 'LIKE', '%'.$keyword.'%');
            }

            $roles = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $roles,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch roles: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Store a new role
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiRolesStore(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:roles,name,'.$request->id,
                'guard_name' => 'nullable|string|max:255',
            ]);

            $role = \Spatie\Permission\Models\Role::updateOrCreate(
                ['id' => $request->id],
                [
                    'name' => $request->name,
                    'guard_name' => $request->guard_name ?? 'web',
                    'is_active' => $request->has('is_status') ? (bool) $request->is_status : true,
                ]
            );

            app(\App\Services\InAppNotificationService::class)->notifySuperAdmins(
                app(\App\Services\InAppNotificationService::class)->buildPayload(
                    $request->id ? 'Role updated' : 'Role created',
                    'Role '.$role->name.' was '.($request->id ? 'updated' : 'created').' in Roles & Permissions.',
                    'privileged_role_changed',
                    [
                        'meta' => [
                            'role' => $role->name,
                            'guard' => $role->guard_name,
                            'is_active' => $role->is_active,
                        ],
                    ]
                )
            );

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully.',
                'data' => $role,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create role: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Delete a role
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiRolesDestroy($id)
    {
        try {
            $role = \Spatie\Permission\Models\Role::findOrFail($id);
            $roleName = $role->name;
            $role->delete();

            app(\App\Services\InAppNotificationService::class)->notifySuperAdmins(
                app(\App\Services\InAppNotificationService::class)->buildPayload(
                    'Role deleted',
                    'Role '.$roleName.' was deleted from Roles & Permissions.',
                    'privileged_role_changed',
                    [
                        'meta' => [
                            'role' => $roleName,
                        ],
                    ]
                )
            );

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Toggle role active status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiRolesToggle(Request $request, int $id)
    {
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        try {
            $role = Roles::findOrFail($id);
            $role->is_active = $request->is_active;
            $role->save();

            return response()->json([
                'success' => true,
                'message' => 'Role status updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role status: '.$e->getMessage(),
            ], 500);
        }
    }
}
