<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ImportManualAssignments extends Command
{
    protected $signature = 'department:import-manual
                            {type : Type of import (users, subjects, students)}
                            {file : Path to CSV file}
                            {--dry-run : Preview changes without applying}';

    protected $description = 'Import manual department assignments from CSV files';

    public function handle()
    {
        $type = $this->argument('type');
        $file = $this->argument('file');
        $dryRun = $this->option('dry-run');

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be applied');
        }

        $this->info("🚀 Importing {$type} assignments from {$file}...");

        $method = 'import' . ucfirst($type);
        
        if (!method_exists($this, $method)) {
            $this->error("Invalid import type: {$type}");
            $this->info("Valid types: users, subjects, students");
            return 1;
        }

        return $this->$method($file, $dryRun);
    }

    private function importUsers(string $file, bool $dryRun)
    {
        $rows = $this->parseCsv($file);
        $imported = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $validator = Validator::make($row, [
                'user_id' => 'required|exists:users,id',
                'department_id' => 'required|exists:departments,id',
                'role_in_department' => 'required|in:super-admin,principal,department_head,registrar,faculty,student',
                'is_primary' => 'required|in:yes,no'
            ]);

            if ($validator->fails()) {
                $errors[] = "Row " . ($index + 2) . ": " . implode(', ', $validator->errors()->all());
                continue;
            }

            if (!$dryRun) {
                DB::table('user_departments')->insertOrIgnore([
                    'user_id' => $row['user_id'],
                    'department_id' => $row['department_id'],
                    'role_in_department' => $row['role_in_department'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                if ($row['is_primary'] === 'yes') {
                    DB::table('users')
                        ->where('id', $row['user_id'])
                        ->update(['primary_department_id' => $row['department_id']]);
                }
            }

            $imported++;
            $this->info("  ✓ User {$row['user_id']} assigned to department {$row['department_id']}");
        }

        $this->displayImportSummary($imported, $errors);
        return count($errors) > 0 ? 1 : 0;
    }

    private function importSubjects(string $file, bool $dryRun)
    {
        $rows = $this->parseCsv($file);
        $imported = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $validator = Validator::make($row, [
                'subject_id' => 'required|exists:subjects,id',
                'department_id' => 'required|exists:departments,id'
            ]);

            if ($validator->fails()) {
                $errors[] = "Row " . ($index + 2) . ": " . implode(', ', $validator->errors()->all());
                continue;
            }

            if (!$dryRun) {
                DB::table('subjects')
                    ->where('id', $row['subject_id'])
                    ->update(['department_id' => $row['department_id']]);
            }

            $imported++;
            $this->info("  ✓ Subject {$row['subject_id']} assigned to department {$row['department_id']}");
        }

        $this->displayImportSummary($imported, $errors);
        return count($errors) > 0 ? 1 : 0;
    }

    private function importStudents(string $file, bool $dryRun)
    {
        $rows = $this->parseCsv($file);
        $imported = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $validator = Validator::make($row, [
                'student_id' => 'required|exists:students,id',
                'department_id' => 'required|exists:departments,id'
            ]);

            if ($validator->fails()) {
                $errors[] = "Row " . ($index + 2) . ": " . implode(', ', $validator->errors()->all());
                continue;
            }

            if (!$dryRun) {
                DB::table('students')
                    ->where('id', $row['student_id'])
                    ->update(['department_id' => $row['department_id']]);
            }

            $imported++;
            $this->info("  ✓ Student {$row['student_id']} assigned to department {$row['department_id']}");
        }

        $this->displayImportSummary($imported, $errors);
        return count($errors) > 0 ? 1 : 0;
    }

    private function parseCsv(string $file): array
    {
        $rows = [];
        $handle = fopen($file, 'r');
        $headers = fgetcsv($handle);

        while (($data = fgetcsv($handle)) !== false) {
            if (empty($data[0]) || str_starts_with($data[0], '#')) {
                continue;
            }
            $rows[] = array_combine($headers, $data);
        }

        fclose($handle);
        return $rows;
    }

    private function displayImportSummary(int $imported, array $errors)
    {
        $this->newLine();
        $this->info("✅ Successfully imported: {$imported} records");

        if (count($errors) > 0) {
            $this->error("❌ Errors: " . count($errors));
            foreach ($errors as $error) {
                $this->warn("  - {$error}");
            }
        }
    }
}
