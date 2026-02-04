<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ApiSessionAuth
{
    /**
     * Handle an incoming request.
     * Ensures API routes can authenticate via session for web requests
     */
    public function handle(Request $request, Closure $next)
    {
        // Force use web guard for API session auth
        Auth::shouldUse('web');
        
        return $next($request);
    }
}
