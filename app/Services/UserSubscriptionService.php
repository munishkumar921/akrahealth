<?php

namespace App\Services;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UserSubscriptionService
{
    /**
     * Sync active subscriptions to expired when they are past grace period.
     *
     * @return int number of updated records
     */
    public function syncExpiredSubscriptionsForUser(User $user, int $gracePeriodDays = 0): int
    {
        $expiredBeforeDate = Carbon::today()->subDays($gracePeriodDays)->toDateString();

        return UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', $expiredBeforeDate)
            ->update(['status' => 'expired']);
    }

    /**
     * Subscribe user to a plan
     *
     * @return UserSubscription
     */
    public function subscribe(User $user, SubscriptionPlan $plan, array $additionalData = [])
    {
        // Check if user already has an active subscription to this plan
        $existingSubscription = UserSubscription::where('user_id', $user->id)
            ->where('subscription_plan_id', $plan->id)
            ->where('status', 'active')
            ->first();

        if ($existingSubscription) {
            throw new \Exception('User already has an active subscription to this plan.');
        }

        $startDate = Carbon::now();
        $endDate = $this->calculateEndDate($startDate, $plan->frequency);

        $subscriptionData = array_merge([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'amount' => $plan->price,
        ], $additionalData);

        return UserSubscription::create($subscriptionData);
    }

    /**
     * Cancel user subscription
     *
     * @return bool
     */
    public function cancel(UserSubscription $subscription)
    {
        $subscription->update([
            'status' => 'cancelled',
            'end_date' => Carbon::now()->toDateString(),
        ]);

        return true;
    }

    /**
     * Renew subscription
     *
     * @return UserSubscription
     */
    public function renew(UserSubscription $subscription)
    {
        $newEndDate = $this->calculateEndDate(
            Carbon::parse($subscription->end_date),
            $subscription->subscriptionPlan->frequency
        );

        $subscription->update([
            'status' => 'active',
            'end_date' => $newEndDate->toDateString(),
        ]);

        return $subscription->fresh();
    }

    /**
     * Check if user has active subscription
     *
     * @param  string|null  $planType
     * @return bool
     */
    public function hasActiveSubscription(User $user, $planType = null)
    {
        $this->syncExpiredSubscriptionsForUser($user, 0);

        $query = UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', Carbon::today());
            });

        if ($planType) {
            $query->whereHas('subscriptionPlan', function ($q) use ($planType) {
                $q->where('plan_for', $planType);
            });
        }

        return $query->latest()->exists();
    }

    /**
     * Get user's active subscriptions
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveSubscriptions(User $user)
    {
        $this->syncExpiredSubscriptionsForUser($user, 0);

        return UserSubscription::with('subscriptionPlan')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', Carbon::today());
            })->latest()
            ->get();
    }

    /**
     * Calculate end date based on billing cycle
     *
     * @param  string  $billingCycle
     * @return Carbon
     */
    private function calculateEndDate(Carbon $startDate, $frequency)
    {
        switch (strtolower($frequency)) {
            case 'monthly':
                return $startDate->copy()->addMonth();
            case 'yearly':
                return $startDate->copy()->addYear();
            default:
                return $startDate->copy()->addMonth();
        }
    }

    /**
     * Create a pending subscription
     *
     * @return UserSubscription
     */
    public function createPendingSubscription(User $user, SubscriptionPlan $plan, array $additionalData = [])
    {
        $startDate = Carbon::now();
        $endDate = $this->calculateEndDate($startDate, $plan->frequency);

        $subscriptionData = array_merge([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'pending',
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'amount' => $plan->price,
        ], $additionalData);

        return UserSubscription::create($subscriptionData);
    }

    /**
     * Process expired subscriptions
     *
     * @return void
     */
    public function processExpiredSubscriptions()
    {
        $expiredSubscriptions = UserSubscription::where('status', 'active')
            ->where('end_date', '<=', Carbon::now())
            ->get();

        foreach ($expiredSubscriptions as $subscription) {
            $subscription->update(['status' => 'expired']);
            Log::info('Subscription expired', ['subscription_id' => $subscription->id]);
        }
    }

    /**
     * Generate payment link for subscription
     *
     * @return array
     */
    public function generatePaymentLink(UserSubscription $subscription, array $customerData)
    {
        $razorpayService = new \App\Services\RazorpayPaymentService;

        $paymentData = [
            'amount' => $subscription->amount,
            'currency' => 'INR',
            'reference_id' => 'sub_'.$subscription->id,
            'description' => 'Subscription payment for '.$subscription->subscriptionPlan->title,
            'customer' => $customerData,
            'notes' => [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
            ],
            'callback_url' => route('payment.callback'),
        ];

        $response = $razorpayService->createPaymentLink($paymentData);

        if ($response['success']) {
            // Store payment_link_id in subscription
            $subscription->update(['payment_link_id' => $response['data']['id']]);
        }

        return $response;
    }

    /**
     * Check if user has an expired subscription
     *
     * @return bool
     */
    public function hasExpiredSubscription(User $user)
    {
        $this->syncExpiredSubscriptionsForUser($user, 0);

        return UserSubscription::where('user_id', $user->id)
            ->where('status', 'expired')
            ->exists();
    }

    /**
     * Get user's expired subscription
     *
     * @return UserSubscription|null
     */
    public function getExpiredSubscription(User $user)
    {
        $this->syncExpiredSubscriptionsForUser($user, 0);

        $expiredSubscription = UserSubscription::with('subscriptionPlan')
            ->where('user_id', $user->id)
            ->where('status', 'expired')
            ->orderBy('end_date', 'desc')
            ->first();

        Log::info('getExpiredSubscription called', [
            'user_id' => $user->id,
            'found' => $expiredSubscription ? true : false,
            'subscription_id' => $expiredSubscription->id ?? null,
            'end_date' => $expiredSubscription->end_date ?? null,
        ]);

        return $expiredSubscription;
    }

    /**
     * Upgrade user subscription from expired subscription to new plan
     *
     * @return UserSubscription
     */
    public function upgradeSubscription(User $user, SubscriptionPlan $newPlan, array $additionalData = [])
    {
        Log::info('upgradeSubscription called', [
            'user_id' => $user->id,
            'new_plan_id' => $newPlan->id,
            'new_plan_title' => $newPlan->title,
        ]);

        // Get the expired subscription
        $expiredSubscription = $this->getExpiredSubscription($user);

        if (! $expiredSubscription) {
            Log::error('upgradeSubscription failed - no expired subscription found', [
                'user_id' => $user->id,
            ]);
            throw new \Exception('No expired subscription found for this user.');
        }

        Log::info('Found expired subscription for upgrade', [
            'expired_subscription_id' => $expiredSubscription->id,
            'expired_subscription_status' => $expiredSubscription->status,
            'end_date' => $expiredSubscription->end_date,
        ]);

        // Check if user already has an active subscription
        $existingActiveSubscription = $this->getActiveSubscriptions($user)->first();
        if ($existingActiveSubscription) {
            Log::error('upgradeSubscription failed - user already has active subscription', [
                'user_id' => $user->id,
                'active_subscription_id' => $existingActiveSubscription->id,
            ]);
            throw new \Exception('User already has an active subscription. Please cancel it first before upgrading.');
        }

        $startDate = Carbon::now();
        $endDate = $this->calculateEndDate($startDate, $newPlan->frequency);

        Log::info('Updating old expired subscription to replaced', [
            'old_subscription_id' => $expiredSubscription->id,
            'new_status' => 'replaced',
        ]);

        // Mark old expired subscription as replaced
        $expiredSubscription->update([
            'status' => 'replaced',
            'replaced_at' => $startDate->toDateTimeString(),
            'replaced_by' => null, // Will be updated after creating new subscription
        ]);

        Log::info('Old subscription updated to replaced', [
            'old_subscription_id' => $expiredSubscription->id,
            'replaced_at' => $startDate->toDateTimeString(),
        ]);

        // Create new subscription
        $subscriptionData = array_merge([
            'user_id' => $user->id,
            'subscription_plan_id' => $newPlan->id,
            'status' => 'active',
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'amount' => $newPlan->price,
            'currency' => $newPlan->currency ?? 'INR',
            'upgraded_from' => $expiredSubscription->id,
        ], $additionalData);

        $newSubscription = UserSubscription::create($subscriptionData);

        Log::info('New subscription created', [
            'new_subscription_id' => $newSubscription->id,
            'new_subscription_status' => $newSubscription->status,
            'start_date' => $newSubscription->start_date,
            'end_date' => $newSubscription->end_date,
        ]);

        // Update old subscription with reference to new one
        $expiredSubscription->update([
            'replaced_by' => $newSubscription->id,
        ]);

        Log::info('Subscription upgrade completed successfully', [
            'user_id' => $user->id,
            'old_subscription_id' => $expiredSubscription->id,
            'new_subscription_id' => $newSubscription->id,
            'new_plan_id' => $newPlan->id,
            'old_status_was' => 'expired',
            'new_status_is' => 'replaced',
        ]);

        return $newSubscription;
    }

    /**
     * Get user's all subscriptions including expired
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllSubscriptions(User $user)
    {
        return UserSubscription::with('subscriptionPlan')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Check if user can upgrade subscription
     *
     * @return bool
     */
    public function canUpgrade(User $user)
    {
        // Can upgrade if:
        // 1. Has expired subscription (not already active)
        // 2. Doesn't have active subscription
        return $this->hasExpiredSubscription($user) &&
               ! $this->hasActiveSubscription($user);
    }

    /**
     * Check if user has an upgraded subscription
     *
     * @return bool
     */
    public function hasUpgradedSubscription(User $user)
    {
        return UserSubscription::where('user_id', $user->id)
            ->where('status', 'upgraded')
            ->exists();
    }

    /**
     * Get user's upgraded subscription
     *
     * @return UserSubscription|null
     */
    public function getUpgradedSubscription(User $user)
    {
        return UserSubscription::with('subscriptionPlan')
            ->where('user_id', $user->id)
            ->where('status', 'upgraded')
            ->orderBy('upgraded_at', 'desc')
            ->first();
    }

    /**
     * Upgrade user from an ACTIVE subscription to a new plan
     * This is for users who want to upgrade while their current subscription is still active
     * The old subscription is CANCELLED first, then new one is created
     *
     * @return UserSubscription
     */
    public function upgradeActiveSubscription(User $user, SubscriptionPlan $newPlan, array $additionalData = [])
    {
        Log::info('upgradeActiveSubscription called', [
            'user_id' => $user->id,
            'new_plan_id' => $newPlan->id,
            'new_plan_title' => $newPlan->title,
        ]);

        // Get the current active subscription
        $currentSubscription = $this->getActiveSubscriptions($user)->first();

        if (! $currentSubscription) {
            Log::error('upgradeActiveSubscription failed - no active subscription found', [
                'user_id' => $user->id,
            ]);
            throw new \Exception('No active subscription found for this user.');
        }

        Log::info('Found active subscription for upgrade', [
            'current_subscription_id' => $currentSubscription->id,
            'current_plan_id' => $currentSubscription->subscription_plan_id,
            'current_plan_title' => $currentSubscription->subscriptionPlan->title ?? 'Unknown',
            'current_end_date' => $currentSubscription->end_date,
        ]);

        // Check if upgrading to the same plan
        if ($currentSubscription->subscription_plan_id === $newPlan->id) {
            Log::warning('upgradeActiveSubscription - upgrading to same plan', [
                'user_id' => $user->id,
                'plan_id' => $newPlan->id,
            ]);
            throw new \Exception('You are already subscribed to this plan.');
        }

        // STEP 1: Cancel the old active subscription FIRST
        Log::info('Cancelling old active subscription', [
            'old_subscription_id' => $currentSubscription->id,
        ]);

        $currentSubscription->update([
            'status' => 'cancelled',
            'end_date' => Carbon::now()->toDateString(),
        ]);

        Log::info('Old subscription cancelled', [
            'old_subscription_id' => $currentSubscription->id,
            'cancelled_at' => Carbon::now()->toDateTimeString(),
        ]);

        // STEP 2: Create new subscription with the new plan
        $startDate = Carbon::now();
        $endDate = $this->calculateEndDate($startDate, $newPlan->frequency);

        // Create new subscription
        $subscriptionData = array_merge([
            'user_id' => $user->id,
            'subscription_plan_id' => $newPlan->id,
            'status' => 'pending_upgrade', // Will become active after payment verification
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'amount' => $newPlan->price,
            'currency' => $newPlan->currency ?? 'INR',
            'upgraded_from' => $currentSubscription->id,
        ], $additionalData);

        $newSubscription = UserSubscription::create($subscriptionData);

        Log::info('New subscription created as pending_upgrade', [
            'new_subscription_id' => $newSubscription->id,
            'new_subscription_status' => $newSubscription->status,
            'new_plan_id' => $newPlan->id,
            'start_date' => $newSubscription->start_date,
            'end_date' => $newSubscription->end_date,
        ]);

        Log::info('Active subscription upgrade initiated successfully', [
            'user_id' => $user->id,
            'old_subscription_id' => $currentSubscription->id,
            'old_subscription_status' => 'cancelled',
            'new_subscription_id' => $newSubscription->id,
            'new_plan_id' => $newPlan->id,
        ]);

        return $newSubscription;
    }

    /**
     * Complete active subscription upgrade after payment verification
     * Activates the new subscription (old was already cancelled)
     *
     * @return UserSubscription
     */
    public function completeActiveSubscriptionUpgrade(UserSubscription $newSubscription)
    {
        Log::info('completeActiveSubscriptionUpgrade called', [
            'new_subscription_id' => $newSubscription->id,
        ]);

        // Activate the new subscription
        $newSubscription->update([
            'status' => 'active',
            'payment_status' => 'paid',
        ]);

        Log::info('New subscription activated', [
            'new_subscription_id' => $newSubscription->id,
            'new_status' => 'active',
        ]);

        Log::info('Active subscription upgrade completed successfully', [
            'new_subscription_id' => $newSubscription->id,
            'new_plan_id' => $newSubscription->subscription_plan_id,
            'new_status' => 'active',
        ]);

        return $newSubscription->fresh();
    }

    /**
     * Check if user can upgrade from active subscription
     *
     * @param  string|null  $newPlanId
     * @return bool
     */
    public function canUpgradeFromActive(User $user, $newPlanId = null)
    {
        // User can upgrade if they have an active subscription
        if (! $this->hasActiveSubscription($user)) {
            return false;
        }

        // If new plan ID is provided, check it's different from current plan
        if ($newPlanId) {
            $activeSubscription = $this->getActiveSubscriptions($user)->first();
            if ($activeSubscription && $activeSubscription->subscription_plan_id === $newPlanId) {
                return false;
            }
        }

        return true;
    }
}
