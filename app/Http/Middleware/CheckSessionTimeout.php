<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get inactivity timeout from config (default: 20 minutes / 1200 seconds)
        $inactive = config('session.inactivity_timeout', 1200);

        // Only check timeout for authenticated users
        if (Auth::check()) {
            $lastActivity = session('last_activity');

            // If last_activity exists and timeout exceeded, logout user
            if ($lastActivity !== null && (time() - $lastActivity) > $inactive) {
                // Store the error message before invalidating session
                $errorMessage = 'Your session has expired. Please log in again.';

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Handle Inertia requests - pass message via query parameter
                if ($request->header('X-Inertia')) {
                    return redirect()->route('login', ['session_expired' => $errorMessage]);
                }

                // For regular requests
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => $errorMessage,
                        'session_expired' => true,
                    ], 401);
                }

                // For regular requests - pass message via query parameter
                return redirect()->route('login', ['session_expired' => $errorMessage]);
            }
        }

        // Update last activity timestamp
        session(['last_activity' => time()]);

        return $next($request);
    }
}
