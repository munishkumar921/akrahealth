<?php

namespace App\Services;

use App\Models\SubscriptionPlan;

class SubscriptionPlanService
{
    /**
     * list
     *
     * @param  mixed  $request
     * @return void
     */
    public function list($request)
    {
        $keyword = trim((string) ($request->keyword ?? $request->search ?? ''));
        $planFor = trim((string) ($request->plan_for ?? ''));
        $currency = trim((string) ($request->currency ?? ''));
        $countryId = trim((string) ($request->country_id ?? ''));
        $frequency = trim((string) ($request->frequency ?? ''));
        $status = $request->status;
        $sort = (string) ($request->sort ?? 'created_at');
        $direction = strtolower((string) ($request->direction ?? 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['plan_for', 'title', 'price', 'currency', 'frequency', 'status', 'created_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'created_at';

        return SubscriptionPlan::query()
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('title', 'like', "%{$keyword}%")
                        ->orWhere('plan_for', 'like', "%{$keyword}%")
                        ->orWhere('price', 'like', "%{$keyword}%")
                        ->orWhere('currency', 'like', "%{$keyword}%")
                        ->orWhere('frequency', 'like', "%{$keyword}%");
                });
            })
            ->when($planFor !== '', fn ($query) => $query->where('plan_for', $planFor))
            ->when($currency !== '', fn ($query) => $query->where('currency', $currency))
            ->when($countryId !== '', fn ($query) => $query->where('country_id', $countryId))
            ->when($frequency !== '', fn ($query) => $query->where('frequency', $frequency))
            ->when($status !== null && $status !== '', fn ($query) => $query->where('status', filter_var($status, FILTER_VALIDATE_BOOLEAN)))
            ->orderBy($sort, $direction)
            ->paginate(request('per_page', 20))->withQueryString();
    }

    /**
     * upsert
     *
     * @param  mixed  $planOrData
     * @param  mixed  $data
     * @return void
     */
    public function upsert($input)
    {
        return SubscriptionPlan::updateOrCreate(
            [
                'id' => $input['id'] ?? null,
            ],
            [
                'plan_for' => $input['plan_for'],
                'title' => $input['title'],
                'price' => $input['price'],
                'frequency' => $input['frequency'],
                'currency' => $input['currency'],
                'status' => $input['status'],
                'features' => $input['features'] ?? null,
                'permissions' => $input['permissions'] ?? null,
                'country_id' => $input['country_id'] ?? null,
            ]
        );
    }

    /**
     * getPeriodInterval
     *
     * @param  mixed  $billing_cycle
     * @return void
     */
    public function getPeriodInterval($billing_cycle)
    {
        switch (strtolower($billing_cycle)) {
            case 'monthly':
                $payload['period'] = 'monthly';
                $payload['interval'] = 1;
                break;

            case 'yearly':
                $payload['period'] = 'yearly';
                $payload['interval'] = 1;
                break;

            default:
                $payload['period'] = 'monthly';
                $payload['interval'] = 1;
        }

        return $payload;
    }

    public function status($id)
    {
        $subscriptionPlan = SubscriptionPlan::find($id);
        if ($subscriptionPlan) {
            $subscriptionPlan->status = ! $subscriptionPlan->status;
            $subscriptionPlan->save();

            return $subscriptionPlan;
        }

        return null;
    }

    public function delete($subscriptionPlan)
    {
        $subscriptionPlan->delete();
    }

    /**
     * Get plans based on filters
     *
     * @param  array  $filters
     * @return mixed
     */
    public function getPlans($filters = [])
    {
        $query = SubscriptionPlan::query();

        if (isset($filters['plan_for'])) {
            $query->where('plan_for', $filters['plan_for']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->get();
    }
}
