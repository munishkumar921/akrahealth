<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && (auth()->user()->hasRole('Admin'))) {

            /* Check active subscription */
            $endDate = Carbon::parse(auth()->user()->userSubscription->end_date);

            if ($endDate->isPast()) {
                return redirect('/subscriptions')->with('error', __('Your subscription has expired. Please upgrade to continue enjoying our service.'));
            }

            return $next($request);
        }

        return redirect('/')->with('error', __('auth.unauthorized_access'));
    }
}
