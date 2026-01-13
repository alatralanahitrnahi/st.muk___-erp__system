<?php

// Simple test script for admission API
require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;

// Initialize database connection
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'sqlite',
    'database' => __DIR__ . '/database/database.sqlite',
    'prefix' => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Test 1: Check if tables exist
echo "=== Testing Database Tables ===\n";
$tables = ['users', 'students', 'categories', 'programs'];
foreach ($tables as $table) {
    try {
        $count = Capsule::table($table)->count();
        echo "✓ Table '$table' exists with $count records\n";
    } catch (Exception $e) {
        echo "✗ Table '$table' error: " . $e->getMessage() . "\n";
    }
}

// Test 2: Create test categories and programs first
echo "\n=== Creating Test Data ===\n";
try {
    Capsule::table('categories')->insert([
        ['name' => 'General', 'code' => 'GEN', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
    ]);
    echo "✓ Category created\n";

    // First create a department
    $deptId = Capsule::table('departments')->insertGetId([
        'name' => 'Computer Science',
        'code' => 'CS',
        'is_active' => true,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    echo "✓ Department created with ID: $deptId\n";

    Capsule::table('programs')->insert([
        ['name' => 'BSc Computer Science', 'level' => 'UG', 'department_id' => $deptId, 'duration_years' => 3, 'is_active' => true, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
    ]);
    echo "✓ Program created\n";
} catch (Exception $e) {
    echo "✗ Test data creation error: " . $e->getMessage() . "\n";
}

// Test 3: Create a test user
echo "\n=== Testing User Creation ===\n";
$user = null;
try {
    // Use direct database insert to avoid facade issues
    $userId = Capsule::table('users')->insertGetId([
        'name' => 'Test Student',
        'email' => 'test@student.com',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'user_type' => 'student',
        'phone' => '+91 9876543210',
        'is_active' => true,
        'email_verified_at' => null,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
    echo "✓ User created with ID: $userId\n";
    $user = (object)['id' => $userId];
} catch (Exception $e) {
    echo "✗ User creation error: " . $e->getMessage() . "\n";
}

// Test 4: Create a test admission
echo "\n=== Testing Admission Creation ===\n";
try {
    $student = Student::create([
        'user_id' => $user->id,
        'student_id' => 'STU001',
        'first_name' => 'Test',
        'last_name' => 'Student',
        'date_of_birth' => '2000-01-01',
        'gender' => 'male',
        'address' => '123 Test Street, Test City',
        'phone' => '+91 9876543210',
        'emergency_contact' => '+91 9876543211',
        'program_id' => 1, // Assuming program exists
        'category_id' => 1, // Assuming category exists
        'application_status' => 'pending',
        'application_date' => now(),
        'application_fee_paid' => false,
        'parent_name' => 'Test Parent',
        'parent_phone' => '+91 9876543212',
        'previous_school' => 'Test School',
        'previous_percentage' => 85.5,
        'documents' => json_encode([]),
    ]);
    echo "✓ Student admission created with ID: " . $student->id . "\n";
} catch (Exception $e) {
    echo "✗ Student creation error: " . $e->getMessage() . "\n";
}

// Test 5: Test admission retrieval
echo "\n=== Testing Admission Retrieval ===\n";
try {
    $retrievedStudent = Student::with(['user'])->find($student->id);
    if ($retrievedStudent) {
        echo "✓ Student retrieved successfully\n";
        echo "  Name: " . $retrievedStudent->first_name . " " . $retrievedStudent->last_name . "\n";
        echo "  Email: " . $retrievedStudent->user->email . "\n";
        echo "  Status: " . $retrievedStudent->application_status . "\n";
    } else {
        echo "✗ Student not found\n";
    }
} catch (Exception $e) {
    echo "✗ Student retrieval error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Summary ===\n";
echo "Basic admission functionality test completed.\n";
echo "Note: Full API testing requires Laravel application to be running.\n";
