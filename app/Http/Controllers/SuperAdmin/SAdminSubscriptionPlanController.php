<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
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
        $subscriptionPlans = $this->subscriptionPlanService->list(request());
        $request = request();
        $keyword = $request->get('keyword') ?? '';
        $countries = \App\Models\Country::select('id', 'name', 'currency', 'currency_symbol')->get();
        $permissions = Permission::all()->pluck('name', 'id')->toArray();

        return Inertia::render('SAdmin/finance/SubscriptionPlan', ['subscriptionPlans' => $subscriptionPlans, 'keyword' => $keyword, 'countries' => $countries, 'permissions' => $permissions]);

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
        $this->subscriptionPlanService->upsert($request->all());

        return back()->with('success', 'The Plan has been updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function statusUpdate($id)
    {
        $updatedPlan = $this->subscriptionPlanService->status($id);
        if ($updatedPlan) {
            return back()->with('success', 'The Plan status has been updated successfully.');
        }

        return back()->with('error', 'Failed to update plan status.');
    }
}
