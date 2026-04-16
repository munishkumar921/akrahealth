<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Arr;

class IsDoctor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = auth()->user();

            /* Check active subscription */
            $hospitalOwner = $user?->doctor?->hospital?->user;
            $subscription = $hospitalOwner?->userSubscription;

            if (!$subscription || !$subscription->end_date) {
                return redirect('/subscriptions')->with('error', 'No active subscription found.');
            }

            if (Carbon::parse($subscription->end_date)->lt(now())) {
                return redirect('/subscriptions')->with('error', 'Your subscription has expired. Please upgrade to continue enjoying our service.');
            }

            /* Check if current feature is allowed in the current Subscription plan */
            $plan = strtolower($subscription->subscriptionPlan->title);

            $features = config('features');
            $allRoutes = Arr::flatten($features[$plan]);
            $currentRoute = $request->route()->getName();
            if (in_array($currentRoute, $allRoutes)) {
                return redirect('/subscriptions')->with('error', 'This feature is not available in your current plan. Upgrade your plan to unlock access.');
            }

            /* Allow if user has Doctor role */
            if ($user->hasRole('Doctor')) {
                return $next($request);
            }

            /* Allow if super/admin is switched to doctor role */
            if (($user->hasRole('Admin')) && session('switched_role') === 'Doctor') {
                return $next($request);
            }
        }

        return redirect('/')->with('error', __('auth.unauthorized_access'));
    }
}
