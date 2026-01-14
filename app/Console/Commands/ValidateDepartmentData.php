<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ValidateDepartmentData extends Command
{
    protected $signature = 'department:validate
                            {--export : Export validation report to CSV}';

    protected $description = 'Validate department data population and generate compliance report';

    private $results = [];
    private $criticalIssues = 0;

    public function handle()
    {
        $this->info('🔍 Starting department data validation...');
        $this->newLine();

        $this->validateStudents();
        $this->validateAttendance();
        $this->validateResults();
        $this->validateFees();
        $this->validateSubjects();
        $this->validateLessonPlans();
        $this->validateUserDepartments();
        $this->validatePermissions();

        $this->newLine();
        $this->displaySummary();

        if ($this->option('export')) {
            $this->exportReport();
        }

        return $this->criticalIssues === 0 ? 0 : 1;
    }

    private function validateStudents()
    {
        $this->info('📋 Validating students...');

        $total = DB::table('students')->count();
        $withDept = DB::table('students')->whereNotNull('department_id')->count();
        $withoutDept = $total - $withDept;
        $percentage = $total > 0 ? round(($withDept / $total) * 100, 2) : 0;

        $status = $percentage >= 95 ? '✅' : '❌';
        if ($percentage < 95) $this->criticalIssues++;

        $this->results[] = [
            'entity' => 'Students',
            'total' => $total,
            'assigned' => $withDept,
            'unassigned' => $withoutDept,
            'percentage' => $percentage,
            'status' => $status
        ];

        $this->info("  Total: {$total} | Assigned: {$withDept} | Coverage: {$percentage}% {$status}");
    }

    private function validateAttendance()
    {
        $this->info('📋 Validating attendance...');

        $total = DB::table('attendance')->count();
        $withDept = DB::table('attendance')->whereNotNull('department_id')->count();
        $withoutDept = $total - $withDept;
        $percentage = $total > 0 ? round(($withDept / $total) * 100, 2) : 0;

        $status = $percentage >= 95 ? '✅' : '❌';
        if ($percentage < 95) $this->criticalIssues++;

        $this->results[] = [
            'entity' => 'Attendance',
            'total' => $total,
            'assigned' => $withDept,
            'unassigned' => $withoutDept,
            'percentage' => $percentage,
            'status' => $status
        ];

        $this->info("  Total: {$total} | Assigned: {$withDept} | Coverage: {$percentage}% {$status}");
    }

    private function validateResults()
    {
        $this->info('📋 Validating results...');

        $total = DB::table('results')->count();
        $withDept = DB::table('results')->whereNotNull('department_id')->count();
        $withoutDept = $total - $withDept;
        $percentage = $total > 0 ? round(($withDept / $total) * 100, 2) : 0;

        $status = $percentage >= 95 ? '✅' : '❌';
        if ($percentage < 95) $this->criticalIssues++;

        $this->results[] = [
            'entity' => 'Results',
            'total' => $total,
            'assigned' => $withDept,
            'unassigned' => $withoutDept,
            'percentage' => $percentage,
            'status' => $status
        ];

        $this->info("  Total: {$total} | Assigned: {$withDept} | Coverage: {$percentage}% {$status}");
    }

    private function validateFees()
    {
        $this->info('📋 Validating fees...');

        $total = DB::table('fees')->count();
        $withDept = DB::table('fees')->whereNotNull('department_id')->count();
        $withoutDept = $total - $withDept;
        $percentage = $total > 0 ? round(($withDept / $total) * 100, 2) : 0;

        $status = $percentage >= 95 ? '✅' : '❌';
        if ($percentage < 95) $this->criticalIssues++;

        $this->results[] = [
            'entity' => 'Fees',
            'total' => $total,
            'assigned' => $withDept,
            'unassigned' => $withoutDept,
            'percentage' => $percentage,
            'status' => $status
        ];

        $this->info("  Total: {$total} | Assigned: {$withDept} | Coverage: {$percentage}% {$status}");
    }

    private function validateSubjects()
    {
        $this->info('📋 Validating subjects...');

        $total = DB::table('subjects')->count();
        $withDept = DB::table('subjects')->whereNotNull('department_id')->count();
        $withoutDept = $total - $withDept;
        $percentage = $total > 0 ? round(($withDept / $total) * 100, 2) : 0;

        $status = $percentage >= 95 ? '✅' : '❌';
        if ($percentage < 95) $this->criticalIssues++;

        $this->results[] = [
            'entity' => 'Subjects',
            'total' => $total,
            'assigned' => $withDept,
            'unassigned' => $withoutDept,
            'percentage' => $percentage,
            'status' => $status
        ];

        $this->info("  Total: {$total} | Assigned: {$withDept} | Coverage: {$percentage}% {$status}");
    }

    private function validateLessonPlans()
    {
        $this->info('📋 Validating lesson plans...');

        $total = DB::table('lesson_plans')->count();
        $withDept = DB::table('lesson_plans')->whereNotNull('department_id')->count();
        $withoutDept = $total - $withDept;
        $percentage = $total > 0 ? round(($withDept / $total) * 100, 2) : 0;

        $status = $percentage >= 95 ? '✅' : '❌';
        if ($percentage < 95) $this->criticalIssues++;

        $this->results[] = [
            'entity' => 'Lesson Plans',
            'total' => $total,
            'assigned' => $withDept,
            'unassigned' => $withoutDept,
            'percentage' => $percentage,
            'status' => $status
        ];

        $this->info("  Total: {$total} | Assigned: {$withDept} | Coverage: {$percentage}% {$status}");
    }

    private function validateUserDepartments()
    {
        $this->info('📋 Validating user-department relationships...');

        $totalUsers = DB::table('users')->count();
        $assignedUsers = DB::table('users')
            ->join('user_departments', 'users.id', '=', 'user_departments.user_id')
            ->distinct('users.id')
            ->count();
        $unassignedUsers = $totalUsers - $assignedUsers;
        $percentage = $totalUsers > 0 ? round(($assignedUsers / $totalUsers) * 100, 2) : 0;

        $status = $percentage >= 95 ? '✅' : '❌';
        if ($percentage < 95) $this->criticalIssues++;

        $this->results[] = [
            'entity' => 'User Departments',
            'total' => $totalUsers,
            'assigned' => $assignedUsers,
            'unassigned' => $unassignedUsers,
            'percentage' => $percentage,
            'status' => $status
        ];

        $this->info("  Total: {$totalUsers} | Assigned: {$assignedUsers} | Coverage: {$percentage}% {$status}");
    }

    private function validatePermissions()
    {
        $this->info('📋 Validating department permissions...');

        $departments = DB::table('departments')->count();
        $roles = ['super-admin', 'principal', 'department_head', 'registrar', 'faculty', 'student'];
        $modules = ['students', 'attendance', 'results', 'fees', 'lesson_plans', 'reports'];
        
        $expectedPermissions = $departments * count($roles) * count($modules);
        $actualPermissions = DB::table('department_permissions')->count();
        $percentage = $expectedPermissions > 0 ? round(($actualPermissions / $expectedPermissions) * 100, 2) : 0;

        $status = $percentage >= 95 ? '✅' : '❌';
        if ($percentage < 95) $this->criticalIssues++;

        $this->results[] = [
            'entity' => 'Permissions',
            'total' => $expectedPermissions,
            'assigned' => $actualPermissions,
            'unassigned' => $expectedPermissions - $actualPermissions,
            'percentage' => $percentage,
            'status' => $status
        ];

        $this->info("  Expected: {$expectedPermissions} | Actual: {$actualPermissions} | Coverage: {$percentage}% {$status}");
    }

    private function displaySummary()
    {
        $this->table(
            ['Entity', 'Total', 'Assigned', 'Unassigned', 'Coverage %', 'Status'],
            collect($this->results)->map(fn($r) => [
                $r['entity'],
                $r['total'],
                $r['assigned'],
                $r['unassigned'],
                $r['percentage'] . '%',
                $r['status']
            ])->toArray()
        );

        $this->newLine();
        
        if ($this->criticalIssues === 0) {
            $this->info('✅ All validation checks passed! System ready for workflow configuration.');
        } else {
            $this->error("❌ {$this->criticalIssues} critical issue(s) found. Please address before proceeding.");
            $this->warn('Run backfill commands to resolve missing department assignments.');
        }
    }

    private function exportReport()
    {
        $filename = storage_path('app/department_validation_' . date('Y-m-d_His') . '.csv');
        
        $fp = fopen($filename, 'w');
        fputcsv($fp, ['Entity', 'Total', 'Assigned', 'Unassigned', 'Coverage %', 'Status', 'Timestamp']);
        
        foreach ($this->results as $result) {
            fputcsv($fp, [
                $result['entity'],
                $result['total'],
                $result['assigned'],
                $result['unassigned'],
                $result['percentage'],
                $result['status'] === '✅' ? 'PASS' : 'FAIL',
                now()->toDateTimeString()
            ]);
        }
        
        fclose($fp);

        $this->info("📄 Validation report exported to: {$filename}");
    }
}
