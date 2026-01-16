<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\DepartmentStudentController;
use App\Http\Controllers\Api\V1\DepartmentAttendanceController;
use App\Http\Controllers\Api\V1\DepartmentResultController;
use App\Http\Controllers\Api\V1\DepartmentFeeController;

/*
|--------------------------------------------------------------------------
| API V1 Routes - Department-Aware Endpoints
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware(['auth', 'validate.department.access'])->group(function () {
    
    // Department Students
    Route::prefix('departments/{departmentId}')->group(function () {
        Route::get('/students', [DepartmentStudentController::class, 'index']);
        Route::get('/students/{studentId}', [DepartmentStudentController::class, 'show']);
        
        // Department Attendance
        Route::get('/attendance', [DepartmentAttendanceController::class, 'index']);
        Route::get('/attendance/report', [DepartmentAttendanceController::class, 'report']);
        
        // Department Results
        Route::get('/results', [DepartmentResultController::class, 'index']);
        
        // Department Fees
        Route::get('/fees', [DepartmentFeeController::class, 'index']);
        Route::get('/fees/summary', [DepartmentFeeController::class, 'summary']);
    });
});

/*
|--------------------------------------------------------------------------
| Legacy Routes - Backward Compatible (Deprecated)
|--------------------------------------------------------------------------
| These routes maintain backward compatibility for 3 months
| Will be removed in version 2.0
*/

Route::middleware('auth')->group(function () {
    
    // Legacy Students - Optional department_id parameter
    Route::get('/students', function (Illuminate\Http\Request $request) {
        $departmentId = $request->get('department_id');
        
        if ($departmentId) {
            $request->headers->set('X-API-Deprecation', 'Use /api/v1/departments/{id}/students instead');
        }
        
        $query = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->join('programs', 'students.program_id', '=', 'programs.id');
        
        if ($departmentId) {
            $query->where('students.department_id', $departmentId);
        }
        
        $students = $query->select('students.*', 'users.name', 'users.email', 'programs.name as program_name')
            ->paginate(15);
        
        return response()->json([
            'success' => true,
            'message' => 'Students retrieved successfully',
            'data' => $students->items(),
            'meta' => [
                'current_page' => $students->currentPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
                'timestamp' => now()->toIso8601String(),
                'deprecation_notice' => $departmentId ? 'This endpoint is deprecated. Use /api/v1/departments/{id}/students' : null
            ]
        ]);
    });
    
    // Legacy Attendance
    Route::get('/attendance/report', function (Illuminate\Http\Request $request) {
        $departmentId = $request->get('department_id');
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        if ($departmentId) {
            $request->headers->set('X-API-Deprecation', 'Use /api/v1/departments/{id}/attendance/report instead');
        }
        
        $query = DB::table('attendance')
            ->whereBetween('date', [$dateFrom, $dateTo]);
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        $report = $query->select(
            DB::raw('COUNT(*) as total_records'),
            DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count'),
            DB::raw('ROUND(SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as attendance_rate')
        )->first();
        
        return response()->json([
            'success' => true,
            'message' => 'Attendance report generated successfully',
            'data' => $report,
            'meta' => [
                'timestamp' => now()->toIso8601String(),
                'deprecation_notice' => $departmentId ? 'This endpoint is deprecated. Use /api/v1/departments/{id}/attendance/report' : null
            ]
        ]);
    });
    
    // Legacy Results
    Route::get('/results/report', function (Illuminate\Http\Request $request) {
        $departmentId = $request->get('department_id');
        
        if ($departmentId) {
            $request->headers->set('X-API-Deprecation', 'Use /api/v1/departments/{id}/results instead');
        }
        
        $query = DB::table('results');
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        $results = $query->paginate(15);
        
        return response()->json([
            'success' => true,
            'message' => 'Results retrieved successfully',
            'data' => $results->items(),
            'meta' => [
                'current_page' => $results->currentPage(),
                'total' => $results->total(),
                'timestamp' => now()->toIso8601String(),
                'deprecation_notice' => $departmentId ? 'This endpoint is deprecated. Use /api/v1/departments/{id}/results' : null
            ]
        ]);
    });
});
