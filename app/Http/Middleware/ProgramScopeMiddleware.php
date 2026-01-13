<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProgramScopeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('student')) {
            // Students can only access their own data
            // This would be implemented in controllers or queries
        } elseif ($user && $user->hasRole('faculty')) {
            // Faculty can access assigned programs
        }

        return $next($request);
    }
}
