<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class DepartmentRateLimiter
{
    public function handle(Request $request, Closure $next, int $maxAttempts = 60, int $decayMinutes = 1)
    {
        $departmentId = $request->route('departmentId') ?? $request->get('department_id');
        $user = $request->user();
        
        if (!$user) {
            return $next($request);
        }

        $key = $this->resolveRequestSignature($request, $user->id, $departmentId);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            return response()->json([
                'success' => false,
                'message' => 'Too many requests',
                'errors' => ['rate_limit' => ["Too many requests. Please try again in {$seconds} seconds."]],
                'meta' => [
                    'retry_after' => $seconds,
                    'timestamp' => now()->toIso8601String()
                ]
            ], 429);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $response->header('X-RateLimit-Limit', $maxAttempts)
                        ->header('X-RateLimit-Remaining', RateLimiter::remaining($key, $maxAttempts));
    }

    protected function resolveRequestSignature(Request $request, int $userId, ?int $departmentId): string
    {
        $route = $request->route()->getName() ?? $request->path();
        return "dept_api:{$userId}:{$departmentId}:{$route}";
    }
}
