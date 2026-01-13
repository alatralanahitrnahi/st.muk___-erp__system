<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\AttendanceRecord;
use App\Models\ExamResult;
use App\Models\FeePayment;
use App\Models\StudentFee;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ReportController extends Controller
{
    protected $cacheService;
    
    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    public function dashboardStats()
    {
        return response()->json($this->cacheService->getDashboardStats());
    }

    public function studentReport(Request $request)
    {
        $cacheKey = 'student_report_' . md5(serialize($request->all()));
        
        $students = Cache::remember($cacheKey, 1800, function () use ($request) {
            $query = Student::with(['user:id,name,email', 'program:id,name', 'category:id,name']);
            
            if ($request->program_id) {
                $query->where('program_id', $request->program_id);
            }
            
            if ($request->status) {
                $query->where('application_status', $request->status);
            }

            return $query->get()->map(function($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'email' => $student->user->email,
                    'admission_number' => $student->admission_number,
                    'program' => $student->program->name,
                    'category' => $student->category->name,
                    'status' => $student->application_status,
                    'admission_date' => $student->admission_date,
                ];
            });
        });

        return response()->json($students);
    }

    public function feeReport(Request $request)
    {
        $cacheKey = 'fee_report_' . md5(serialize($request->all()));
        
        $data = Cache::remember($cacheKey, 900, function () use ($request) {
            $query = StudentFee::with(['student.user:id,name', 'student.program:id,name', 'feeStructure:id']);
            
            if ($request->program_id) {
                $query->whereHas('student', function($q) use ($request) {
                    $q->where('program_id', $request->program_id);
                });
            }

            $fees = $query->get()->map(function($fee) {
                return [
                    'student_name' => $fee->student->user->name,
                    'admission_number' => $fee->student->admission_number,
                    'program' => $fee->student->program->name,
                    'total_amount' => $fee->total_amount,
                    'paid_amount' => $fee->paid_amount,
                    'balance_amount' => $fee->balance_amount,
                    'status' => $fee->status,
                    'payment_percentage' => round(($fee->paid_amount / $fee->final_amount) * 100, 2),
                ];
            });

            $summary = [
                'total_fees' => $fees->sum('total_amount'),
                'collected_fees' => $fees->sum('paid_amount'),
                'pending_fees' => $fees->sum('balance_amount'),
                'collection_percentage' => round(($fees->sum('paid_amount') / $fees->sum('total_amount')) * 100, 2),
            ];

            return ['fees' => $fees, 'summary' => $summary];
        });

        return response()->json($data);
    }

    public function attendanceReport(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'program_id' => 'nullable|exists:programs,id',
        ]);

        $cacheKey = 'attendance_report_' . md5(serialize($request->all()));
        
        $report = Cache::remember($cacheKey, 600, function () use ($request) {
            $query = DB::table('attendance_records')
                ->join('students', 'attendance_records.student_id', '=', 'students.id')
                ->join('users', 'students.user_id', '=', 'users.id')
                ->join('programs', 'students.program_id', '=', 'programs.id')
                ->whereBetween('attendance_records.attendance_date', [$request->date_from, $request->date_to]);

            if ($request->program_id) {
                $query->where('students.program_id', $request->program_id);
            }

            return $query->select(
                'students.id as student_id',
                'users.name as student_name',
                'students.admission_number',
                'programs.name as program',
                DB::raw('COUNT(*) as total_classes'),
                DB::raw('SUM(CASE WHEN attendance_records.status = "present" THEN 1 ELSE 0 END) as present_count'),
                DB::raw('ROUND((SUM(CASE WHEN attendance_records.status = "present" THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2) as percentage')
            )
            ->groupBy('students.id', 'users.name', 'students.admission_number', 'programs.name')
            ->get()
            ->map(function($record) {
                return [
                    'student_name' => $record->student_name,
                    'admission_number' => $record->admission_number,
                    'program' => $record->program,
                    'total_classes' => $record->total_classes,
                    'present_count' => $record->present_count,
                    'absent_count' => $record->total_classes - $record->present_count,
                    'percentage' => $record->percentage,
                    'status' => $record->percentage >= 75 ? 'Good' : ($record->percentage >= 60 ? 'Average' : 'Poor'),
                ];
            });
        });

        return response()->json($report);
    }

    public function resultReport(Request $request)
    {
        $request->validate([
            'academic_year' => 'required|string',
            'semester' => 'required|integer',
            'program_id' => 'nullable|exists:programs,id',
        ]);

        $cacheKey = 'result_report_' . md5(serialize($request->all()));
        
        $report = Cache::remember($cacheKey, 1800, function () use ($request) {
            $query = DB::table('exam_results')
                ->join('students', 'exam_results.student_id', '=', 'students.id')
                ->join('users', 'students.user_id', '=', 'users.id')
                ->join('programs', 'students.program_id', '=', 'programs.id')
                ->where('exam_results.academic_year', $request->academic_year)
                ->where('exam_results.semester', $request->semester);

            if ($request->program_id) {
                $query->where('students.program_id', $request->program_id);
            }

            return $query->select(
                'students.id as student_id',
                'users.name as student_name',
                'students.admission_number',
                'programs.name as program',
                DB::raw('AVG(exam_results.percentage) as average_percentage'),
                DB::raw('COUNT(*) as total_subjects'),
                DB::raw('SUM(CASE WHEN exam_results.result = "pass" THEN 1 ELSE 0 END) as passed_subjects')
            )
            ->groupBy('students.id', 'users.name', 'students.admission_number', 'programs.name')
            ->get()
            ->map(function($result) {
                $passPercentage = ($result->passed_subjects / $result->total_subjects) * 100;
                
                return [
                    'student_name' => $result->student_name,
                    'admission_number' => $result->admission_number,
                    'program' => $result->program,
                    'total_subjects' => $result->total_subjects,
                    'passed_subjects' => $result->passed_subjects,
                    'failed_subjects' => $result->total_subjects - $result->passed_subjects,
                    'average_percentage' => round($result->average_percentage, 2),
                    'pass_percentage' => round($passPercentage, 2),
                    'overall_result' => $passPercentage == 100 ? 'Pass' : 'ATKT',
                    'grade' => $this->calculateOverallGrade($result->average_percentage),
                ];
            });
        });

        return response()->json($report);
    }

    public function naacReport()
    {
        return Cache::remember('naac_report', 3600, function () {
            $currentYear = date('Y');
            
            return [
                'academic_year' => ($currentYear-1) . '-' . $currentYear,
                'total_students' => DB::table('students')->where('status', 'active')->count(),
                'programs_offered' => DB::table('programs')->where('is_active', true)->count(),
                'departments' => DB::table('departments')->where('is_active', true)->count(),
                'faculty_count' => DB::table('users')->where('user_type', 'faculty')->where('is_active', true)->count(),
                'average_attendance' => DB::table('attendance_records')
                    ->selectRaw('AVG(CASE WHEN status = "present" THEN 100 ELSE 0 END) as avg_attendance')
                    ->value('avg_attendance'),
                'pass_percentage' => DB::table('exam_results')
                    ->selectRaw('AVG(CASE WHEN result = "pass" THEN 100 ELSE 0 END) as pass_rate')
                    ->value('pass_rate'),
                'fee_collection_rate' => DB::table('student_fees')
                    ->selectRaw('AVG((paid_amount / final_amount) * 100) as collection_rate')
                    ->value('collection_rate'),
            ];
        });
    }

    private function calculateOverallGrade($percentage)
    {
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 80) return 'A';
        if ($percentage >= 70) return 'B+';
        if ($percentage >= 60) return 'B';
        if ($percentage >= 50) return 'C';
        if ($percentage >= 40) return 'D';
        return 'F';
    }
}