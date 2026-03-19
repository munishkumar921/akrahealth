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
        return SubscriptionPlan::query()
            ->where('status', true)
            ->when(
                $request->search,
                fn ($q) => $q->where('title', 'like', "%{$request->search}%")
                    ->orWhere('price', 'like', "%{$request->search}%")
            )
            ->orderBy('created_at', 'desc')
            ->paginate(request('per_page', paginateLimit()))->withQueryString();
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
