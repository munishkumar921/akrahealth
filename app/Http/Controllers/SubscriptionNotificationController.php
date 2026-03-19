<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubscriptionNotificationController extends Controller
{
    public function notify(Request $request)
    {
        $user = $request->user();
        $subscription = null;
        $subscriptionPlan = null;
        $hospital = $user?->doctor?->hospital ?? null;

        /* Load subscription for users with hospital (doctors) or Admin users */
        if ($user) {
            $subscriptionService = app(\App\Services\UserSubscriptionService::class);
            $subscriptionUser = $user;

            /* If user is a Doctor, check the subscription of the Admin (Hospital Owner) */
            if ($user->hasRole('Doctor') && $hospital) {
                $hospitalOwnerId = $hospital->user_id;
                if ($hospitalOwnerId && $hospitalOwnerId != $user->id) {
                    $owner = \App\Models\User::find($hospitalOwnerId);
                    if ($owner) {
                        $subscriptionUser = $owner;
                    }
                }
            }

            // Ensure expired subscriptions are status-synced before reading.
            $subscriptionService->syncExpiredSubscriptionsForUser($subscriptionUser);

            /* Get active subscription first */
            $subscription = $subscriptionService
                ->getActiveSubscriptions($subscriptionUser)
                ->first();

            /* If no active subscription, get the latest one (including expired) */
            if (! $subscription) {
                $subscription = \App\Models\UserSubscription::where('user_id', $subscriptionUser->id)
                    ->latest('end_date')
                    ->first();
            }

            if ($subscription) {
                $subscriptionPlan = $subscription->subscriptionPlan;
            }
        }

        return response()->json([
            'subscription' => $subscription,
            'subscriptionPlan' => $subscriptionPlan,
        ]);
    }
}
