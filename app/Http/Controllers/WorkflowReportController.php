<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\WorkflowService;

class WorkflowReportController extends Controller
{
    private $workflowService;

    public function __construct(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    public function departmentWorkflowHistory(Request $request, int $departmentId)
    {
        $workflowName = $request->get('workflow');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $history = DB::table('workflow_history as wh')
            ->join('users as u', 'wh.performed_by', '=', 'u.id')
            ->where('wh.department_id', $departmentId)
            ->when($workflowName, fn($q) => $q->where('wh.workflow_name', $workflowName))
            ->when($dateFrom, fn($q) => $q->where('wh.performed_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('wh.performed_at', '<=', $dateTo))
            ->select(
                'wh.*',
                'u.name as performed_by_name',
                'u.email as performed_by_email'
            )
            ->orderBy('wh.performed_at', 'desc')
            ->paginate(50);

        return response()->json($history);
    }

    public function studentWorkflowHistory(int $studentId)
    {
        $history = DB::table('workflow_history as wh')
            ->join('users as u', 'wh.performed_by', '=', 'u.id')
            ->join('departments as d', 'wh.department_id', '=', 'd.id')
            ->where('wh.entity_type', 'App\\Models\\Student')
            ->where('wh.entity_id', $studentId)
            ->select(
                'wh.*',
                'u.name as performed_by_name',
                'd.name as department_name'
            )
            ->orderBy('wh.performed_at', 'desc')
            ->get();

        return response()->json($history);
    }

    public function naacComplianceReport(Request $request, int $departmentId)
    {
        $academicYear = $request->get('academic_year', date('Y'));

        $report = [
            'department_id' => $departmentId,
            'academic_year' => $academicYear,
            'generated_at' => now(),
            'workflows' => []
        ];

        $workflows = ['student_admission', 'fee_waiver', 'lesson_plan_approval', 'department_transfer'];

        foreach ($workflows as $workflow) {
            $summary = $this->workflowService->getDepartmentWorkflowSummary(
                $departmentId,
                $workflow,
                "{$academicYear}-01-01",
                "{$academicYear}-12-31"
            );

            $report['workflows'][$workflow] = [
                'total_transitions' => $summary['total_transitions'],
                'states' => $summary['by_state'],
                'average_approval_time' => $this->calculateAverageApprovalTime($departmentId, $workflow, $academicYear),
                'pending_approvals' => $this->getPendingApprovals($departmentId, $workflow)
            ];
        }

        return response()->json($report);
    }

    public function principalDashboard(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $dashboard = [
            'pending_approvals' => $this->getPrincipalPendingApprovals(),
            'recent_activities' => $this->getRecentWorkflowActivities($dateFrom, $dateTo),
            'department_summary' => $this->getDepartmentWorkflowSummary($dateFrom, $dateTo),
            'compliance_alerts' => $this->getComplianceAlerts()
        ];

        return response()->json($dashboard);
    }

    private function calculateAverageApprovalTime(int $departmentId, string $workflow, string $year): ?float
    {
        $approvals = DB::table('workflow_history')
            ->where('department_id', $departmentId)
            ->where('workflow_name', $workflow)
            ->whereYear('performed_at', $year)
            ->whereIn('to_state', ['principal_approved', 'hod_approved'])
            ->get();

        if ($approvals->isEmpty()) {
            return null;
        }

        $totalTime = 0;
        $count = 0;

        foreach ($approvals as $approval) {
            $start = DB::table('workflow_history')
                ->where('entity_type', $approval->entity_type)
                ->where('entity_id', $approval->entity_id)
                ->where('from_state', 'pending')
                ->value('performed_at');

            if ($start) {
                $totalTime += strtotime($approval->performed_at) - strtotime($start);
                $count++;
            }
        }

        return $count > 0 ? round($totalTime / $count / 3600, 2) : null; // Hours
    }

    private function getPendingApprovals(int $departmentId, string $workflow): int
    {
        return DB::table('workflow_history')
            ->where('department_id', $departmentId)
            ->where('workflow_name', $workflow)
            ->whereIn('to_state', ['pending', 'registrar_review', 'hod_review', 'submitted'])
            ->distinct('entity_id')
            ->count();
    }

    private function getPrincipalPendingApprovals(): array
    {
        return DB::table('workflow_history as wh')
            ->join('departments as d', 'wh.department_id', '=', 'd.id')
            ->whereIn('wh.to_state', ['hod_approved', 'target_hod_approved'])
            ->select(
                'wh.workflow_name',
                'd.name as department_name',
                DB::raw('count(*) as count')
            )
            ->groupBy('wh.workflow_name', 'd.name')
            ->get()
            ->toArray();
    }

    private function getRecentWorkflowActivities(string $dateFrom, string $dateTo): array
    {
        return DB::table('workflow_history as wh')
            ->join('users as u', 'wh.performed_by', '=', 'u.id')
            ->join('departments as d', 'wh.department_id', '=', 'd.id')
            ->whereBetween('wh.performed_at', [$dateFrom, $dateTo])
            ->select(
                'wh.workflow_name',
                'wh.from_state',
                'wh.to_state',
                'wh.performed_at',
                'u.name as performed_by',
                'd.name as department'
            )
            ->orderBy('wh.performed_at', 'desc')
            ->limit(50)
            ->get()
            ->toArray();
    }

    private function getDepartmentWorkflowSummary(string $dateFrom, string $dateTo): array
    {
        return DB::table('workflow_history as wh')
            ->join('departments as d', 'wh.department_id', '=', 'd.id')
            ->whereBetween('wh.performed_at', [$dateFrom, $dateTo])
            ->select(
                'd.name as department',
                'wh.workflow_name',
                DB::raw('count(*) as total_transitions')
            )
            ->groupBy('d.name', 'wh.workflow_name')
            ->get()
            ->toArray();
    }

    private function getComplianceAlerts(): array
    {
        $alerts = [];

        // Alert: Workflows pending > 7 days
        $longPending = DB::table('workflow_history')
            ->whereIn('to_state', ['pending', 'registrar_review', 'hod_review'])
            ->where('performed_at', '<', now()->subDays(7))
            ->count();

        if ($longPending > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "{$longPending} workflows pending for more than 7 days",
                'action' => 'Review pending approvals'
            ];
        }

        // Alert: Departments without HOD
        $noHod = DB::table('departments')
            ->whereNull('department_head_id')
            ->count();

        if ($noHod > 0) {
            $alerts[] = [
                'type' => 'error',
                'message' => "{$noHod} departments without assigned department head",
                'action' => 'Assign department heads'
            ];
        }

        return $alerts;
    }
}
