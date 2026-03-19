<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AllowClarityBot
{
    public function handle(Request $request, Closure $next)
    {
        $userAgent = $request->header('User-Agent');

        // ✅ Allow Microsoft Clarity bot to pass through
        if (stripos($userAgent, 'Clarity-Bot') !== false) {
            return $next($request);
        }

        // Example: if you have IP or bot restrictions, add them below
        // Otherwise, just continue
        return $next($request);
    }
}
