<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

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

            // Allow if user has Doctor role
            if ($user->hasRole('Doctor')) {
                return $next($request);
            }

            // Allow if super/admin is switched to doctor role
            if (($user->hasRole('Admin')) && session('switched_role') === 'Doctor') {
                return $next($request);
            }
        }

        return redirect('/')->with('error', __('auth.unauthorized_access'));
    }
}
