<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GovernmentReportingService
{
    public function generateAisheReport($academicYear)
    {
        // All India Survey on Higher Education (AISHE) Report
        return [
            'basic_info' => $this->getAisheBasicInfo(),
            'enrollment_data' => $this->getEnrollmentData($academicYear),
            'faculty_data' => $this->getFacultyData(),
            'infrastructure_data' => $this->getInfrastructureData(),
            'financial_data' => $this->getFinancialData($academicYear),
            'examination_results' => $this->getExaminationResults($academicYear),
        ];
    }

    public function generateUgcReport($academicYear)
    {
        // University Grants Commission Report
        return [
            'institution_profile' => $this->getInstitutionProfile(),
            'academic_programs' => $this->getAcademicPrograms(),
            'student_enrollment' => $this->getStudentEnrollment($academicYear),
            'faculty_profile' => $this->getFacultyProfile(),
            'research_activities' => $this->getResearchActivities($academicYear),
            'extension_activities' => $this->getExtensionActivities($academicYear),
        ];
    }

    public function generateStateReport($academicYear)
    {
        // Maharashtra State Education Department Report
        return [
            'college_details' => $this->getCollegeDetails(),
            'student_statistics' => $this->getStateStudentStatistics($academicYear),
            'fee_structure' => $this->getFeeStructureReport(),
            'scholarship_data' => $this->getScholarshipData($academicYear),
            'placement_data' => $this->getPlacementData($academicYear),
        ];
    }

    private function getAisheBasicInfo()
    {
        return [
            'college_code' => 'PVGS001',
            'college_name' => 'PVG\'s College of Science & Commerce',
            'university_code' => 'MU001',
            'university_name' => 'University of Mumbai',
            'state' => 'Maharashtra',
            'district' => 'Mumbai',
            'college_type' => 'Affiliated',
            'establishment_year' => 1985,
            'recognition_date' => '1985-07-15',
        ];
    }

    private function getEnrollmentData($academicYear)
    {
        $enrollmentByLevel = DB::table('students')
            ->join('programs', 'students.program_id', '=', 'programs.id')
            ->join('exam_results', 'students.id', '=', 'exam_results.student_id')
            ->where('exam_results.academic_year', $academicYear)
            ->select('programs.level', DB::raw('COUNT(DISTINCT students.id) as count'))
            ->groupBy('programs.level')
            ->get();

        $enrollmentByGender = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->join('exam_results', 'students.id', '=', 'exam_results.student_id')
            ->where('exam_results.academic_year', $academicYear)
            ->select(
                DB::raw('CASE WHEN users.name LIKE "%a" OR users.name LIKE "%i" THEN "Female" ELSE "Male" END as gender'),
                DB::raw('COUNT(DISTINCT students.id) as count')
            )
            ->groupBy('gender')
            ->get();

        return [
            'by_level' => $enrollmentByLevel,
            'by_gender' => $enrollmentByGender,
            'total_enrollment' => $enrollmentByLevel->sum('count'),
        ];
    }

    private function getFacultyData()
    {
        return [
            'total_faculty' => DB::table('users')->where('user_type', 'faculty')->where('is_active', true)->count(),
            'permanent_faculty' => DB::table('users')->where('user_type', 'faculty')->where('is_active', true)->count() * 0.8,
            'temporary_faculty' => DB::table('users')->where('user_type', 'faculty')->where('is_active', true)->count() * 0.2,
            'phd_holders' => DB::table('users')->where('user_type', 'faculty')->where('is_active', true)->count() * 0.3,
        ];
    }

    private function getInstitutionProfile()
    {
        return [
            'naac_accreditation' => 'A Grade',
            'naac_score' => 3.2,
            'autonomy_status' => 'Non-Autonomous',
            'minority_status' => 'No',
            'coeducational' => 'Yes',
            'residential_facility' => 'Yes',
        ];
    }

    private function getAcademicPrograms()
    {
        return DB::table('programs')
            ->join('departments', 'programs.department_id', '=', 'departments.id')
            ->where('programs.is_active', true)
            ->select(
                'programs.name',
                'programs.level',
                'programs.duration_years',
                'departments.name as department',
                'programs.total_semesters'
            )
            ->get();
    }

    private function getStudentEnrollment($academicYear)
    {
        return DB::table('students')
            ->join('categories', 'students.category_id', '=', 'categories.id')
            ->join('programs', 'students.program_id', '=', 'programs.id')
            ->join('exam_results', 'students.id', '=', 'exam_results.student_id')
            ->where('exam_results.academic_year', $academicYear)
            ->select(
                'categories.name as category',
                'programs.level',
                DB::raw('COUNT(DISTINCT students.id) as count')
            )
            ->groupBy('categories.name', 'programs.level')
            ->get();
    }

    private function getFacultyProfile()
    {
        $totalFaculty = DB::table('users')->where('user_type', 'faculty')->where('is_active', true)->count();
        
        return [
            'total_sanctioned_posts' => $totalFaculty + 5, // Mock data
            'total_filled_posts' => $totalFaculty,
            'vacancy_percentage' => round((5 / ($totalFaculty + 5)) * 100, 2),
            'qualification_wise' => [
                'phd' => round($totalFaculty * 0.3),
                'mphil' => round($totalFaculty * 0.2),
                'masters' => round($totalFaculty * 0.5),
            ],
        ];
    }

    private function getResearchActivities($academicYear)
    {
        return [
            'research_projects' => 8,
            'publications' => 15,
            'conferences_attended' => 25,
            'research_grants' => 500000,
            'patents_filed' => 2,
        ];
    }

    private function getExtensionActivities($academicYear)
    {
        return [
            'nss_units' => 2,
            'nss_volunteers' => 120,
            'community_service_hours' => 2400,
            'social_outreach_programs' => 15,
            'environmental_initiatives' => 8,
        ];
    }

    private function getCollegeDetails()
    {
        return [
            'college_code' => 'PVGS001',
            'affiliation_number' => 'MU/AFF/2024/001',
            'recognition_status' => 'Permanent',
            'minority_institution' => 'No',
            'women_college' => 'No',
            'rural_urban' => 'Urban',
        ];
    }

    private function getStateStudentStatistics($academicYear)
    {
        $totalStudents = DB::table('students')
            ->join('exam_results', 'students.id', '=', 'exam_results.student_id')
            ->where('exam_results.academic_year', $academicYear)
            ->distinct('students.id')
            ->count();

        return [
            'total_students' => $totalStudents,
            'maharashtra_domicile' => round($totalStudents * 0.85), // Mock 85% local
            'other_states' => round($totalStudents * 0.15),
            'first_generation_learners' => round($totalStudents * 0.40),
        ];
    }

    private function getFeeStructureReport()
    {
        return DB::table('fee_structures')
            ->join('programs', 'fee_structures.program_id', '=', 'programs.id')
            ->where('fee_structures.is_active', true)
            ->select(
                'programs.name as program',
                'programs.level',
                'fee_structures.tuition_fee',
                'fee_structures.total_fee',
                'fee_structures.academic_year'
            )
            ->get();
    }

    private function getScholarshipData($academicYear)
    {
        $totalStudents = DB::table('students')
            ->join('exam_results', 'students.id', '=', 'exam_results.student_id')
            ->where('exam_results.academic_year', $academicYear)
            ->distinct('students.id')
            ->count();

        return [
            'total_scholarship_recipients' => round($totalStudents * 0.25), // Mock 25%
            'government_scholarships' => round($totalStudents * 0.15),
            'institutional_scholarships' => round($totalStudents * 0.10),
            'total_scholarship_amount' => 2500000, // Mock amount
        ];
    }

    private function getPlacementData($academicYear)
    {
        $graduatingStudents = DB::table('students')
            ->join('programs', 'students.program_id', '=', 'programs.id')
            ->join('exam_results', 'students.id', '=', 'exam_results.student_id')
            ->where('exam_results.academic_year', $academicYear)
            ->where('exam_results.semester', DB::raw('programs.total_semesters'))
            ->distinct('students.id')
            ->count();

        return [
            'total_graduating_students' => $graduatingStudents,
            'students_placed' => round($graduatingStudents * 0.85), // Mock 85% placement
            'average_salary' => 350000, // Mock average salary
            'highest_salary' => 1200000,
            'companies_visited' => 45,
        ];
    }

    public function generateComplianceCalendar()
    {
        return [
            'monthly_reports' => [
                'January' => ['AISHE Data Submission'],
                'March' => ['Annual Financial Report'],
                'June' => ['Academic Year End Report'],
                'September' => ['Mid-Year Progress Report'],
                'December' => ['NAAC Annual Report'],
            ],
            'quarterly_reports' => [
                'Q1' => ['Student Enrollment Report'],
                'Q2' => ['Faculty Performance Report'],
                'Q3' => ['Infrastructure Utilization Report'],
                'Q4' => ['Annual Compliance Summary'],
            ],
            'annual_reports' => [
                'UGC Annual Report',
                'State Education Department Report',
                'NAAC Self-Assessment Report',
                'Audit Report',
            ],
        ];
    }

    public function exportReport($reportType, $academicYear, $format = 'json')
    {
        $report = match($reportType) {
            'aishe' => $this->generateAisheReport($academicYear),
            'ugc' => $this->generateUgcReport($academicYear),
            'state' => $this->generateStateReport($academicYear),
            default => throw new \InvalidArgumentException('Invalid report type')
        };

        return [
            'report_type' => $reportType,
            'academic_year' => $academicYear,
            'generated_at' => Carbon::now()->toISOString(),
            'data' => $report,
            'format' => $format,
        ];
    }
}