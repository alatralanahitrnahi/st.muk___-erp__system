<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SecureSessionService
{
    private const SESSION_TTL = 900; // 15 minutes
    private const COOKIE_NAME = 'pvgs_session';

    public function createSession($user, ?int $departmentId = null): string
    {
        $sessionId = Str::random(64);
        
        $sessionData = [
            'user_id' => $user->id,
            'department_id' => $departmentId,
            'created_at' => now()->timestamp,
            'last_activity' => now()->timestamp,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ];

        Cache::put("session:{$sessionId}", $sessionData, self::SESSION_TTL);
        
        $this->logSessionActivity($user->id, 'session_created', $departmentId);

        return $sessionId;
    }

    public function rotateSession(string $oldSessionId, int $departmentId): string
    {
        $oldSession = Cache::get("session:{$oldSessionId}");
        
        if (!$oldSession) {
            throw new \Exception('Invalid session');
        }

        Cache::forget("session:{$oldSessionId}");
        
        $newSessionId = Str::random(64);
        $oldSession['department_id'] = $departmentId;
        $oldSession['last_activity'] = now()->timestamp;
        
        Cache::put("session:{$newSessionId}", $oldSession, self::SESSION_TTL);
        
        $this->logSessionActivity($oldSession['user_id'], 'session_rotated', $departmentId);

        return $newSessionId;
    }

    public function validateSession(string $sessionId): ?array
    {
        $session = Cache::get("session:{$sessionId}");
        
        if (!$session) {
            return null;
        }

        $inactiveTime = now()->timestamp - $session['last_activity'];
        
        if ($inactiveTime > self::SESSION_TTL) {
            Cache::forget("session:{$sessionId}");
            $this->logSessionActivity($session['user_id'], 'session_expired', $session['department_id']);
            return null;
        }

        $session['last_activity'] = now()->timestamp;
        Cache::put("session:{$sessionId}", $session, self::SESSION_TTL);

        return $session;
    }

    public function destroySession(string $sessionId): void
    {
        $session = Cache::get("session:{$sessionId}");
        
        if ($session) {
            $this->logSessionActivity($session['user_id'], 'session_destroyed', $session['department_id']);
        }
        
        Cache::forget("session:{$sessionId}");
    }

    private function logSessionActivity(int $userId, string $action, ?int $departmentId): void
    {
        DB::table('audit_logs')->insert([
            'user_id' => $userId,
            'department_id' => $departmentId,
            'action' => $action,
            'entity_type' => 'Session',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now()
        ]);
    }
}
