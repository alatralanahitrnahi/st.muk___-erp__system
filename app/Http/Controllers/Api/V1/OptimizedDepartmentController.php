<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Services\PermissionCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class OptimizedDepartmentController extends Controller
{
    use ApiResponse;

    private $permissionCache;

    public function __construct(PermissionCacheService $permissionCache)
    {
        $this->permissionCache = $permissionCache;
    }

    public function dashboard(Request $request, int $departmentId)
    {
        $cacheKey = "dept_dashboard:{$departmentId}";

        $data = Cache::remember($cacheKey, 300, function () use ($departmentId) {
            return [
                'total_students' => DB::table('students')
                    ->where('department_id', $departmentId)
                    ->count(),
                
                'total_faculty' => DB::table('user_departments')
                    ->where('department_id', $departmentId)
                    ->where('role_in_department', 'faculty')
                    ->count(),
                
                'attendance_rate' => DB::table('attendance')
                    ->where('department_id', $departmentId)
                    ->where('date', '>=', now()->subDays(30))
                    ->selectRaw('ROUND(SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as rate')
                    ->value('rate') ?? 0,
                
                'pending_approvals' => DB::table('workflow_history')
                    ->where('department_id', $departmentId)
                    ->whereIn('to_state', ['pending', 'registrar_review', 'hod_review'])
                    ->distinct('entity_id')
                    ->count()
            ];
        });

        return $this->successResponse($data, 'Dashboard data retrieved successfully', $departmentId);
    }

    public function studentsOptimized(Request $request, int $departmentId)
    {
        $perPage = $request->get('per_page', 15);

        // Single optimized query with eager loading
        $students = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->join('programs', 'students.program_id', '=', 'programs.id')
            ->where('students.department_id', $departmentId)
            ->select(
                'students.id',
                'students.enrollment_number',
                'users.name',
                'users.email',
                'programs.name as program_name',
                'students.created_at'
            )
            ->paginate($perPage);

        return $this->paginatedResponse($students, 'Students retrieved successfully', $departmentId);
    }
}
