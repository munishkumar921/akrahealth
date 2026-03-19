<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsVerifyEmail
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::user()->is_email_verified) {
            auth()->guard('web')->logout();

            return redirect()->route('login')->with('error', 'You need to confirm your account. We have sent you an activation Link, please check your email.');
        }

        return $next($request);
    }
}
