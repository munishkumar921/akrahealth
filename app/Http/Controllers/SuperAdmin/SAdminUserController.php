<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\Country;
use App\Models\Hospital;
use App\Models\SubscriptionPlan;
use App\Models\State;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SAdminUserController extends Controller
{
    public function userdashboard()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();

        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $usersToday = User::whereDate('created_at', $now->toDateString())->count();
        $verifiedUsers = User::where('is_email_verified', true)->count();

        $roleBreakdown = [
            'admins' => User::role('Admin')->count(),
            'doctors' => User::role('Doctor')->count(),
            'patients' => User::role('Patient')->count(),
        ];

        $topCountries = Hospital::query()
            ->select('country', DB::raw('COUNT(*) as total'))
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->groupBy('country')
            ->orderByDesc('total')
            ->take(12)
            ->get()
            ->map(fn ($row) => [
                'country' => $row->country,
                'users' => (int) $row->total,
            ])
            ->values();

        $daysInMonth = $startOfMonth->daysInMonth;
        $dailyLabels = collect(range(1, $daysInMonth))->values();
        $monthlyNewUsers = collect(range(1, $daysInMonth))->map(function ($day) use ($startOfMonth) {
            $date = $startOfMonth->copy()->day($day);

            return User::whereDate('created_at', $date->toDateString())->count();
        })->values();

        $monthLabels = collect(range(1, 12))->map(function ($month) use ($now) {
            return Carbon::create($now->year, $month, 1)->format('M');
        })->values();

        $yearlyUsers = collect(range(1, 12))->map(function ($month) use ($now) {
            return User::whereYear('created_at', $now->year)
                ->whereMonth('created_at', $month)
                ->count();
        })->values();

        $latestUsers = User::with('roles')
            ->latest()
            ->take(6)
            ->get()
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name ?: trim(($user->first_name ?? '').' '.($user->last_name ?? '')),
                    'email' => $user->email,
                    'role' => strtolower($user->roles->first()?->name ?? 'user'),
                    'status' => $user->is_active ? 'active' : 'inactive',
                    'verified' => (bool) $user->is_email_verified,
                    'created_at' => optional($user->created_at)->format('M d, Y h:i A'),
                    'profile_photo_url' => $user->profile_photo_url,
                ];
            });

        return Inertia::render('SAdmin/user/Dashboard', [
            'metrics' => [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'users_today' => $usersToday,
                'verified_users' => $verifiedUsers,
            ],
            'roleBreakdown' => $roleBreakdown,
            'topCountries' => $topCountries,
            'charts' => [
                'daily_labels' => $dailyLabels,
                'monthly_new_users' => $monthlyNewUsers,
                'month_labels' => $monthLabels,
                'yearly_users' => $yearlyUsers,
            ],
            'latestUsers' => $latestUsers,
        ]);
    }

    public function userlist(Request $request)
    {
        $filters = [
            'keyword' => trim((string) $request->input('keyword', $request->input('search', ''))),
            'role' => trim((string) $request->input('role', '')),
            'status' => $request->input('status', ''),
            'verified' => $request->input('verified', ''),
            'country' => trim((string) $request->input('country', '')),
            'plan_id' => trim((string) $request->input('plan_id', '')),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
        ];

        $sort = $request->input('sort', 'created_at');
        $direction = strtolower((string) $request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['name', 'email', 'mobile', 'is_active', 'is_email_verified', 'created_at', 'country', 'plan_name'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'created_at';

        $query = User::query()
            ->with(['roles', 'hospital', 'subscriptionPlan', 'address'])
            ->when($filters['keyword'] !== '', function ($builder) use ($filters) {
                $keyword = $filters['keyword'];
                $builder->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', "%{$keyword}%")
                        ->orWhere('first_name', 'like', "%{$keyword}%")
                        ->orWhere('last_name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%")
                        ->orWhere('mobile', 'like', "%{$keyword}%")
                        ->orWhereHas('roles', function ($roleQuery) use ($keyword) {
                            $roleQuery->where('name', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('hospital', function ($hospitalQuery) use ($keyword) {
                            $hospitalQuery->where('name', 'like', "%{$keyword}%")
                                ->orWhere('country', 'like', "%{$keyword}%")
                                ->orWhere('city', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('address', function ($addressQuery) use ($keyword) {
                            $addressQuery->where('country', 'like', "%{$keyword}%")
                                ->orWhere('city', 'like', "%{$keyword}%")
                                ->orWhere('address_1', 'like', "%{$keyword}%")
                                ->orWhere('address_2', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($filters['role'] !== '', fn ($builder) => $builder->role($filters['role']))
            ->when($filters['status'] !== '', fn ($builder) => $builder->where('is_active', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN)))
            ->when($filters['verified'] !== '', fn ($builder) => $builder->where('is_email_verified', filter_var($filters['verified'], FILTER_VALIDATE_BOOLEAN)))
            ->when($filters['country'] !== '', function ($builder) use ($filters) {
                $builder->where(function ($query) use ($filters) {
                    $query->whereHas('hospital', fn ($hospitalQuery) => $hospitalQuery->where('country', $filters['country']))
                        ->orWhereHas('address', fn ($addressQuery) => $addressQuery->where('country', $filters['country']));
                });
            })
            ->when($filters['plan_id'] !== '', fn ($builder) => $builder->where('subscription_plan_id', $filters['plan_id']))
            ->when($filters['date_from'] !== '', fn ($builder) => $builder->whereDate('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'] !== '', fn ($builder) => $builder->whereDate('created_at', '<=', $filters['date_to']))
            ->when($sort === 'country', function ($builder) use ($direction) {
                $builder->orderBy(
                    Hospital::select('country')
                        ->whereColumn('user_id', 'users.id')
                        ->limit(1),
                    $direction
                );
            })
            ->when($sort === 'plan_name', function ($builder) use ($direction) {
                $builder->orderBy(
                    SubscriptionPlan::select('title')
                        ->whereColumn('subscription_plans.id', 'users.subscription_plan_id')
                        ->limit(1),
                    $direction
                );
            })
            ->when(!in_array($sort, ['country', 'plan_name'], true), fn ($builder) => $builder->orderBy($sort, $direction));

        $metrics = [
            'total_users' => (clone $query)->count(),
            'active_users' => (clone $query)->where('is_active', true)->count(),
            'inactive_users' => (clone $query)->where('is_active', false)->count(),
            'pending_verification' => (clone $query)->where('is_email_verified', false)->count(),
        ];

        $admins = $query->paginate((int) $request->input('per_page', paginateLimit()))->withQueryString();
        $admins->getCollection()->transform(function (User $user) {
            $hospital = $user->hospital;
            $address = $user->address;
            $name = $user->name ?: trim(($user->first_name ?? '').' '.($user->last_name ?? ''));

            return [
                'id' => $user->id,
                'name' => $name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'is_active' => (bool) $user->is_active,
                'is_email_verified' => (bool) $user->is_email_verified,
                'is_verified' => (bool) $user->is_email_verified,
                'status_label' => $user->is_active ? 'Active' : 'Inactive',
                'verified_label' => $user->is_email_verified ? 'Verified' : 'Pending',
                'created_at' => optional($user->created_at)->toDateTimeString(),
                'created_label' => optional($user->created_at)->format('M d, Y h:i A'),
                'profile_photo_url' => $user->profile_photo_url,
                'roles' => $user->roles->map(fn ($role) => ['name' => $role->name])->values(),
                'hospital_name' => $hospital?->name,
                'country' => $hospital?->country ?: $address?->country,
                'city' => $hospital?->city ?: $address?->city,
                'plan_name' => $user->subscriptionPlan?->title,
                'plan_id' => $user->subscription_plan_id,
            ];
        });

        return Inertia::render('SAdmin/user/UserList', [
            'admins' => $admins,
            'metrics' => $metrics,
            'states' => State::get(),
            'countries' => Country::get(),
            'roles' => Role::query()->orderBy('name')->get(['id', 'name']),
            'plans' => SubscriptionPlan::query()
                ->where('status', true)
                ->orderBy('title')
                ->get(['id', 'title']),
            'filters' => array_merge($filters, [
                'sort' => $sort,
                'direction' => $direction,
                'per_page' => (int) $request->input('per_page', paginateLimit()),
            ]),
        ]);
    }

    public function show(string $id)
    {
        $user = User::query()
            ->with(['roles', 'hospital', 'subscriptionPlan', 'address'])
            ->findOrFail($id);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name ?: trim(($user->first_name ?? '').' '.($user->last_name ?? '')),
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'is_active' => (bool) $user->is_active,
            'is_email_verified' => (bool) $user->is_email_verified,
            'created_at' => optional($user->created_at)->format('M d, Y h:i A'),
            'roles' => $user->roles->pluck('name')->values(),
            'hospital' => [
                'name' => $user->hospital?->name,
                'email' => $user->hospital?->email,
                'phone' => $user->hospital?->phone,
                'city' => $user->hospital?->city,
                'state' => $user->hospital?->state,
                'country' => $user->hospital?->country,
            ],
            'address' => [
                'address' => $user->address?->address_1,
                'address_2' => $user->address?->address_2,
                'city' => $user->address?->city,
                'state' => $user->address?->state,
                'country' => $user->address?->country,
            ],
            'subscription_plan' => $user->subscriptionPlan?->title,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$id],
            'mobile' => ['nullable', 'string', 'max:20'],
            'is_active' => ['required', 'boolean'],
            'is_email_verified' => ['required', 'boolean'],
        ]);

        $user = User::query()->findOrFail($id);
        $name = trim(($validated['first_name'] ?? '').' '.($validated['last_name'] ?? ''));

        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? null,
            'name' => $name,
            'email' => $validated['email'],
            'mobile' => $validated['mobile'] ?? null,
            'is_active' => (bool) $validated['is_active'],
            'is_email_verified' => (bool) $validated['is_email_verified'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
        ]);
    }

    public function destroy(string $id)
    {
        $user = User::query()->findOrFail($id);
        $roleNames = $user->roles()->pluck('name')->values()->all();
        $user->delete();

        if (in_array('SuperAdmin', $roleNames, true)) {
            app(\App\Services\InAppNotificationService::class)->notifySuperAdmins(
                app(\App\Services\InAppNotificationService::class)->buildPayload(
                    'Super Admin deleted',
                    ($user->name ?? 'A user').' with Super Admin access was deleted.',
                    'superadmin_deleted',
                    [
                        'related_model_type' => User::class,
                        'related_model_id' => $id,
                        'meta' => [
                            'email' => $user->email,
                        ],
                    ]
                )
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ]);
    }

    public function toggleStatus(Request $request, string $id)
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $user = User::query()->findOrFail($id);
        $user->update([
            'is_active' => (bool) $validated['is_active'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully.',
        ]);
    }

    public function toggleVerification(Request $request, string $id)
    {
        $validated = $request->validate([
            'is_email_verified' => ['required', 'boolean'],
        ]);

        $user = User::query()->findOrFail($id);
        $user->update([
            'is_email_verified' => (bool) $validated['is_email_verified'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User verification updated successfully.',
        ]);
    }

    public function useractivitymonitoring(Request $request)
    {
        $filters = [
            'keyword' => trim((string) $request->input('keyword', $request->input('search', ''))),
            'role' => trim((string) $request->input('role', '')),
            'module' => trim((string) $request->input('module', '')),
            'action' => trim((string) $request->input('action', '')),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
        ];

        $sort = $request->input('sort', 'created_at');
        $direction = strtolower((string) $request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['created_at', 'module', 'action', 'ip_address'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'created_at';

        $query = Audit::query()
            ->with(['user', 'admin'])
            ->when($filters['keyword'] !== '', function ($builder) use ($filters) {
                $keyword = $filters['keyword'];
                $builder->where(function ($query) use ($keyword) {
                    $query->where('description', 'like', "%{$keyword}%")
                        ->orWhere('module', 'like', "%{$keyword}%")
                        ->orWhere('action', 'like', "%{$keyword}%")
                        ->orWhere('ip_address', 'like', "%{$keyword}%")
                        ->orWhereHas('user', function ($userQuery) use ($keyword) {
                            $userQuery->where('name', 'like', "%{$keyword}%")
                                ->orWhere('email', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('admin', function ($adminQuery) use ($keyword) {
                            $adminQuery->where('name', 'like', "%{$keyword}%")
                                ->orWhere('email', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($filters['role'] !== '', fn ($builder) => $builder->where('user_type', $filters['role']))
            ->when($filters['module'] !== '', fn ($builder) => $builder->where('module', $filters['module']))
            ->when($filters['action'] !== '', fn ($builder) => $builder->where('action', $filters['action']))
            ->when($filters['date_from'] !== '', fn ($builder) => $builder->whereDate('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'] !== '', fn ($builder) => $builder->whereDate('created_at', '<=', $filters['date_to']))
            ->orderBy($sort, $direction);

        $metrics = [
            'total_logs' => (clone $query)->count(),
            'unique_actors' => (clone $query)->distinct('admin_id')->count('admin_id'),
            'today_logs' => (clone $query)->whereDate('created_at', now()->toDateString())->count(),
            'critical_actions' => (clone $query)->whereIn('action', ['delete', 'cancelled', 'rejected'])->count(),
        ];

        $auditLogs = $query
            ->paginate((int) $request->input('per_page', paginateLimit()))
            ->withQueryString();
        $auditLogs->getCollection()->transform(function (Audit $log) {
            $actor = $log->admin ?: $log->user;
            $role = $log->user_type ?: ($actor?->getRoleNames()?->first() ?? 'System');

            return [
                'id' => $log->id,
                'actor_name' => $actor?->name ?: 'System',
                'actor_email' => $actor?->email,
                'profile_photo_url' => $actor?->profile_photo_url,
                'role' => $role,
                'module' => $log->module,
                'module_label' => $log->module_label,
                'action' => $log->action,
                'action_label' => ucfirst((string) $log->action),
                'description' => $log->description,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'user_agent_short' => $this->shortUserAgent($log->user_agent),
                'created_at' => $log->created_at?->toDateTimeString(),
                'created_label' => $log->created_at?->format('M d, Y h:i A'),
                'created_human' => $log->created_at?->diffForHumans(),
            ];
        });

        return Inertia::render('SAdmin/user/UserActivityMonitoring', [
            'activities' => $auditLogs,
            'metrics' => $metrics,
            'roles' => collect(Role::query()->orderBy('name')->get(['id', 'name']))
                ->push((object) ['id' => 'system', 'name' => 'System']),
            'modules' => Audit::query()->select('module')->distinct()->pluck('module')->filter()->values(),
            'actions' => Audit::query()->select('action')->distinct()->pluck('action')->filter()->values(),
            'filters' => array_merge($filters, [
                'sort' => $sort,
                'direction' => $direction,
                'per_page' => (int) $request->input('per_page', paginateLimit()),
            ]),
        ]);
    }

    protected function shortUserAgent(?string $userAgent): string
    {
        if (! $userAgent) {
            return 'Unknown device';
        }

        $device = str_contains($userAgent, 'Mobile') || str_contains($userAgent, 'Android') || str_contains($userAgent, 'iPhone')
            ? 'Mobile'
            : 'Desktop';

        $browser = 'Browser';
        if (str_contains($userAgent, 'Edg/')) {
            $browser = 'Edge';
        } elseif (str_contains($userAgent, 'Chrome/')) {
            $browser = 'Chrome';
        } elseif (str_contains($userAgent, 'Firefox/')) {
            $browser = 'Firefox';
        } elseif (str_contains($userAgent, 'Safari/') && ! str_contains($userAgent, 'Chrome/')) {
            $browser = 'Safari';
        }

        $platform = 'Unknown OS';
        if (str_contains($userAgent, 'Windows')) {
            $platform = 'Windows';
        } elseif (str_contains($userAgent, 'Mac OS X') || str_contains($userAgent, 'Macintosh')) {
            $platform = 'macOS';
        } elseif (str_contains($userAgent, 'Android')) {
            $platform = 'Android';
        } elseif (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            $platform = 'iOS';
        } elseif (str_contains($userAgent, 'Linux')) {
            $platform = 'Linux';
        }

        return "{$browser} on {$platform} ({$device})";
    }
}
