<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiSessionAuth
{
    /**
     * Handle an incoming request.
     * Ensures API routes can authenticate via session for web requests
     */
    public function handle(Request $request, Closure $next)
    {
        // If user is already authenticated via session, continue
        if (Auth::check()) {
            return $next($request);
        }

        // If it's an API request with session cookie, ensure we try to authenticate
        if ($request->is('api/*')) {
            // Force session-based authentication
            Auth::shouldUse('web');
            
            // Try to guard with session
            if (!Auth::check() && $request->hasCookie(config('session.cookie'))) {
                // Session exists but user not loaded, load it
                $user = Auth::guard('web')->user();
            }
        }

        return $next($request);
    }
}
