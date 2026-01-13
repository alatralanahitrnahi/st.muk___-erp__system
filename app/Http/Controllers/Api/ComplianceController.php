<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NaacComplianceService;
use App\Services\GovernmentReportingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ComplianceController extends Controller
{
    protected $naacService;
    protected $govReportService;

    public function __construct(
        NaacComplianceService $naacService,
        GovernmentReportingService $govReportService
    ) {
        $this->naacService = $naacService;
        $this->govReportService = $govReportService;
    }

    public function getNaacReport(Request $request)
    {
        $request->validate([
            'academic_year' => 'required|string',
            'format' => 'nullable|in:json,csv,excel',
        ]);

        $format = $request->format ?? 'json';
        $report = $this->naacService->exportNaacReport($request->academic_year, $format);

        if ($format === 'csv') {
            return Response::make($report, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="naac_report_' . $request->academic_year . '.csv"',
            ]);
        }

        return response()->json($report);
    }

    public function getGovernmentReport(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:aishe,ugc,state',
            'academic_year' => 'required|string',
            'format' => 'nullable|in:json,csv,excel',
        ]);

        $report = $this->govReportService->exportReport(
            $request->report_type,
            $request->academic_year,
            $request->format ?? 'json'
        );

        return response()->json($report);
    }

    public function getComplianceCalendar()
    {
        $calendar = $this->govReportService->generateComplianceCalendar();
        return response()->json($calendar);
    }

    public function getComplianceDashboard()
    {
        $currentYear = date('Y');
        $academicYear = ($currentYear - 1) . '-' . $currentYear;

        $dashboard = [
            'naac_status' => [
                'current_grade' => 'A',
                'accreditation_valid_until' => '2027-03-15',
                'next_assessment_due' => '2026-12-31',
                'compliance_score' => 85.6,
            ],
            'pending_reports' => [
                [
                    'report_name' => 'AISHE Annual Report',
                    'due_date' => '2024-01-31',
                    'status' => 'pending',
                    'priority' => 'high',
                ],
                [
                    'report_name' => 'UGC Quarterly Report',
                    'due_date' => '2024-02-15',
                    'status' => 'in_progress',
                    'priority' => 'medium',
                ],
            ],
            'recent_submissions' => [
                [
                    'report_name' => 'State Education Report',
                    'submitted_date' => '2023-12-15',
                    'status' => 'approved',
                ],
                [
                    'report_name' => 'NAAC Annual Data',
                    'submitted_date' => '2023-12-01',
                    'status' => 'under_review',
                ],
            ],
            'compliance_metrics' => [
                'data_accuracy' => 96.5,
                'timely_submissions' => 92.3,
                'documentation_completeness' => 88.7,
                'overall_compliance' => 92.5,
            ],
        ];

        return response()->json($dashboard);
    }

    public function generateAutomatedReport(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:naac,aishe,ugc,state',
            'academic_year' => 'required|string',
            'schedule' => 'nullable|in:monthly,quarterly,annually',
        ]);

        // Mock automated report generation
        $reportId = 'RPT_' . time();
        
        $automatedReport = [
            'report_id' => $reportId,
            'report_type' => $request->report_type,
            'academic_year' => $request->academic_year,
            'status' => 'generated',
            'generated_at' => now()->toISOString(),
            'file_path' => "/reports/{$reportId}.pdf",
            'schedule' => $request->schedule,
            'next_generation' => $this->getNextGenerationDate($request->schedule),
        ];

        return response()->json([
            'message' => 'Automated report generated successfully',
            'report' => $automatedReport,
        ], 201);
    }

    public function getDataQualityReport()
    {
        $qualityMetrics = [
            'student_data' => [
                'completeness' => 98.5,
                'accuracy' => 96.2,
                'consistency' => 94.8,
                'issues' => [
                    'Missing phone numbers: 12 records',
                    'Invalid email formats: 3 records',
                ],
            ],
            'academic_data' => [
                'completeness' => 99.1,
                'accuracy' => 97.8,
                'consistency' => 96.5,
                'issues' => [
                    'Missing attendance records: 5 days',
                    'Pending result entries: 2 subjects',
                ],
            ],
            'financial_data' => [
                'completeness' => 97.3,
                'accuracy' => 98.9,
                'consistency' => 95.7,
                'issues' => [
                    'Unreconciled payments: ₹15,000',
                    'Missing transaction IDs: 8 records',
                ],
            ],
            'overall_score' => 96.8,
            'recommendations' => [
                'Update missing contact information',
                'Complete pending attendance entries',
                'Reconcile payment discrepancies',
                'Implement automated data validation',
            ],
        ];

        return response()->json($qualityMetrics);
    }

    public function exportComplianceData(Request $request)
    {
        $request->validate([
            'data_type' => 'required|in:all,student,faculty,financial,academic',
            'format' => 'required|in:csv,excel,json',
            'academic_year' => 'nullable|string',
        ]);

        // Mock export functionality
        $exportData = [
            'export_id' => 'EXP_' . time(),
            'data_type' => $request->data_type,
            'format' => $request->format,
            'academic_year' => $request->academic_year ?? 'current',
            'file_size' => '2.5 MB',
            'record_count' => 1250,
            'download_url' => "/exports/compliance_data_{$request->data_type}." . $request->format,
            'expires_at' => now()->addHours(24)->toISOString(),
        ];

        return response()->json([
            'message' => 'Export prepared successfully',
            'export' => $exportData,
        ]);
    }

    private function getNextGenerationDate($schedule)
    {
        return match($schedule) {
            'monthly' => now()->addMonth()->toISOString(),
            'quarterly' => now()->addMonths(3)->toISOString(),
            'annually' => now()->addYear()->toISOString(),
            default => null,
        };
    }
}