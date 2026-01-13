<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'sqlite',
    'database' => __DIR__ . '/database/database.sqlite',
    'prefix' => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "=== Database Performance Optimization ===\n\n";

// Create indexes for better query performance
$indexes = [
    // Users table indexes
    "CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)",
    "CREATE INDEX IF NOT EXISTS idx_users_type ON users(user_type)",
    "CREATE INDEX IF NOT EXISTS idx_users_active ON users(is_active)",
    
    // Students table indexes
    "CREATE INDEX IF NOT EXISTS idx_students_user_id ON students(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_students_program_id ON students(program_id)",
    "CREATE INDEX IF NOT EXISTS idx_students_status ON students(application_status)",
    "CREATE INDEX IF NOT EXISTS idx_students_admission_no ON students(admission_number)",
    
    // Attendance indexes
    "CREATE INDEX IF NOT EXISTS idx_attendance_student_id ON attendance_records(student_id)",
    "CREATE INDEX IF NOT EXISTS idx_attendance_subject_id ON attendance_records(subject_id)",
    "CREATE INDEX IF NOT EXISTS idx_attendance_date ON attendance_records(attendance_date)",
    "CREATE INDEX IF NOT EXISTS idx_attendance_composite ON attendance_records(student_id, subject_id, attendance_date)",
    
    // Fee management indexes
    "CREATE INDEX IF NOT EXISTS idx_student_fees_student_id ON student_fees(student_id)",
    "CREATE INDEX IF NOT EXISTS idx_student_fees_status ON student_fees(status)",
    "CREATE INDEX IF NOT EXISTS idx_fee_payments_student_fee_id ON fee_payments(student_fee_id)",
    "CREATE INDEX IF NOT EXISTS idx_fee_payments_date ON fee_payments(payment_date)",
    
    // Exam results indexes
    "CREATE INDEX IF NOT EXISTS idx_exam_results_student_id ON exam_results(student_id)",
    "CREATE INDEX IF NOT EXISTS idx_exam_results_subject_id ON exam_results(subject_id)",
    "CREATE INDEX IF NOT EXISTS idx_exam_results_academic_year ON exam_results(academic_year)",
    "CREATE INDEX IF NOT EXISTS idx_exam_results_semester ON exam_results(semester)",
    
    // Programs and subjects indexes
    "CREATE INDEX IF NOT EXISTS idx_programs_department_id ON programs(department_id)",
    "CREATE INDEX IF NOT EXISTS idx_programs_active ON programs(is_active)",
    "CREATE INDEX IF NOT EXISTS idx_subjects_program_id ON subjects(program_id)",
    "CREATE INDEX IF NOT EXISTS idx_subjects_semester ON subjects(semester)",
];

echo "Creating database indexes...\n";
foreach ($indexes as $index) {
    try {
        Capsule::statement($index);
        echo "✓ " . substr($index, 0, 50) . "...\n";
    } catch (Exception $e) {
        echo "✗ Error creating index: " . $e->getMessage() . "\n";
    }
}

// Analyze tables for query optimization
echo "\nAnalyzing tables for optimization...\n";
$tables = ['users', 'students', 'attendance_records', 'student_fees', 'fee_payments', 'exam_results'];
foreach ($tables as $table) {
    try {
        Capsule::statement("ANALYZE $table");
        echo "✓ Analyzed $table\n";
    } catch (Exception $e) {
        echo "✗ Error analyzing $table\n";
    }
}

echo "\n✅ Database optimization complete!\n";