<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackfillDepartmentData extends Command
{
    protected $signature = 'department:backfill
                            {table? : Specific table to backfill (students, attendance, results, fees, subjects, lesson_plans)}
                            {--batch=1000 : Batch size for processing}
                            {--dry-run : Preview changes without applying}';

    protected $description = 'Backfill department_id for existing records using intelligent assignment logic';

    private $stats = [];

    public function handle()
    {
        $table = $this->argument('table');
        $batchSize = (int) $this->option('batch');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be applied');
        }

        $this->info('🚀 Starting department data backfill...');
        $startTime = microtime(true);

        $tables = $table ? [$table] : ['students', 'attendance', 'results', 'fees', 'subjects', 'lesson_plans'];

        foreach ($tables as $tbl) {
            $this->processTable($tbl, $batchSize, $dryRun);
        }

        $duration = round(microtime(true) - $startTime, 2);
        
        $this->newLine();
        $this->info("✅ Backfill completed in {$duration}s");
        $this->displayStats();

        return 0;
    }

    private function processTable(string $table, int $batchSize, bool $dryRun)
    {
        $this->newLine();
        $this->info("📋 Processing table: {$table}");

        $method = 'backfill' . str_replace('_', '', ucwords($table, '_'));
        
        if (!method_exists($this, $method)) {
            $this->error("  ❌ No backfill method for {$table}");
            return;
        }

        try {
            $this->$method($batchSize, $dryRun);
        } catch (\Exception $e) {
            $this->error("  ❌ Error: {$e->getMessage()}");
            Log::error("Department backfill failed for {$table}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function backfillStudents(int $batchSize, bool $dryRun)
    {
        $nullCount = DB::table('students')->whereNull('department_id')->count();
        
        if ($nullCount === 0) {
            $this->info('  ✓ All students already have department_id');
            return;
        }

        $this->info("  Found {$nullCount} students without department_id");
        $bar = $this->output->createProgressBar($nullCount);

        $updated = 0;
        $failed = [];

        DB::table('students')
            ->whereNull('department_id')
            ->orderBy('id')
            ->chunk($batchSize, function ($students) use (&$updated, &$failed, $bar, $dryRun) {
                foreach ($students as $student) {
                    $deptId = DB::table('programs')
                        ->where('id', $student->program_id)
                        ->value('department_id');

                    if ($deptId) {
                        if (!$dryRun) {
                            DB::table('students')
                                ->where('id', $student->id)
                                ->update(['department_id' => $deptId]);
                            
                            Log::info('Student department assigned', [
                                'student_id' => $student->id,
                                'department_id' => $deptId,
                                'program_id' => $student->program_id
                            ]);
                        }
                        $updated++;
                    } else {
                        $failed[] = [
                            'student_id' => $student->id,
                            'program_id' => $student->program_id,
                            'reason' => 'Program has no department_id'
                        ];
                    }
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info("  ✓ Updated: {$updated} students");
        
        if (count($failed) > 0) {
            $this->warn("  ⚠ Failed: " . count($failed) . " students (see CSV export)");
            $this->exportFailures('students', $failed);
        }

        $this->stats['students'] = ['updated' => $updated, 'failed' => count($failed)];
    }

    private function backfillAttendance(int $batchSize, bool $dryRun)
    {
        $nullCount = DB::table('attendance')->whereNull('department_id')->count();
        
        if ($nullCount === 0) {
            $this->info('  ✓ All attendance records already have department_id');
            return;
        }

        $this->info("  Found {$nullCount} attendance records without department_id");
        $bar = $this->output->createProgressBar($nullCount);

        $updated = 0;

        DB::table('attendance')
            ->whereNull('department_id')
            ->orderBy('id')
            ->chunk($batchSize, function ($records) use (&$updated, $bar, $dryRun) {
                foreach ($records as $record) {
                    $deptId = DB::table('students')
                        ->where('id', $record->student_id)
                        ->value('department_id');

                    if ($deptId && !$dryRun) {
                        DB::table('attendance')
                            ->where('id', $record->id)
                            ->update(['department_id' => $deptId]);
                        $updated++;
                    }
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info("  ✓ Updated: {$updated} attendance records");
        $this->stats['attendance'] = ['updated' => $updated, 'failed' => 0];
    }

    private function backfillResults(int $batchSize, bool $dryRun)
    {
        $nullCount = DB::table('results')->whereNull('department_id')->count();
        
        if ($nullCount === 0) {
            $this->info('  ✓ All results already have department_id');
            return;
        }

        $this->info("  Found {$nullCount} results without department_id");
        $bar = $this->output->createProgressBar($nullCount);

        $updated = 0;

        DB::table('results')
            ->whereNull('department_id')
            ->orderBy('id')
            ->chunk($batchSize, function ($records) use (&$updated, $bar, $dryRun) {
                foreach ($records as $record) {
                    $deptId = DB::table('students')
                        ->where('id', $record->id)
                        ->value('department_id');

                    if ($deptId && !$dryRun) {
                        DB::table('results')
                            ->where('id', $record->id)
                            ->update(['department_id' => $deptId]);
                        $updated++;
                    }
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info("  ✓ Updated: {$updated} results");
        $this->stats['results'] = ['updated' => $updated, 'failed' => 0];
    }

    private function backfillFees(int $batchSize, bool $dryRun)
    {
        $nullCount = DB::table('fees')->whereNull('department_id')->count();
        
        if ($nullCount === 0) {
            $this->info('  ✓ All fee records already have department_id');
            return;
        }

        $this->info("  Found {$nullCount} fee records without department_id");
        $bar = $this->output->createProgressBar($nullCount);

        $updated = 0;

        DB::table('fees')
            ->whereNull('department_id')
            ->orderBy('id')
            ->chunk($batchSize, function ($records) use (&$updated, $bar, $dryRun) {
                foreach ($records as $record) {
                    $deptId = DB::table('students')
                        ->where('id', $record->student_id)
                        ->value('department_id');

                    if ($deptId && !$dryRun) {
                        DB::table('fees')
                            ->where('id', $record->id)
                            ->update(['department_id' => $deptId]);
                        $updated++;
                    }
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info("  ✓ Updated: {$updated} fee records");
        $this->stats['fees'] = ['updated' => $updated, 'failed' => 0];
    }

    private function backfillSubjects(int $batchSize, bool $dryRun)
    {
        $nullCount = DB::table('subjects')->whereNull('department_id')->count();
        
        if ($nullCount === 0) {
            $this->info('  ✓ All subjects already have department_id');
            return;
        }

        $this->info("  Found {$nullCount} subjects without department_id");
        $bar = $this->output->createProgressBar($nullCount);

        $updated = 0;
        $failed = [];

        DB::table('subjects')
            ->whereNull('department_id')
            ->orderBy('id')
            ->chunk($batchSize, function ($subjects) use (&$updated, &$failed, $bar, $dryRun) {
                foreach ($subjects as $subject) {
                    $deptId = DB::table('program_subjects')
                        ->join('programs', 'program_subjects.program_id', '=', 'programs.id')
                        ->where('program_subjects.subject_id', $subject->id)
                        ->value('programs.department_id');

                    if ($deptId) {
                        if (!$dryRun) {
                            DB::table('subjects')
                                ->where('id', $subject->id)
                                ->update(['department_id' => $deptId]);
                        }
                        $updated++;
                    } else {
                        $failed[] = [
                            'subject_id' => $subject->id,
                            'subject_code' => $subject->code ?? 'N/A',
                            'reason' => 'No program association found'
                        ];
                    }
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info("  ✓ Updated: {$updated} subjects");
        
        if (count($failed) > 0) {
            $this->warn("  ⚠ Failed: " . count($failed) . " subjects (see CSV export)");
            $this->exportFailures('subjects', $failed);
        }

        $this->stats['subjects'] = ['updated' => $updated, 'failed' => count($failed)];
    }

    private function backfillLessonPlans(int $batchSize, bool $dryRun)
    {
        $nullCount = DB::table('lesson_plans')->whereNull('department_id')->count();
        
        if ($nullCount === 0) {
            $this->info('  ✓ All lesson plans already have department_id');
            return;
        }

        $this->info("  Found {$nullCount} lesson plans without department_id");
        $bar = $this->output->createProgressBar($nullCount);

        $updated = 0;

        DB::table('lesson_plans')
            ->whereNull('department_id')
            ->orderBy('id')
            ->chunk($batchSize, function ($records) use (&$updated, $bar, $dryRun) {
                foreach ($records as $record) {
                    $deptId = DB::table('subjects')
                        ->where('id', $record->subject_id)
                        ->value('department_id');

                    if ($deptId && !$dryRun) {
                        DB::table('lesson_plans')
                            ->where('id', $record->id)
                            ->update(['department_id' => $deptId]);
                        $updated++;
                    }
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info("  ✓ Updated: {$updated} lesson plans");
        $this->stats['lesson_plans'] = ['updated' => $updated, 'failed' => 0];
    }

    private function exportFailures(string $table, array $failures)
    {
        $filename = storage_path("app/department_backfill_{$table}_failures_" . date('Y-m-d_His') . '.csv');
        
        $fp = fopen($filename, 'w');
        if (!empty($failures)) {
            fputcsv($fp, array_keys($failures[0]));
            foreach ($failures as $row) {
                fputcsv($fp, $row);
            }
        }
        fclose($fp);

        $this->info("  📄 Failures exported to: {$filename}");
    }

    private function displayStats()
    {
        $this->table(
            ['Table', 'Updated', 'Failed', 'Status'],
            collect($this->stats)->map(function ($stat, $table) {
                $status = $stat['failed'] === 0 ? '✅' : '⚠️';
                return [$table, $stat['updated'], $stat['failed'], $status];
            })->toArray()
        );
    }
}
