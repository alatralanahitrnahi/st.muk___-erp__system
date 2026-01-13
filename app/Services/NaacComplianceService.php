<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class NaacComplianceService
{
    public function generateAnnualReport($academicYear)
    {
        return Cache::remember("naac_annual_report_{$academicYear}", 7200, function () use ($academicYear) {
            return [
                'institution_info' => $this->getInstitutionInfo(),
                'student_statistics' => $this->getStudentStatistics($academicYear),
                'faculty_statistics' => $this->getFacultyStatistics($academicYear),
                'academic_performance' => $this->getAcademicPerformance($academicYear),
                'infrastructure' => $this->getInfrastructureData(),
                'financial_summary' => $this->getFinancialSummary($academicYear),
                'quality_indicators' => $this->getQualityIndicators($academicYear),
            ];
        });
    }

    private function getInstitutionInfo()
    {
        return [
            'name' => 'PVG\'s College of Science & Commerce',
            'type' => 'Affiliated College',
            'university' => 'University of Mumbai',
            'establishment_year' => '1985',
            'naac_grade' => 'A',
            'accreditation_year' => '2020',
            'location' => 'Mumbai, Maharashtra',
        ];
    }

    private function getStudentStatistics($academicYear)
    {
        $totalStudents = DB::table('students')
            ->join('exam_results', 'students.id', '=', 'exam_results.student_id')
            ->where('exam_results.academic_year', $academicYear)
            ->distinct('students.id')
            ->count();

        $categoryWise = DB::table('students')
            ->join('categories', 'students.category_id', '=', 'categories.id')
            ->join('exam_results', 'students.id', '=', 'exam_results.student_id')
            ->where('exam_results.academic_year', $academicYear)
            ->select('categories.name', DB::raw('COUNT(DISTINCT students.id) as count'))
            ->groupBy('categories.name')
            ->get();

        $programWise = DB::table('students')
            ->join('programs', 'students.program_id', '=', 'programs.id')
            ->join('exam_results', 'students.id', '=', 'exam_results.student_id')
            ->where('exam_results.academic_year', $academicYear)
            ->select('programs.name', 'programs.level', DB::raw('COUNT(DISTINCT students.id) as count'))
            ->groupBy('programs.name', 'programs.level')
            ->get();

        return [
            'total_enrollment' => $totalStudents,
            'category_wise_enrollment' => $categoryWise,
            'program_wise_enrollment' => $programWise,
            'dropout_rate' => $this->calculateDropoutRate($academicYear),
            'completion_rate' => $this->calculateCompletionRate($academicYear),
        ];
    }

    private function getFacultyStatistics($academicYear)
    {
        $totalFaculty = DB::table('users')
            ->where('user_type', 'faculty')
            ->where('is_active', true)
            ->count();

        return [
            'total_faculty' => $totalFaculty,
            'student_faculty_ratio' => $this->calculateStudentFacultyRatio($academicYear),
            'qualification_wise' => [
                'phd' => round($totalFaculty * 0.3), // Mock data
                'masters' => round($totalFaculty * 0.7),
            ],
            'experience_wise' => [
                'above_10_years' => round($totalFaculty * 0.4),
                '5_to_10_years' => round($totalFaculty * 0.35),
                'below_5_years' => round($totalFaculty * 0.25),
            ],
        ];
    }

    private function getAcademicPerformance($academicYear)
    {
        $passPercentage = DB::table('exam_results')
            ->where('academic_year', $academicYear)
            ->selectRaw('AVG(CASE WHEN result = "pass" THEN 100 ELSE 0 END) as pass_rate')
            ->value('pass_rate');

        $averageMarks = DB::table('exam_results')
            ->where('academic_year', $academicYear)
            ->avg('percentage');

        $gradeDistribution = DB::table('exam_results')
            ->where('academic_year', $academicYear)
            ->select('grade', DB::raw('COUNT(*) as count'))
            ->groupBy('grade')
            ->get();

        return [
            'overall_pass_percentage' => round($passPercentage, 2),
            'average_percentage' => round($averageMarks, 2),
            'grade_distribution' => $gradeDistribution,
            'attendance_percentage' => $this->getAverageAttendance($academicYear),
        ];
    }

    private function getInfrastructureData()
    {
        return [
            'classrooms' => 25,
            'laboratories' => 8,
            'library_books' => 15000,
            'computer_systems' => 120,
            'internet_connectivity' => 'High Speed Broadband',
            'sports_facilities' => 'Available',
            'hostel_capacity' => 200,
        ];
    }

    private function getFinancialSummary($academicYear)
    {
        $totalFeeCollection = DB::table('fee_payments')
            ->whereYear('payment_date', substr($academicYear, 0, 4))
            ->sum('amount');

        $pendingFees = DB::table('student_fees')
            ->sum('balance_amount');

        return [
            'total_fee_collection' => $totalFeeCollection,
            'pending_fees' => $pendingFees,
            'collection_efficiency' => round(($totalFeeCollection / ($totalFeeCollection + $pendingFees)) * 100, 2),
            'scholarship_amount' => $totalFeeCollection * 0.15, // Mock 15% scholarship
        ];
    }

    private function getQualityIndicators($academicYear)
    {
        return [
            'student_satisfaction_index' => 4.2, // Out of 5
            'employer_satisfaction_index' => 4.0,
            'alumni_feedback_score' => 4.1,
            'placement_percentage' => 85,
            'research_publications' => 12,
            'industry_collaborations' => 5,
            'community_service_hours' => 2400,
        ];
    }

    private function calculateDropoutRate($academicYear)
    {
        // Mock calculation - in real scenario, track student status changes
        return 5.2; // 5.2% dropout rate
    }

    private function calculateCompletionRate($academicYear)
    {
        // Mock calculation - track students who completed their programs
        return 94.8; // 94.8% completion rate
    }

    private function calculateStudentFacultyRatio($academicYear)
    {
        $totalStudents = DB::table('students')
            ->join('exam_results', 'students.id', '=', 'exam_results.student_id')
            ->where('exam_results.academic_year', $academicYear)
            ->distinct('students.id')
            ->count();

        $totalFaculty = DB::table('users')
            ->where('user_type', 'faculty')
            ->where('is_active', true)
            ->count();

        return $totalFaculty > 0 ? round($totalStudents / $totalFaculty, 1) : 0;
    }

    private function getAverageAttendance($academicYear)
    {
        return DB::table('attendance_records')
            ->whereYear('attendance_date', substr($academicYear, 0, 4))
            ->selectRaw('AVG(CASE WHEN status = "present" THEN 100 ELSE 0 END) as avg_attendance')
            ->value('avg_attendance') ?? 0;
    }

    public function exportNaacReport($academicYear, $format = 'json')
    {
        $report = $this->generateAnnualReport($academicYear);
        
        switch ($format) {
            case 'csv':
                return $this->exportToCsv($report);
            case 'excel':
                return $this->exportToExcel($report);
            default:
                return $report;
        }
    }

    private function exportToCsv($report)
    {
        $csv = "NAAC Compliance Report\n\n";
        
        foreach ($report as $section => $data) {
            $csv .= strtoupper(str_replace('_', ' ', $section)) . "\n";
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    $csv .= "$key," . (is_array($value) ? json_encode($value) : $value) . "\n";
                }
            }
            $csv .= "\n";
        }
        
        return $csv;
    }

    private function exportToExcel($report)
    {
        // Mock Excel export - in real implementation, use PhpSpreadsheet
        return [
            'format' => 'excel',
            'data' => $report,
            'filename' => 'naac_report_' . date('Y-m-d') . '.xlsx'
        ];
    }
}