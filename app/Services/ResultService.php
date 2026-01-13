<?php

namespace App\Services;

use App\Models\ExamResult;
use App\Models\Student;
use App\Models\Subject;

class ResultService
{
    public function calculateResult(Student $student, array $marks)
    {
        // Logic to calculate result based on marks
        // Implement ATKT/backlog logic here
        $totalMarks = array_sum($marks);
        $subjects = count($marks);
        $percentage = $totalMarks / ($subjects * 100) * 100;

        $result = [
            'student_id' => $student->id,
            'total_marks' => $totalMarks,
            'percentage' => $percentage,
            'status' => $percentage >= 40 ? 'Pass' : 'Fail',
        ];

        return $result;
    }

    public function checkATKT(Student $student)
    {
        // Check if student has backlog subjects
        // Logic to determine ATKT
        $backlogSubjects = []; // Fetch from database

        return count($backlogSubjects) > 0;
    }
}
