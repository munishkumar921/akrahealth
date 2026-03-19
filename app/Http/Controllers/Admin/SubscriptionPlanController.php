<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubscriptionPlanRequest;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionPlanService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionPlanController extends Controller
{
    protected $subscriptionPlanService;

    public function __construct(SubscriptionPlanService $subscriptionPlanService)
    {
        $this->subscriptionPlanService = $subscriptionPlanService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'plan_for', 'active']);
        $subscriptionPlans = $this->subscriptionPlanService->getPlans($filters);

        return Inertia::render('Admin/Subscription/Index', [
            'subscriptionPlans' => $subscriptionPlans,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Subscription/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SubscriptionPlanRequest $request)
    {
        $this->subscriptionPlanService->upsert($request->validated());

        return redirect()->route('admin.subscription-plans.index')->with('success', 'Subscription plan created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SubscriptionPlan $id)
    {
        return Inertia::render('Admin/Subscription/Show', [
            'subscriptionPlan' => $id,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubscriptionPlan $id)
    {
        return Inertia::render('Admin/Subscription/Edit', [
            'subscriptionPlan' => $id,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SubscriptionPlanRequest $request, SubscriptionPlan $id)
    {
        $this->subscriptionPlanService->upsert($id, $request->validated());

        return redirect()->route('admin.subscription-plans.index')->with('success', 'Subscription plan updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubscriptionPlan $id)
    {
        $this->subscriptionPlanService->delete($id);

        return redirect()->route('admin.subscription-plans.index')->with('success', 'Subscription plan deleted successfully.');
    }

    /**
     * Toggle active status of a subscription plan
     */
    public function toggleActive(SubscriptionPlan $id)
    {
        $updatedPlan = $this->subscriptionPlanService->toggleActive($id);

        return response()->json([
            'message' => 'Status updated successfully.',
            'active' => $updatedPlan->active,
        ]);
    }
}
