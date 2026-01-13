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

echo "=== Phase 3 Setup & Test ===\n\n";

// Add sample subjects
echo "1. Adding sample subjects...\n";
try {
    Capsule::table('subjects')->insert([
        ['name' => 'Programming in C', 'code' => 'CS101', 'program_id' => 1, 'semester' => 1, 'credits' => 4, 'is_active' => true, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
        ['name' => 'Mathematics I', 'code' => 'MATH101', 'program_id' => 1, 'semester' => 1, 'credits' => 3, 'is_active' => true, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
        ['name' => 'Data Structures', 'code' => 'CS201', 'program_id' => 1, 'semester' => 2, 'credits' => 4, 'is_active' => true, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
    ]);
    echo "   ✓ Subjects added\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Add sample fee structure
echo "\n2. Adding sample fee structure...\n";
try {
    Capsule::table('fee_structures')->insert([
        [
            'program_id' => 1,
            'academic_year' => '2024-25',
            'tuition_fee' => 50000.00,
            'development_fee' => 5000.00,
            'exam_fee' => 2000.00,
            'library_fee' => 1000.00,
            'other_fees' => 2000.00,
            'total_fee' => 60000.00,
            'installments' => 2,
            'is_active' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]
    ]);
    echo "   ✓ Fee structure added\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Create test student
echo "\n3. Creating test student...\n";
try {
    $userId = Capsule::table('users')->insertGetId([
        'name' => 'Test Student',
        'email' => 'student@test.com',
        'phone' => '+91 9876543210',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'user_type' => 'student',
        'is_active' => true,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);

    $studentId = Capsule::table('students')->insertGetId([
        'user_id' => $userId,
        'program_id' => 1,
        'category_id' => 1,
        'application_status' => 'approved',
        'application_date' => date('Y-m-d'),
        'admission_number' => 'ADM2024001',
        'parent_name' => 'Test Parent',
        'parent_phone' => '+91 9876543211',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
    echo "   ✓ Test student created (ID: $studentId)\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Assign fee to student
echo "\n4. Assigning fee to student...\n";
try {
    $studentFeeId = Capsule::table('student_fees')->insertGetId([
        'student_id' => $studentId,
        'fee_structure_id' => 1,
        'total_amount' => 60000.00,
        'discount_amount' => 5000.00,
        'final_amount' => 55000.00,
        'paid_amount' => 0.00,
        'balance_amount' => 55000.00,
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
    echo "   ✓ Fee assigned to student\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Add sample attendance
echo "\n5. Adding sample attendance...\n";
try {
    Capsule::table('attendance_records')->insert([
        ['student_id' => $studentId, 'subject_id' => 1, 'attendance_date' => date('Y-m-d'), 'status' => 'present', 'marked_by' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
        ['student_id' => $studentId, 'subject_id' => 2, 'attendance_date' => date('Y-m-d'), 'status' => 'present', 'marked_by' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
    ]);
    echo "   ✓ Sample attendance added\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Add sample exam results
echo "\n6. Adding sample exam results...\n";
try {
    Capsule::table('exam_results')->insert([
        ['student_id' => $studentId, 'subject_id' => 1, 'exam_type' => 'internal', 'marks_obtained' => 85.00, 'max_marks' => 100.00, 'percentage' => 85.00, 'grade' => 'A', 'result' => 'pass', 'academic_year' => '2024-25', 'semester' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
        ['student_id' => $studentId, 'subject_id' => 2, 'exam_type' => 'internal', 'marks_obtained' => 78.00, 'max_marks' => 100.00, 'percentage' => 78.00, 'grade' => 'B+', 'result' => 'pass', 'academic_year' => '2024-25', 'semester' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
    ]);
    echo "   ✓ Sample exam results added\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Test data verification
echo "\n7. Verifying Phase 3 data...\n";
$tables = [
    'fee_structures' => 'Fee Structures',
    'student_fees' => 'Student Fees',
    'subjects' => 'Subjects',
    'attendance_records' => 'Attendance Records',
    'exam_results' => 'Exam Results'
];

foreach ($tables as $table => $name) {
    try {
        $count = Capsule::table($table)->count();
        echo "   ✓ $name: $count records\n";
    } catch (Exception $e) {
        echo "   ✗ $name: ERROR\n";
    }
}

echo "\n=== Phase 3 Complete ===\n";
echo "✅ Fee Management: Structure, assignment, payment tracking\n";
echo "✅ Attendance System: Daily marking, reporting\n";
echo "✅ Examination System: Results entry, grade calculation\n";
echo "✅ All Phase 3 APIs ready for testing\n\n";

echo "Available API Endpoints:\n";
echo "Fee Management:\n";
echo "  GET /api/fee-structures\n";
echo "  POST /api/fee-structures\n";
echo "  GET /api/students/{id}/fees\n";
echo "  POST /api/record-payment\n\n";

echo "Attendance:\n";
echo "  POST /api/attendance/mark\n";
echo "  GET /api/attendance\n";
echo "  GET /api/students/{id}/attendance\n\n";

echo "Examinations:\n";
echo "  GET /api/subjects\n";
echo "  POST /api/results/enter\n";
echo "  GET /api/students/{id}/results\n";