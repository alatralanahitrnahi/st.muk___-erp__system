<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignUserDepartments extends Command
{
    protected $signature = 'department:assign-users
                            {--dry-run : Preview changes without applying}
                            {--export-csv : Export unassigned users to CSV}';

    protected $description = 'Assign users to departments based on role and historical data';

    private $stats = [
        'super_admin' => 0,
        'principal' => 0,
        'registrar' => 0,
        'faculty' => 0,
        'student' => 0,
        'unassigned' => 0
    ];

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $exportCsv = $this->option('export-csv');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be applied');
        }

        $this->info('🚀 Starting user-department assignment...');
        $startTime = microtime(true);

        $this->assignSuperAdmins($dryRun);
        $this->assignPrincipals($dryRun);
        $this->assignRegistrars($dryRun);
        $this->assignFaculty($dryRun);
        $this->assignStudents($dryRun);

        if ($exportCsv) {
            $this->exportUnassignedUsers();
        }

        $duration = round(microtime(true) - $startTime, 2);
        
        $this->newLine();
        $this->info("✅ Assignment completed in {$duration}s");
        $this->displayStats();

        return 0;
    }

    private function assignSuperAdmins(bool $dryRun)
    {
        $this->info('📋 Assigning super-admins to all departments...');

        $superAdmins = DB::table('users')->where('role', 'super-admin')->get();
        $departments = DB::table('departments')->pluck('id');

        foreach ($superAdmins as $user) {
            foreach ($departments as $deptId) {
                if (!$dryRun) {
                    DB::table('user_departments')->insertOrIgnore([
                        'user_id' => $user->id,
                        'department_id' => $deptId,
                        'role_in_department' => 'super-admin',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            if (!$dryRun && $departments->isNotEmpty()) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['primary_department_id' => $departments->first()]);
            }

            $this->stats['super_admin']++;
            Log::info('Super-admin assigned to all departments', ['user_id' => $user->id]);
        }

        $this->info("  ✓ Assigned {$this->stats['super_admin']} super-admins");
    }

    private function assignPrincipals(bool $dryRun)
    {
        $this->info('📋 Assigning principals to all departments...');

        $principals = DB::table('users')->where('role', 'principal')->get();
        $departments = DB::table('departments')->pluck('id');

        foreach ($principals as $user) {
            foreach ($departments as $deptId) {
                if (!$dryRun) {
                    DB::table('user_departments')->insertOrIgnore([
                        'user_id' => $user->id,
                        'department_id' => $deptId,
                        'role_in_department' => 'principal',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            if (!$dryRun && $departments->isNotEmpty()) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['primary_department_id' => $departments->first()]);
            }

            $this->stats['principal']++;
            Log::info('Principal assigned to all departments', ['user_id' => $user->id]);
        }

        $this->info("  ✓ Assigned {$this->stats['principal']} principals");
    }

    private function assignRegistrars(bool $dryRun)
    {
        $this->info('📋 Assigning registrars to primary department...');

        $registrars = DB::table('users')->where('role', 'registrar')->get();
        $defaultDept = DB::table('departments')->orderBy('id')->value('id');

        foreach ($registrars as $user) {
            $deptId = $defaultDept;

            if (!$dryRun) {
                DB::table('user_departments')->insertOrIgnore([
                    'user_id' => $user->id,
                    'department_id' => $deptId,
                    'role_in_department' => 'registrar',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['primary_department_id' => $deptId]);
            }

            $this->stats['registrar']++;
            Log::info('Registrar assigned to primary department', [
                'user_id' => $user->id,
                'department_id' => $deptId
            ]);
        }

        $this->info("  ✓ Assigned {$this->stats['registrar']} registrars");
    }

    private function assignFaculty(bool $dryRun)
    {
        $this->info('📋 Assigning faculty based on subject assignments...');

        $faculty = DB::table('users')->where('role', 'faculty')->get();
        $bar = $this->output->createProgressBar($faculty->count());

        foreach ($faculty as $user) {
            $deptIds = DB::table('subject_faculty')
                ->join('subjects', 'subject_faculty.subject_id', '=', 'subjects.id')
                ->where('subject_faculty.faculty_id', $user->id)
                ->whereNotNull('subjects.department_id')
                ->distinct()
                ->pluck('subjects.department_id');

            if ($deptIds->isEmpty()) {
                $this->stats['unassigned']++;
                $bar->advance();
                continue;
            }

            foreach ($deptIds as $deptId) {
                if (!$dryRun) {
                    DB::table('user_departments')->insertOrIgnore([
                        'user_id' => $user->id,
                        'department_id' => $deptId,
                        'role_in_department' => 'faculty',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            if (!$dryRun) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['primary_department_id' => $deptIds->first()]);
            }

            $this->stats['faculty']++;
            Log::info('Faculty assigned to departments', [
                'user_id' => $user->id,
                'department_ids' => $deptIds->toArray()
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("  ✓ Assigned {$this->stats['faculty']} faculty members");
    }

    private function assignStudents(bool $dryRun)
    {
        $this->info('📋 Assigning students based on program enrollment...');

        $students = DB::table('users')->where('role', 'student')->get();
        $bar = $this->output->createProgressBar($students->count());

        foreach ($students as $user) {
            $deptId = DB::table('students')
                ->join('programs', 'students.program_id', '=', 'programs.id')
                ->where('students.user_id', $user->id)
                ->whereNotNull('programs.department_id')
                ->value('programs.department_id');

            if (!$deptId) {
                $this->stats['unassigned']++;
                $bar->advance();
                continue;
            }

            if (!$dryRun) {
                DB::table('user_departments')->insertOrIgnore([
                    'user_id' => $user->id,
                    'department_id' => $deptId,
                    'role_in_department' => 'student',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['primary_department_id' => $deptId]);
            }

            $this->stats['student']++;
            Log::info('Student assigned to department', [
                'user_id' => $user->id,
                'department_id' => $deptId
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("  ✓ Assigned {$this->stats['student']} students");
    }

    private function exportUnassignedUsers()
    {
        $this->info('📄 Exporting unassigned users...');

        $unassigned = DB::table('users')
            ->leftJoin('user_departments', 'users.id', '=', 'user_departments.user_id')
            ->whereNull('user_departments.user_id')
            ->select('users.id', 'users.name', 'users.email', 'users.role')
            ->get();

        if ($unassigned->isEmpty()) {
            $this->info('  ✓ No unassigned users found');
            return;
        }

        $filename = storage_path('app/unassigned_users_' . date('Y-m-d_His') . '.csv');
        
        $fp = fopen($filename, 'w');
        fputcsv($fp, ['user_id', 'name', 'email', 'role', 'suggested_department_id', 'notes']);
        
        foreach ($unassigned as $user) {
            fputcsv($fp, [
                $user->id,
                $user->name,
                $user->email,
                $user->role,
                '', // Manual assignment needed
                'No automatic assignment available'
            ]);
        }
        
        fclose($fp);

        $this->info("  📄 Exported to: {$filename}");
    }

    private function displayStats()
    {
        $this->table(
            ['Role', 'Assigned', 'Status'],
            [
                ['Super Admin', $this->stats['super_admin'], '✅'],
                ['Principal', $this->stats['principal'], '✅'],
                ['Registrar', $this->stats['registrar'], '✅'],
                ['Faculty', $this->stats['faculty'], $this->stats['faculty'] > 0 ? '✅' : '⚠️'],
                ['Student', $this->stats['student'], $this->stats['student'] > 0 ? '✅' : '⚠️'],
                ['Unassigned', $this->stats['unassigned'], $this->stats['unassigned'] > 0 ? '⚠️' : '✅'],
            ]
        );
    }
}
