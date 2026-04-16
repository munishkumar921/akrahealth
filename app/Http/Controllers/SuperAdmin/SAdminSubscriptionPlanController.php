<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\SubscriptionPlan;
use App\Http\Requests\SubscriptionPlanRequest;
use App\Services\SubscriptionPlanService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class SAdminSubscriptionPlanController extends Controller
{
    protected $subscriptionPlanService;

    /**
     * __construct
     *
     * @param  mixed  $userService
     * @return void
     */
    public function __construct(SubscriptionPlanService $subscriptionPlanService)
    {
        // hasAccess('manage admins');
        $this->subscriptionPlanService = $subscriptionPlanService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $subscriptionPlans = $this->subscriptionPlanService->list($request);
        $countries = Country::select('id', 'name', 'currency', 'currency_symbol')->get();
        $permissions = Permission::all()->pluck('name', 'id')->toArray();
        $filters = [
            'keyword' => trim((string) $request->input('keyword', $request->input('search', ''))),
            'plan_for' => trim((string) $request->input('plan_for', '')),
            'currency' => trim((string) $request->input('currency', '')),
            'country_id' => trim((string) $request->input('country_id', '')),
            'frequency' => trim((string) $request->input('frequency', '')),
            'status' => $request->input('status', ''),
        ];

        $metricsQuery = SubscriptionPlan::query()
            ->when($filters['keyword'] !== '', function ($query) use ($filters) {
                $keyword = $filters['keyword'];
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('title', 'like', "%{$keyword}%")
                        ->orWhere('plan_for', 'like', "%{$keyword}%")
                        ->orWhere('price', 'like', "%{$keyword}%")
                        ->orWhere('currency', 'like', "%{$keyword}%")
                        ->orWhere('frequency', 'like', "%{$keyword}%");
                });
            })
            ->when($filters['plan_for'] !== '', fn ($query) => $query->where('plan_for', $filters['plan_for']))
            ->when($filters['currency'] !== '', fn ($query) => $query->where('currency', $filters['currency']))
            ->when($filters['country_id'] !== '', fn ($query) => $query->where('country_id', $filters['country_id']))
            ->when($filters['frequency'] !== '', fn ($query) => $query->where('frequency', $filters['frequency']))
            ->when($filters['status'] !== '' && $filters['status'] !== null, fn ($query) => $query->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN)));

        $metrics = [
            'total_plans' => (clone $metricsQuery)->count(),
            'active_plans' => (clone $metricsQuery)->where('status', true)->count(),
            'inactive_plans' => (clone $metricsQuery)->where('status', false)->count(),
            'monthly_plans' => (clone $metricsQuery)->where('frequency', 'monthly')->count(),
        ];

        return Inertia::render('SAdmin/finance/SubscriptionPlan', [
            'subscriptionPlans' => $subscriptionPlans,
            'filters' => $filters,
            'countries' => $countries,
            'permissions' => $permissions,
            'metrics' => $metrics,
            'planForOptions' => SubscriptionPlan::query()
                ->select('plan_for')
                ->whereNotNull('plan_for')
                ->distinct()
                ->orderBy('plan_for')
                ->pluck('plan_for')
                ->filter()
                ->values(),
            'currencyOptions' => SubscriptionPlan::query()
                ->select('currency')
                ->whereNotNull('currency')
                ->distinct()
                ->orderBy('currency')
                ->pluck('currency')
                ->filter()
                ->values(),
            'frequencyOptions' => SubscriptionPlan::query()
                ->select('frequency')
                ->whereNotNull('frequency')
                ->distinct()
                ->orderBy('frequency')
                ->pluck('frequency')
                ->filter()
                ->values(),
        ]);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SubscriptionPlanRequest $request)
    {
        $this->subscriptionPlanService->upsert($request->all());

        return back()->with('success', 'The Plan has been saved successfully.');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SubscriptionPlanRequest $request, string $id)
    {
        $data = $request->all();
        $data['id'] = $id; // Ensure the ID is included for update
        $this->subscriptionPlanService->upsert($data);

        return back()->with('success', 'The Plan has been updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $subscriptionPlan = SubscriptionPlan::findOrFail($id);
        $this->subscriptionPlanService->delete($subscriptionPlan);

        return back()->with('success', 'The Plan has been deleted successfully.');
    }

    public function statusUpdate($id)
    {
        $updatedPlan = $this->subscriptionPlanService->status($id);
        if ($updatedPlan) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }
}
