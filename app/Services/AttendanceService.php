<?php

namespace App\Services;

use App\Models\Student;
use App\Models\AttendanceRecord;

class AttendanceService
{
    public function markAttendance(array $attendanceData)
    {
        // Logic to mark attendance for students
        foreach ($attendanceData as $data) {
            AttendanceRecord::create($data);
        }
    }

    public function calculateAttendancePercentage(Student $student)
    {
        // Calculate attendance percentage
        $totalClasses = AttendanceRecord::where('student_id', $student->id)->count();
        $presentClasses = AttendanceRecord::where('student_id', $student->id)
            ->where('status', 'present')
            ->count();

        return $totalClasses > 0 ? ($presentClasses / $totalClasses) * 100 : 0;
    }

    public function getDefaulters()
    {
        // Get students with low attendance
        $defaulters = [];
        // Logic to fetch defaulters
        return $defaulters;
    }
}
