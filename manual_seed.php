<?php

require __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// Bootstrap Laravel without HTTP
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔄 Starting manual database seed...\n\n";

// Clear existing data
echo "Clearing existing data...\n";
DB::statement('PRAGMA foreign_keys = OFF');
DB::table('users')->delete();
DB::table('departments')->delete();
DB::table('programs')->delete();
DB::table('students')->delete();
DB::statement('PRAGMA foreign_keys = ON');

// Create Super Admin
echo "Creating Super Admin...\n";
$adminId = DB::table('users')->insertGetId([
    'name' => 'Super Admin',
    'email' => 'admin@pvgs.edu',
    'password' => Hash::make('password123'),
    'user_type' => 'super-admin',
    'created_at' => now(),
    'updated_at' => now(),
]);

// Create Departments
echo "Creating Departments...\n";
$scienceId = DB::table('departments')->insertGetId([
    'name' => 'Science',
    'code' => 'SCI',
    'created_at' => now(),
    'updated_at' => now(),
]);

$commerceId = DB::table('departments')->insertGetId([
    'name' => 'Commerce',
    'code' => 'COM',
    'created_at' => now(),
    'updated_at' => now(),
]);

$artsId = DB::table('departments')->insertGetId([
    'name' => 'Arts',
    'code' => 'ART',
    'created_at' => now(),
    'updated_at' => now(),
]);

// Create Principal
echo "Creating Principal...\n";
$principalId = DB::table('users')->insertGetId([
    'name' => 'Dr. Principal',
    'email' => 'principal@pvgs.edu',
    'password' => Hash::make('password123'),
    'user_type' => 'principal',
    'created_at' => now(),
    'updated_at' => now(),
]);

// Create HODs
echo "Creating HODs...\n";
$hodScienceId = DB::table('users')->insertGetId([
    'name' => 'HOD Science',
    'email' => 'hod.science@pvgs.edu',
    'password' => Hash::make('password123'),
    'user_type' => 'registrar',
    'department_id' => $scienceId,
    'created_at' => now(),
    'updated_at' => now(),
]);

$hodCommerceId = DB::table('users')->insertGetId([
    'name' => 'HOD Commerce',
    'email' => 'hod.commerce@pvgs.edu',
    'password' => Hash::make('password123'),
    'user_type' => 'registrar',
    'department_id' => $commerceId,
    'created_at' => now(),
    'updated_at' => now(),
]);

$hodArtsId = DB::table('users')->insertGetId([
    'name' => 'HOD Arts',
    'email' => 'hod.arts@pvgs.edu',
    'password' => Hash::make('password123'),
    'user_type' => 'registrar',
    'department_id' => $artsId,
    'created_at' => now(),
    'updated_at' => now(),
]);

// Create Programs
echo "Creating Programs...\n";
$bscId = DB::table('programs')->insertGetId([
    'name' => 'B.Sc Computer Science',
    'code' => 'BSC-CS',
    'department_id' => $scienceId,
    'duration_years' => 3,
    'created_at' => now(),
    'updated_at' => now(),
]);

$bcomId = DB::table('programs')->insertGetId([
    'name' => 'B.Com',
    'code' => 'BCOM',
    'department_id' => $commerceId,
    'duration_years' => 3,
    'created_at' => now(),
    'updated_at' => now(),
]);

$baId = DB::table('programs')->insertGetId([
    'name' => 'B.A English',
    'code' => 'BA-ENG',
    'department_id' => $artsId,
    'duration_years' => 3,
    'created_at' => now(),
    'updated_at' => now(),
]);

// Create Faculty
echo "Creating Faculty...\n";
for ($i = 1; $i <= 5; $i++) {
    DB::table('users')->insert([
        'name' => "Faculty Science $i",
        'email' => "faculty.sci$i@pvgs.edu",
        'password' => Hash::make('password123'),
        'user_type' => 'faculty',
        'department_id' => $scienceId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

// Create Students
echo "Creating Students...\n";
for ($i = 1; $i <= 10; $i++) {
    $userId = DB::table('users')->insertGetId([
        'name' => "Student $i",
        'email' => "student$i@pvgs.edu",
        'password' => Hash::make('password123'),
        'user_type' => 'student',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    DB::table('students')->insert([
        'user_id' => $userId,
        'department_id' => $scienceId,
        'program_id' => $bscId,
        'enrollment_number' => 'ENR2024' . str_pad($i, 4, '0', STR_PAD_LEFT),
        'academic_year' => '2024-25',
        'semester' => 1,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

echo "\n✅ Manual seed completed!\n\n";
echo "📊 Test Credentials:\n";
echo "Super Admin: admin@pvgs.edu / password123\n";
echo "Principal: principal@pvgs.edu / password123\n";
echo "HOD Science: hod.science@pvgs.edu / password123\n";
echo "Faculty: faculty.sci1@pvgs.edu / password123\n";
echo "Student: student1@pvgs.edu / password123\n\n";
echo "📈 Data Created:\n";
echo "  • 3 Departments\n";
echo "  • 3 Programs\n";
echo "  • 5 Faculty (Science)\n";
echo "  • 10 Students\n\n";
