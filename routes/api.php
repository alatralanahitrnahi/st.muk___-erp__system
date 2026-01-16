<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\AdmissionController;
use App\Http\Controllers\Api\ProgramController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\FeeController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ComplianceController;
use App\Http\Controllers\Api\ActivityLogController;

use App\Http\Controllers\Api\LessonPlanController;

use App\Http\Controllers\Api\PermissionConfigController;
use App\Http\Controllers\Api\PrincipalConfigController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Student Management
    Route::apiResource('students', StudentController::class);
    Route::post('students/{student}/approve', [StudentController::class, 'approve']);
    Route::post('students/{student}/reject', [StudentController::class, 'reject']);

    // Admission Management
    Route::post('admissions', [AdmissionController::class, 'store']);
    Route::get('admissions/{id}', [AdmissionController::class, 'show']);
    Route::post('admissions/{id}/approve', [AdmissionController::class, 'approve']);
    Route::post('admissions/{id}/reject', [AdmissionController::class, 'reject']);

    // Academic Structure
    Route::apiResource('programs', ProgramController::class);
    Route::apiResource('departments', DepartmentController::class);

    // Fee Management
    Route::get('fee-structures', [FeeController::class, 'getFeeStructures']);
    Route::post('fee-structures', [FeeController::class, 'createFeeStructure']);
    Route::get('students/{id}/fees', [FeeController::class, 'getStudentFees']);
    Route::post('assign-fee', [FeeController::class, 'assignFeeToStudent']);
    Route::post('record-payment', [FeeController::class, 'recordPayment']);

    // Attendance Management
    Route::post('attendance/mark', [AttendanceController::class, 'markAttendance']);
    Route::get('attendance', [AttendanceController::class, 'getAttendance']);
    Route::get('students/{id}/attendance', [AttendanceController::class, 'getStudentAttendance']);
    Route::get('attendance/report', [AttendanceController::class, 'getAttendanceReport']);

    // Examination Management
    Route::get('subjects', [ExamController::class, 'getSubjects']);
    Route::post('subjects', [ExamController::class, 'createSubject']);
    Route::post('results/enter', [ExamController::class, 'enterResults']);
    Route::get('students/{id}/results', [ExamController::class, 'getStudentResults']);
    Route::get('results/report', [ExamController::class, 'getResultsReport']);

    // Reporting & Analytics
    Route::get('reports/dashboard', [ReportController::class, 'dashboardStats']);
    Route::get('reports/students', [ReportController::class, 'studentReport']);
    Route::get('reports/fees', [ReportController::class, 'feeReport']);
    Route::get('reports/attendance', [ReportController::class, 'attendanceReport']);
    Route::get('reports/results', [ReportController::class, 'resultReport']);
    Route::get('reports/naac', [ReportController::class, 'naacReport']);

    // Payment Integration
    Route::post('payments/create-order', [PaymentController::class, 'createOrder']);
    Route::post('payments/verify', [PaymentController::class, 'verifyPayment']);
    Route::get('payments/history/{studentId}', [PaymentController::class, 'getPaymentHistory']);
    Route::post('payments/refund', [PaymentController::class, 'refundPayment']);

    // Compliance & Government Reporting
    Route::get('compliance/naac-report', [ComplianceController::class, 'getNaacReport']);
    Route::get('compliance/government-report', [ComplianceController::class, 'getGovernmentReport']);
    Route::get('compliance/calendar', [ComplianceController::class, 'getComplianceCalendar']);
    Route::get('compliance/dashboard', [ComplianceController::class, 'getComplianceDashboard']);
    Route::post('compliance/generate-report', [ComplianceController::class, 'generateAutomatedReport']);
    Route::get('compliance/data-quality', [ComplianceController::class, 'getDataQualityReport']);
    Route::post('compliance/export', [ComplianceController::class, 'exportComplianceData']);

    // Lesson Planning
    Route::apiResource('lesson-plans', LessonPlanController::class);
    Route::post('lesson-plans/{lessonPlan}/submit', [LessonPlanController::class, 'submit']);
    Route::post('lesson-plans/{lessonPlan}/approve', [LessonPlanController::class, 'approve']);
    Route::post('lesson-plans/{lessonPlan}/reflection', [LessonPlanController::class, 'addReflection']);

    // Permission Configuration (Principal only)
    Route::prefix('config')->middleware('role:principal')->group(function() {
        Route::get('modules/{roleId}', [PermissionConfigController::class, 'getModuleAccess']);
        Route::post('modules/{roleId}', [PermissionConfigController::class, 'updateModuleAccess']);
        Route::get('workflows', [PermissionConfigController::class, 'getApprovalWorkflows']);
        Route::post('workflows', [PermissionConfigController::class, 'updateApprovalWorkflow']);
        Route::get('visibility/{roleId}', [PermissionConfigController::class, 'getVisibilityRules']);
        Route::post('visibility/{roleId}', [PermissionConfigController::class, 'updateVisibilityRule']);
    });

    // Principal Configuration System
    Route::prefix('principal/config')->middleware('role:principal')->group(function() {
        Route::get('permissions/{module}', [PrincipalConfigController::class, 'getModulePermissions']);
        Route::post('permissions', [PrincipalConfigController::class, 'updatePermissions']);
        Route::get('visibility/{module}', [PrincipalConfigController::class, 'getVisibilityRules']);
        Route::post('visibility', [PrincipalConfigController::class, 'updateVisibilityRules']);
        Route::get('approval/{workflow}', [PrincipalConfigController::class, 'getApprovalChains']);
        Route::post('approval', [PrincipalConfigController::class, 'updateApprovalChain']);
        Route::post('export', [PrincipalConfigController::class, 'exportConfig']);
        Route::post('import', [PrincipalConfigController::class, 'importConfig']);
        Route::get('history/{module}', [PrincipalConfigController::class, 'getHistory']);
    });

    // Activity Logs
    Route::get('activity-logs', [ActivityLogController::class, 'index']);
    Route::get('activity-logs/{id}', [ActivityLogController::class, 'show']);

    // Public routes for admission form
    Route::get('programs/public', [ProgramController::class, 'index']);
    Route::get('departments/public', [DepartmentController::class, 'index']);
});

// Department Head Management
Route::middleware('auth:sanctum')->prefix('department-heads')->group(function () {
    Route::post('/{departmentId}/assign', [App\Http\Controllers\DepartmentHeadController::class, 'assign']);
    Route::delete('/{departmentId}', [App\Http\Controllers\DepartmentHeadController::class, 'remove']);
    Route::get('/', [App\Http\Controllers\DepartmentHeadController::class, 'list']);
});

// Workflow Reports & NAAC Compliance
Route::middleware('auth:sanctum')->prefix('workflow-reports')->group(function () {
    Route::get('/departments/{departmentId}/history', [App\Http\Controllers\WorkflowReportController::class, 'departmentWorkflowHistory']);
    Route::get('/students/{studentId}/history', [App\Http\Controllers\WorkflowReportController::class, 'studentWorkflowHistory']);
    Route::get('/departments/{departmentId}/naac-compliance', [App\Http\Controllers\WorkflowReportController::class, 'naacComplianceReport']);
    Route::get('/principal/dashboard', [App\Http\Controllers\WorkflowReportController::class, 'principalDashboard']);
});


// Include V1 API Routes (Department-Aware)
require __DIR__.'/api_v1.php';
