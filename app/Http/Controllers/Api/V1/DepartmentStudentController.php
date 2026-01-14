<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentStudentController extends Controller
{
    use ApiResponse;

    public function index(Request $request, int $departmentId)
    {
        $perPage = $request->get('per_page', 15);
        
        $students = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->join('programs', 'students.program_id', '=', 'programs.id')
            ->where('students.department_id', $departmentId)
            ->select('students.*', 'users.name', 'users.email', 'programs.name as program_name')
            ->paginate($perPage);

        return $this->paginatedResponse($students, 'Students retrieved successfully', $departmentId);
    }

    public function show(int $departmentId, int $studentId)
    {
        $student = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->join('programs', 'students.program_id', '=', 'programs.id')
            ->where('students.id', $studentId)
            ->where('students.department_id', $departmentId)
            ->select('students.*', 'users.name', 'users.email', 'programs.name as program_name')
            ->first();

        if (!$student) {
            return $this->errorResponse('Student not found', ['student' => ['Student not found in this department']], $departmentId, 404);
        }

        return $this->successResponse($student, 'Student retrieved successfully', $departmentId);
    }
}
