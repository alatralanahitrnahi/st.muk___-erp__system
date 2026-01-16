<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentFeeController extends Controller
{
    use ApiResponse;

    public function index(Request $request, int $departmentId)
    {
        $perPage = $request->get('per_page', 15);
        
        $fees = DB::table('student_fees')
            ->join('students', 'student_fees.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->where('students.department_id', $departmentId)
            ->select('student_fees.*', 'users.name as student_name', 'students.admission_number')
            ->paginate($perPage);

        return $this->paginatedResponse($fees, 'Fees retrieved successfully', $departmentId);
    }

    public function summary(Request $request, int $departmentId)
    {
        $summary = DB::table('student_fees')
            ->join('students', 'student_fees.student_id', '=', 'students.id')
            ->where('students.department_id', $departmentId)
            ->select(
                DB::raw('COUNT(DISTINCT student_fees.student_id) as total_students'),
                DB::raw('SUM(student_fees.total_amount) as total_fees'),
                DB::raw('SUM(student_fees.paid_amount) as total_paid'),
                DB::raw('SUM(student_fees.total_amount - student_fees.paid_amount) as total_pending')
            )
            ->first();

        return $this->successResponse($summary, 'Fee summary generated successfully', $departmentId);
    }
}
