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
        $perPage = $request->get('per_page', 15);
        $academicYear = $request->get('academic_year');
        $semester = $request->get('semester');
        
        $query = DB::table('exam_results')
            ->join('students', 'exam_results.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->where('students.department_id', $departmentId);
        
        if ($academicYear) {
            $query->where('exam_results.academic_year', $academicYear);
        }
        
        if ($semester) {
            $query->where('exam_results.semester', $semester);
        }
        
        $results = $query->select('exam_results.*', 'users.name as student_name', 'students.admission_number')
            ->paginate($perPage);

        return $this->paginatedResponse($results, 'Results retrieved successfully', $departmentId);
    }
}
