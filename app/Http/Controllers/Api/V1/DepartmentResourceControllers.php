<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentResultController extends Controller
{
    use ApiResponse;

    public function index(Request $request, int $departmentId)
    {
        $academicYear = $request->get('academic_year');
        $semester = $request->get('semester');
        $perPage = $request->get('per_page', 15);

        $query = DB::table('results')
            ->join('students', 'results.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->where('results.department_id', $departmentId);

        if ($academicYear) {
            $query->where('results.academic_year', $academicYear);
        }

        if ($semester) {
            $query->where('results.semester', $semester);
        }

        $results = $query->select('results.*', 'users.name as student_name')
            ->paginate($perPage);

        return $this->paginatedResponse($results, 'Results retrieved successfully', $departmentId);
    }
}

class DepartmentFeeController extends Controller
{
    use ApiResponse;

    public function index(Request $request, int $departmentId)
    {
        $status = $request->get('status');
        $perPage = $request->get('per_page', 15);

        $query = DB::table('fees')
            ->join('students', 'fees.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->where('fees.department_id', $departmentId);

        if ($status) {
            $query->where('fees.status', $status);
        }

        $fees = $query->select('fees.*', 'users.name as student_name')
            ->paginate($perPage);

        return $this->paginatedResponse($fees, 'Fees retrieved successfully', $departmentId);
    }

    public function summary(int $departmentId)
    {
        $summary = DB::table('fees')
            ->where('department_id', $departmentId)
            ->select(
                DB::raw('COUNT(*) as total_records'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(CASE WHEN status = "paid" THEN amount ELSE 0 END) as paid_amount'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as pending_amount')
            )
            ->first();

        return $this->successResponse($summary, 'Fee summary generated successfully', $departmentId);
    }
}
