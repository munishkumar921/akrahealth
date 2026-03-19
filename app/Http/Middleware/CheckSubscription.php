<?php

namespace App\Http\Middleware;

use App\Services\UserSubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    protected $userSubscriptionService;

    public function __construct(UserSubscriptionService $userSubscriptionService)
    {
        $this->userSubscriptionService = $userSubscriptionService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip check for guest users
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        $subscriptionUser = $user;

        // If user is a Doctor, check the subscription of the Admin (Hospital Owner)
        if ($user->hasRole('Doctor') && $user->doctor && $user->doctor->hospital) {
            $hospitalOwnerId = $user->doctor->hospital->user_id;
            if ($hospitalOwnerId && $hospitalOwnerId != $user->id) {
                $owner = \App\Models\User::find($hospitalOwnerId);
                if ($owner) {
                    $subscriptionUser = $owner;
                }
            }
        }

        // Skip check for users without subscriptions
        if (! \App\Models\UserSubscription::where('user_id', $subscriptionUser->id)->exists()) {
            return $next($request);
        }

        // Skip check for SuperAdmin and Patient roles (Admins should have their subscription checked)
        if ($user->hasRole(['SuperAdmin', 'Patient'])) {
            return $next($request);
        }

        // Check if user has active subscription
        $hasActiveSubscription = $this->userSubscriptionService->hasActiveSubscription($subscriptionUser);

        // Allow access during grace period (3 days)
        if (! $hasActiveSubscription) {
            $gracePeriodDays = 3;
            $graceSubscription = \App\Models\UserSubscription::where('user_id', $subscriptionUser->id)
                ->whereIn('status', ['active', 'expired'])
                ->whereDate('end_date', '>=', now()->subDays($gracePeriodDays)->toDateString())
                ->whereDate('end_date', '<', now()->toDateString())
                ->latest('end_date')
                ->first();

            if ($graceSubscription) {
                $hasActiveSubscription = true;
                // Notify user about grace period once per session
                if (! $request->session()->has('grace_period_alert')) {
                    $planName = $graceSubscription->plan?->name ? $graceSubscription->plan->name.' ' : '';
                    $request->session()->flash('warning', "Your {$planName}subscription has expired, but you are in a grace period. Please renew within ".$gracePeriodDays.' days.');
                    $request->session()->put('grace_period_alert', true);
                }
            }
        }

        if (! $hasActiveSubscription) {
            // Check if user has expired subscription
            $expiredSubscription = \App\Models\UserSubscription::where('user_id', $subscriptionUser->id)
                ->where('status', 'expired')
                ->latest('end_date')
                ->first();

            $planName = $expiredSubscription?->plan?->name ? $expiredSubscription->plan->name.' ' : '';

            // If it's an API request, return JSON
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Your {$planName}subscription has expired. Please renew to continue using all features.",
                    'subscription_expired' => true,
                    'subscription_id' => $expiredSubscription?->id,
                ], 403);
            }

            // For web requests, redirect to renewal page or show payment popup
            // Store the intended URL for redirect after payment
            if (! $request->session()->has('url.intended')) {
                $request->session()->put('url.intended', $request->fullUrl());
            }

            // Prevent redirect loop
            if ($request->routeIs('admin.subscription') ||
                $request->routeIs('subscriptions.*') ||
                $request->routeIs('logout')) {
                return $next($request);
            }

            if ($user->hasRole('Admin')) {
                return redirect()->route('admin.subscription')->with('error', "Your {$planName}subscription has expired. Please renew.");
            }

            // Redirect others to generic subscription page
            return redirect()->route('subscriptions.index')->with('error', "{$planName}Subscription has expired.");
        }

        return $next($request);
    }
}
