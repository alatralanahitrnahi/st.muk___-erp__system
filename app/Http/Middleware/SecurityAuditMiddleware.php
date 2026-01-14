<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SecurityAuditMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $user = $request->user();
        $departmentId = $request->route('departmentId') ?? $request->get('department_id');

        $response = $next($request);

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        if ($user) {
            $this->logAccess($request, $user, $departmentId, $response->status(), $duration);
            $this->checkSuspiciousActivity($user->id, $departmentId);
        }

        return $response;
    }

    private function logAccess(Request $request, $user, ?int $departmentId, int $statusCode, float $duration): void
    {
        DB::table('security_audit_logs')->insert([
            'user_id' => $user->id,
            'department_id' => $departmentId,
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'status_code' => $statusCode,
            'duration_ms' => $duration,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_params' => json_encode($request->except(['password', 'token'])),
            'created_at' => now()
        ]);
    }

    private function checkSuspiciousActivity(int $userId, ?int $departmentId): void
    {
        $cacheKey = "suspicious_check:{$userId}:{$departmentId}";

        if (Cache::has($cacheKey)) {
            return;
        }

        // Check for rapid department switching (> 10 switches in 5 minutes)
        $recentSwitches = DB::table('security_audit_logs')
            ->where('user_id', $userId)
            ->where('created_at', '>', now()->subMinutes(5))
            ->distinct('department_id')
            ->count();

        if ($recentSwitches > 10) {
            $this->createComplianceAlert($userId, 'rapid_department_switching', $departmentId);
        }

        // Check for failed access attempts (> 5 in 10 minutes)
        $failedAttempts = DB::table('security_audit_logs')
            ->where('user_id', $userId)
            ->where('status_code', 403)
            ->where('created_at', '>', now()->subMinutes(10))
            ->count();

        if ($failedAttempts > 5) {
            $this->createComplianceAlert($userId, 'multiple_failed_access', $departmentId);
        }

        Cache::put($cacheKey, true, 60);
    }

    private function createComplianceAlert(int $userId, string $alertType, ?int $departmentId): void
    {
        DB::table('compliance_alerts')->insert([
            'user_id' => $userId,
            'department_id' => $departmentId,
            'alert_type' => $alertType,
            'severity' => 'high',
            'description' => $this->getAlertDescription($alertType),
            'created_at' => now()
        ]);
    }

    private function getAlertDescription(string $alertType): string
    {
        return match($alertType) {
            'rapid_department_switching' => 'User switched departments more than 10 times in 5 minutes',
            'multiple_failed_access' => 'User had more than 5 failed access attempts in 10 minutes',
            default => 'Suspicious activity detected'
        };
    }
}
