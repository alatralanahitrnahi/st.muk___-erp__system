<?php

// Direct SQLite seeding without Laravel
$dbPath = __DIR__ . '/database/database.sqlite';

if (!file_exists($dbPath)) {
    die("❌ Database file not found: $dbPath\n");
}

$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "🔄 Starting direct database seed...\n\n";

// Clear existing data
echo "Clearing existing data...\n";
$pdo->exec('PRAGMA foreign_keys = OFF');
$pdo->exec('DELETE FROM users');
$pdo->exec('DELETE FROM departments');
$pdo->exec('DELETE FROM programs');
$pdo->exec('DELETE FROM students');
$pdo->exec('PRAGMA foreign_keys = ON');

$now = date('Y-m-d H:i:s');

// Create Super Admin
echo "Creating Super Admin...\n";
$stmt = $pdo->prepare('INSERT INTO users (name, email, password, user_type, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)');
$stmt->execute(['Super Admin', 'admin@pvgs.edu', password_hash('password123', PASSWORD_BCRYPT), 'admin', $now, $now]);
$adminId = $pdo->lastInsertId();

// Create Departments
echo "Creating Departments...\n";
$stmt = $pdo->prepare('INSERT INTO departments (name, code, created_at, updated_at) VALUES (?, ?, ?, ?)');
$stmt->execute(['Science', 'SCI', $now, $now]);
$scienceId = $pdo->lastInsertId();

$stmt->execute(['Commerce', 'COM', $now, $now]);
$commerceId = $pdo->lastInsertId();

$stmt->execute(['Arts', 'ART', $now, $now]);
$artsId = $pdo->lastInsertId();

// Create Principal
echo "Creating Principal...\n";
$stmt = $pdo->prepare('INSERT INTO users (name, email, password, user_type, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)');
$stmt->execute(['Dr. Principal', 'principal@pvgs.edu', password_hash('password123', PASSWORD_BCRYPT), 'admin', $now, $now]);

// Create HODs
echo "Creating HODs...\n";
$stmt = $pdo->prepare('INSERT INTO users (name, email, password, user_type, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)');
$stmt->execute(['HOD Science', 'hod.science@pvgs.edu', password_hash('password123', PASSWORD_BCRYPT), 'staff', $now, $now]);
$stmt->execute(['HOD Commerce', 'hod.commerce@pvgs.edu', password_hash('password123', PASSWORD_BCRYPT), 'staff', $now, $now]);
$stmt->execute(['HOD Arts', 'hod.arts@pvgs.edu', password_hash('password123', PASSWORD_BCRYPT), 'staff', $now, $now]);

// Create Registrar
echo "Creating Registrar...\n";
$stmt = $pdo->prepare('INSERT INTO users (name, email, password, user_type, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)');
$stmt->execute(['Registrar', 'registrar@pvgs.edu', password_hash('password123', PASSWORD_BCRYPT), 'staff', $now, $now]);

// Create Programs
echo "Creating Programs...\n";
$stmt = $pdo->prepare('INSERT INTO programs (name, code, department_id, duration_years, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)');
$stmt->execute(['B.Sc Computer Science', 'BSC-CS', $scienceId, 3, $now, $now]);
$bscId = $pdo->lastInsertId();

$stmt->execute(['B.Com', 'BCOM', $commerceId, 3, $now, $now]);
$bcomId = $pdo->lastInsertId();

$stmt->execute(['B.A English', 'BA-ENG', $artsId, 3, $now, $now]);
$baId = $pdo->lastInsertId();

// Create Faculty
echo "Creating Faculty...\n";
$stmt = $pdo->prepare('INSERT INTO users (name, email, password, user_type, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)');
for ($i = 1; $i <= 15; $i++) {
    $deptName = $i <= 5 ? 'Science' : ($i <= 10 ? 'Commerce' : 'Arts');
    $stmt->execute(["Faculty $deptName " . (($i-1) % 5 + 1), "faculty$i@pvgs.edu", password_hash('password123', PASSWORD_BCRYPT), 'faculty', $now, $now]);
}

// Create Students
echo "Creating Students...\n";
$userStmt = $pdo->prepare('INSERT INTO users (name, email, password, user_type, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)');
$studentStmt = $pdo->prepare('INSERT INTO students (user_id, department_id, program_id, enrollment_number, academic_year, semester, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');

for ($i = 1; $i <= 30; $i++) {
    $dept = $i <= 10 ? $scienceId : ($i <= 20 ? $commerceId : $artsId);
    $prog = $i <= 10 ? $bscId : ($i <= 20 ? $bcomId : $baId);
    
    $userStmt->execute(["Student $i", "student$i@pvgs.edu", password_hash('password123', PASSWORD_BCRYPT), 'student', $now, $now]);
    $userId = $pdo->lastInsertId();
    
    $studentStmt->execute([$userId, $dept, $prog, 'ENR2024' . str_pad($i, 4, '0', STR_PAD_LEFT), '2024-25', 1, 'active', $now, $now]);
}

echo "\n✅ Direct seed completed!\n\n";
echo "📊 Test Credentials (all passwords: password123):\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Super Admin:  admin@pvgs.edu\n";
echo "Principal:    principal@pvgs.edu\n";
echo "HOD Science:  hod.science@pvgs.edu\n";
echo "HOD Commerce: hod.commerce@pvgs.edu\n";
echo "HOD Arts:     hod.arts@pvgs.edu\n";
echo "Registrar:    registrar@pvgs.edu\n";
echo "Faculty:      faculty1@pvgs.edu (1-15)\n";
echo "Student:      student1@pvgs.edu (1-30)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
echo "📈 Data Created:\n";
echo "  • 3 Departments (Science, Commerce, Arts)\n";
echo "  • 3 Programs (BSC-CS, BCOM, BA-ENG)\n";
echo "  • 15 Faculty (5 per department)\n";
echo "  • 30 Students (10 per department)\n";
echo "  • Total: 50 users\n\n";
echo "🚀 Ready to test APIs!\n\n";
