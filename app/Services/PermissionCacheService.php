<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PermissionCacheService
{
    private const CACHE_TTL = 3600; // 60 minutes

    public function getUserDepartmentPermissions(int $userId, int $departmentId): array
    {
        $cacheKey = "permissions:{$userId}:{$departmentId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $departmentId) {
            $role = DB::table('user_departments')
                ->where('user_id', $userId)
                ->where('department_id', $departmentId)
                ->value('role_in_department');

            if (!$role) {
                return [];
            }

            return DB::table('department_permissions')
                ->where('department_id', $departmentId)
                ->where('role', $role)
                ->get()
                ->keyBy('module')
                ->map(fn($p) => [
                    'can_view' => $p->can_view,
                    'can_create' => $p->can_create,
                    'can_edit' => $p->can_edit,
                    'can_delete' => $p->can_delete,
                    'can_approve' => $p->can_approve
                ])
                ->toArray();
        });
    }

    public function invalidateUserPermissions(int $userId, ?int $departmentId = null): void
    {
        if ($departmentId) {
            Cache::forget("permissions:{$userId}:{$departmentId}");
        } else {
            $departments = DB::table('user_departments')
                ->where('user_id', $userId)
                ->pluck('department_id');

            foreach ($departments as $deptId) {
                Cache::forget("permissions:{$userId}:{$deptId}");
            }
        }
    }

    public function invalidateDepartmentPermissions(int $departmentId): void
    {
        $users = DB::table('user_departments')
            ->where('department_id', $departmentId)
            ->pluck('user_id');

        foreach ($users as $userId) {
            Cache::forget("permissions:{$userId}:{$departmentId}");
        }
    }
}
