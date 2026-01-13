<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamResult;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    public function getSubjects()
    {
        $subjects = Subject::with('program')->where('is_active', true)->get();
        return response()->json($subjects);
    }

    public function createSubject(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:subjects',
            'program_id' => 'required|exists:programs,id',
            'semester' => 'required|integer|min:1|max:10',
            'credits' => 'required|integer|min:1|max:10',
        ]);

        $subject = Subject::create($request->all());
        return response()->json(['message' => 'Subject created', 'subject' => $subject], 201);
    }

    public function enterResults(Request $request)
    {
        $request->validate([
            'results' => 'required|array',
            'results.*.student_id' => 'required|exists:students,id',
            'results.*.subject_id' => 'required|exists:subjects,id',
            'results.*.exam_type' => 'required|in:internal,external,practical',
            'results.*.marks_obtained' => 'required|numeric|min:0',
            'results.*.max_marks' => 'required|numeric|min:1',
            'academic_year' => 'required|string',
            'semester' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->results as $resultData) {
                $percentage = ($resultData['marks_obtained'] / $resultData['max_marks']) * 100;
                $result = $percentage >= 40 ? 'pass' : 'fail';

                $examResult = ExamResult::updateOrCreate(
                    [
                        'student_id' => $resultData['student_id'],
                        'subject_id' => $resultData['subject_id'],
                        'exam_type' => $resultData['exam_type'],
                        'academic_year' => $request->academic_year,
                        'semester' => $request->semester,
                    ],
                    [
                        'marks_obtained' => $resultData['marks_obtained'],
                        'max_marks' => $resultData['max_marks'],
                        'percentage' => round($percentage, 2),
                        'result' => $result,
                    ]
                );

                // Calculate and update grade
                $examResult->grade = $examResult->calculateGrade();
                $examResult->save();
            }

            DB::commit();
            return response()->json(['message' => 'Results entered successfully'], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to enter results'], 500);
        }
    }

    public function getStudentResults($studentId)
    {
        $results = ExamResult::with(['subject'])
                           ->where('student_id', $studentId)
                           ->orderBy('academic_year', 'desc')
                           ->orderBy('semester', 'desc')
                           ->get();

        $summary = ExamResult::where('student_id', $studentId)
                           ->select('academic_year', 'semester',
                                   DB::raw('AVG(percentage) as average_percentage'),
                                   DB::raw('COUNT(*) as total_subjects'),
                                   DB::raw('SUM(CASE WHEN result = "pass" THEN 1 ELSE 0 END) as passed_subjects'))
                           ->groupBy('academic_year', 'semester')
                           ->get();

        return response()->json(['results' => $results, 'summary' => $summary]);
    }

    public function getResultsReport(Request $request)
    {
        $request->validate([
            'program_id' => 'nullable|exists:programs,id',
            'academic_year' => 'required|string',
            'semester' => 'required|integer',
        ]);

        $query = ExamResult::with(['student.user', 'subject'])
                          ->where('academic_year', $request->academic_year)
                          ->where('semester', $request->semester);

        if ($request->program_id) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('program_id', $request->program_id);
            });
        }

        $results = $query->get();

        $summary = $query->select('student_id',
                                DB::raw('AVG(percentage) as average_percentage'),
                                DB::raw('COUNT(*) as total_subjects'),
                                DB::raw('SUM(CASE WHEN result = "pass" THEN 1 ELSE 0 END) as passed_subjects'))
                        ->groupBy('student_id')
                        ->get();

        return response()->json(['results' => $results, 'summary' => $summary]);
    }
}