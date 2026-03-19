<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Pagination\LengthAwarePaginator;

class SubscriberService
{
    /**
     * Get all subscribers with their active subscriptions
     */
    public function getAllSubscribers(int $perPage = 15): LengthAwarePaginator
    {
        return User::with(['userSubscriptions' => function ($query) {
            $query->with('subscriptionPlan')->latest();
        }])
            ->whereHas('userSubscriptions')
            ->paginate(request('per_page', paginateLimit()))->withQueryString();
    }

    /**
     * Get active subscribers
     */
    public function getActiveSubscribers(int $perPage = 15): LengthAwarePaginator
    {
        return User::with(['userSubscriptions' => function ($query) {
            $query->with('subscriptionPlan')
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>', now());
                });
        }])
            ->whereHas('userSubscriptions', function ($query) {
                $query->where('status', 'active')
                    ->where(function ($q) {
                        $q->whereNull('end_date')
                            ->orWhere('end_date', '>', now());
                    });
            })
            ->paginate($perPage);
    }

    /**
     * Get subscriber by ID with subscription details
     */
    public function getSubscriberById(string $userId): ?User
    {
        return User::with(['userSubscriptions.subscriptionPlan'])
            ->find($userId);
    }

    /**
     * Get subscribers by plan
     */
    public function getSubscribersByPlan(int $planId, int $perPage = 15): LengthAwarePaginator
    {
        return User::with(['userSubscriptions' => function ($query) use ($planId) {
            $query->with('subscriptionPlan')
                ->where('subscription_plan_id', $planId);
        }])
            ->whereHas('userSubscriptions', function ($query) use ($planId) {
                $query->where('subscription_plan_id', $planId);
            })
            ->paginate($perPage);
    }

    /**
     * Get subscriber statistics
     */
    public function getSubscriberStats(): array
    {
        $totalSubscribers = User::whereHas('userSubscriptions')->count();
        $activeSubscribers = User::whereHas('userSubscriptions', function ($query) {
            $query->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>', now());
                });
        })->count();

        $totalSubscriptions = Subscription::count();
        $activeSubscriptions = UserSubscription::where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>', now());
            })
            ->count();

        return [
            'total_subscribers' => $totalSubscribers,
            'active_subscribers' => $activeSubscribers,
            'total_subscriptions' => $totalSubscriptions,
            'active_subscriptions' => $activeSubscriptions,
        ];
    }

    /**
     * Search subscribers by name or email
     */
    public function searchSubscribers(string $searchTerm, int $perPage = 15): LengthAwarePaginator
    {
        return User::with(['userSubscriptions.subscriptionPlan'])
            ->whereHas('userSubscriptions')
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%")
                    ->orWhere('first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('last_name', 'like', "%{$searchTerm}%");
            })
            ->paginate($perPage);
    }
}
