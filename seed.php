<?php

$dbPath = __DIR__ . '/database/database.sqlite';
$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "🔄 Seeding database with test data...\n\n";

$pdo->exec('PRAGMA foreign_keys = OFF');
$pdo->exec('DELETE FROM users');
$pdo->exec('DELETE FROM departments');
$pdo->exec('DELETE FROM programs');
$pdo->exec('DELETE FROM students');
$pdo->exec('DELETE FROM categories');
$pdo->exec('DELETE FROM academic_patterns');
$pdo->exec('PRAGMA foreign_keys = ON');

$now = date('Y-m-d H:i:s');

// 1. Create Super Admin
echo "✓ Creating Super Admin\n";
$stmt = $pdo->prepare('INSERT INTO users (name, email, password, user_type, role, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
$stmt->execute(['Super Admin', 'admin@pvgs.edu', password_hash('password123', PASSWORD_BCRYPT), 'admin', 'super-admin', $now, $now]);

// 2. Create Departments
echo "✓ Creating Departments\n";
$stmt = $pdo->prepare('INSERT INTO departments (name, code, description, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)');
$stmt->execute(['Science', 'SCI', 'Science Department', 1, $now, $now]);
$scienceId = $pdo->lastInsertId();
$stmt->execute(['Commerce', 'COM', 'Commerce Department', 1, $now, $now]);
$commerceId = $pdo->lastInsertId();
$stmt->execute(['Arts', 'ART', 'Arts Department', 1, $now, $now]);
$artsId = $pdo->lastInsertId();

// 3. Create Academic Patterns
echo "✓ Creating Academic Patterns\n";
$stmt = $pdo->prepare('INSERT INTO academic_patterns (name, code, total_units, unit_type, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
$stmt->execute(['Semester Pattern', 'SEM', 6, 'semester', 1, $now, $now]);
$semPatternId = $pdo->lastInsertId();

// 4. Create Categories
echo "✓ Creating Fee Categories\n";
$stmt = $pdo->prepare('INSERT INTO categories (name, code, description, fee_percentage, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
$stmt->execute(['General', 'GEN', 'General Category', 100, 1, $now, $now]);
$genCatId = $pdo->lastInsertId();
$stmt->execute(['SC/ST', 'SCST', 'SC/ST Category', 50, 1, $now, $now]);
$scstCatId = $pdo->lastInsertId();

// 4. Create Principal
echo "✓ Creating Principal\n";
$stmt = $pdo->prepare('INSERT INTO users (name, email, password, user_type, role, designation, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->execute(['Dr. Principal', 'principal@pvgs.edu', password_hash('password123', PASSWORD_BCRYPT), 'admin', 'principal', 'Principal', $now, $now]);

// 5. Create HODs
echo "✓ Creating HODs\n";
$stmt->execute(['HOD Science', 'hod.science@pvgs.edu', password_hash('password123', PASSWORD_BCRYPT), 'staff', 'registrar', 'Head of Department', $now, $now]);
$stmt->execute(['HOD Commerce', 'hod.commerce@pvgs.edu', password_hash('password123', PASSWORD_BCRYPT), 'staff', 'registrar', 'Head of Department', $now, $now]);
$stmt->execute(['HOD Arts', 'hod.arts@pvgs.edu', password_hash('password123', PASSWORD_BCRYPT), 'staff', 'registrar', 'Head of Department', $now, $now]);

// 6. Create Registrar
echo "✓ Creating Registrar\n";
$stmt->execute(['Registrar', 'registrar@pvgs.edu', password_hash('password123', PASSWORD_BCRYPT), 'staff', 'registrar', 'Registrar', $now, $now]);

// 7. Create Programs
echo "✓ Creating Programs\n";
$stmt = $pdo->prepare('INSERT INTO programs (name, code, level, department_id, academic_pattern_id, duration_years, total_semesters, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->execute(['B.Sc Computer Science', 'BSC-CS', 'UG', $scienceId, $semPatternId, 3, 6, 1, $now, $now]);
$bscId = $pdo->lastInsertId();
$stmt->execute(['B.Com', 'BCOM', 'UG', $commerceId, $semPatternId, 3, 6, 1, $now, $now]);
$bcomId = $pdo->lastInsertId();
$stmt->execute(['B.A English', 'BA-ENG', 'UG', $artsId, $semPatternId, 3, 6, 1, $now, $now]);
$baId = $pdo->lastInsertId();

// 8. Create Faculty
echo "✓ Creating Faculty (15 users)\n";
$stmt = $pdo->prepare('INSERT INTO users (name, email, password, user_type, role, designation, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
for ($i = 1; $i <= 15; $i++) {
    $deptName = $i <= 5 ? 'Science' : ($i <= 10 ? 'Commerce' : 'Arts');
    $stmt->execute(["Faculty $deptName " . (($i-1) % 5 + 1), "faculty$i@pvgs.edu", password_hash('password123', PASSWORD_BCRYPT), 'faculty', 'faculty', 'Assistant Professor', $now, $now]);
}

// 9. Create Students
echo "✓ Creating Students (30 users)\n";
$userStmt = $pdo->prepare('INSERT INTO users (name, email, password, user_type, role, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
$studentStmt = $pdo->prepare('INSERT INTO students (user_id, program_id, category_id, department_id, status, application_status, application_date, application_fee_paid, parent_name, parent_phone, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

for ($i = 1; $i <= 30; $i++) {
    $dept = $i <= 10 ? $scienceId : ($i <= 20 ? $commerceId : $artsId);
    $prog = $i <= 10 ? $bscId : ($i <= 20 ? $bcomId : $baId);
    
    $userStmt->execute(["Student $i", "student$i@pvgs.edu", password_hash('password123', PASSWORD_BCRYPT), 'student', 'student', $now, $now]);
    $userId = $pdo->lastInsertId();
    
    $studentStmt->execute([
        $userId, 
        $prog, 
        $genCatId, 
        $dept, 
        'active', 
        'approved', 
        date('Y-m-d'), 
        1, 
        "Parent of Student $i", 
        '9876543210', 
        $now, 
        $now
    ]);
}

echo "\n✅ Seed completed!\n\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 TEST CREDENTIALS (password: password123)\n";
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
echo "📈 DATA SUMMARY:\n";
echo "  • 3 Departments\n";
echo "  • 3 Programs\n";
echo "  • 2 Fee Categories\n";
echo "  • 15 Faculty\n";
echo "  • 30 Students\n";
echo "  • Total: 51 users\n\n";
