<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsPatient
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $canCheckRole = $user && method_exists($user, 'hasRole');

        if ($canCheckRole && call_user_func([$user, 'hasRole'], 'Patient')) {
            return $next($request);
        }

        // Allow admins who switched into Doctor view to bypass patient-only routes
        if ($canCheckRole && call_user_func([$user, 'hasRole'], 'Admin') && session('switched_role') === 'Doctor') {
            return redirect()->route('doctor.dashboard');
        }

        // If the user is an Admin (not in switched role mode), redirect to admin dashboard
        if ($canCheckRole && call_user_func([$user, 'hasRole'], 'Admin')) {
            return redirect()->route('admin.dashboard')->with('error', __('auth.unauthorized_access'));
        }

        // If the user is not a patient, block access with a proper response
        return redirect('/')->with('error', __('auth.unauthorized_access'));
    }
}
