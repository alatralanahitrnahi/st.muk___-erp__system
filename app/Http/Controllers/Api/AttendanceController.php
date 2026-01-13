<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function markAttendance(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'attendance_date' => 'required|date',
            'students' => 'required|array',
            'students.*.student_id' => 'required|exists:students,id',
            'students.*.status' => 'required|in:present,absent,late',
            'students.*.check_in_time' => 'nullable|date_format:H:i',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->students as $studentData) {
                AttendanceRecord::updateOrCreate(
                    [
                        'student_id' => $studentData['student_id'],
                        'subject_id' => $request->subject_id,
                        'attendance_date' => $request->attendance_date,
                    ],
                    [
                        'status' => $studentData['status'],
                        'check_in_time' => $studentData['check_in_time'] ?? null,
                        'marked_by' => $request->user()->id,
                        'remarks' => $studentData['remarks'] ?? null,
                    ]
                );
            }

            DB::commit();
            return response()->json(['message' => 'Attendance marked successfully'], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to mark attendance'], 500);
        }
    }

    public function getAttendance(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date',
        ]);

        $attendance = AttendanceRecord::with(['student.user', 'subject'])
                                    ->where('subject_id', $request->subject_id)
                                    ->whereBetween('attendance_date', [$request->date_from, $request->date_to])
                                    ->orderBy('attendance_date')
                                    ->get();

        return response()->json($attendance);
    }

    public function getStudentAttendance($studentId)
    {
        $attendance = AttendanceRecord::with(['subject'])
                                    ->where('student_id', $studentId)
                                    ->orderBy('attendance_date', 'desc')
                                    ->get();

        $summary = AttendanceRecord::where('student_id', $studentId)
                                 ->select('subject_id', 
                                         DB::raw('COUNT(*) as total_classes'),
                                         DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count'),
                                         DB::raw('ROUND((SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2) as percentage'))
                                 ->groupBy('subject_id')
                                 ->with('subject')
                                 ->get();

        return response()->json(['records' => $attendance, 'summary' => $summary]);
    }

    public function getAttendanceReport(Request $request)
    {
        $request->validate([
            'program_id' => 'nullable|exists:programs,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date',
        ]);

        $query = AttendanceRecord::with(['student.user', 'subject'])
                                ->whereBetween('attendance_date', [$request->date_from, $request->date_to]);

        if ($request->subject_id) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->program_id) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('program_id', $request->program_id);
            });
        }

        $report = $query->select('student_id', 'subject_id',
                               DB::raw('COUNT(*) as total_classes'),
                               DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count'),
                               DB::raw('ROUND((SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2) as percentage'))
                       ->groupBy('student_id', 'subject_id')
                       ->get();

        return response()->json($report);
    }
}