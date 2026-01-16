<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentAttendanceController extends Controller
{
    use ApiResponse;

    public function index(Request $request, int $departmentId)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        
        $attendance = DB::table('attendance_records')
            ->join('students', 'attendance_records.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->where('students.department_id', $departmentId)
            ->where('attendance_records.date', $date)
            ->select('attendance_records.*', 'users.name as student_name', 'students.admission_number')
            ->get();

        return $this->successResponse($attendance, 'Attendance retrieved successfully', $departmentId);
    }

    public function report(Request $request, int $departmentId)
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        $report = DB::table('attendance_records')
            ->join('students', 'attendance_records.student_id', '=', 'students.id')
            ->where('students.department_id', $departmentId)
            ->whereBetween('attendance_records.date', [$dateFrom, $dateTo])
            ->select(
                DB::raw('COUNT(*) as total_records'),
                DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count'),
                DB::raw('SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_count'),
                DB::raw('ROUND(SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as attendance_percentage')
            )
            ->first();

        return $this->successResponse($report, 'Attendance report generated successfully', $departmentId);
    }
}
