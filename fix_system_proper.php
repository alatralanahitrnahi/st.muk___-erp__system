<?php
// Proper System Fix Based on Actual Schema
echo "🔧 PVGS ERP System Fix (Schema-Aware)\n";
echo "=====================================\n\n";

$dbPath = __DIR__ . '/database/database.sqlite';

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Database connected\n";
    
    // 1. Get existing student users
    echo "\n📚 Checking existing student users...\n";
    $studentUsers = $pdo->query("SELECT id, name, email FROM users WHERE user_type = 'student'")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($studentUsers) . " student users:\n";
    foreach ($studentUsers as $user) {
        echo "  - {$user['name']} ({$user['email']})\n";
    }
    
    // 2. Create student records for existing student users
    echo "\n📝 Creating student records...\n";
    
    $studentStmt = $pdo->prepare("INSERT OR IGNORE INTO students (user_id, program_id, admission_number, status, admission_date, name, created_at, updated_at) VALUES (?, ?, ?, 'active', date('now'), ?, datetime('now'), datetime('now'))");
    
    foreach ($studentUsers as $index => $user) {
        $admissionNumber = 'ADM2024' . str_pad($user['id'], 3, '0', STR_PAD_LEFT);
        $programId = ($index % 2) + 1; // Alternate between program 1 and 2
        
        $studentStmt->execute([$user['id'], $programId, $admissionNumber, $user['name']]);
        echo "  + Created student record for: {$user['name']}\n";
    }
    
    // 3. Add fee records for students
    echo "\n💰 Adding fee records...\n";
    
    $students = $pdo->query("SELECT id, user_id FROM students")->fetchAll(PDO::FETCH_ASSOC);
    
    $feeStmt = $pdo->prepare("INSERT OR IGNORE INTO student_fees (student_id, total_amount, paid_amount, balance_amount, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, datetime('now'), datetime('now'))");
    
    foreach ($students as $student) {
        $totalAmount = 60000;
        $paidAmount = rand(20000, 50000);
        $balanceAmount = $totalAmount - $paidAmount;
        $status = $balanceAmount > 0 ? 'partial' : 'paid';
        
        $feeStmt->execute([$student['id'], $totalAmount, $paidAmount, $balanceAmount, $status]);
        echo "  + Added fee record for student ID: {$student['id']}\n";
    }
    
    // 4. Add attendance records
    echo "\n📊 Adding attendance records...\n";
    
    $subjects = $pdo->query("SELECT id FROM subjects")->fetchAll(PDO::FETCH_COLUMN);
    $attendanceStmt = $pdo->prepare("INSERT OR IGNORE INTO attendance_records (student_id, subject_id, date, status, created_at, updated_at) VALUES (?, ?, ?, ?, datetime('now'), datetime('now'))");
    
    foreach ($students as $student) {
        foreach ($subjects as $subjectId) {
            // Generate 30 days of attendance
            for ($day = 1; $day <= 30; $day++) {
                $date = date('Y-m-d', strtotime("-$day days"));
                // 85% attendance rate
                $status = (rand(1, 100) <= 85) ? 'present' : 'absent';
                
                $attendanceStmt->execute([$student['id'], $subjectId, $date, $status]);
            }
        }
    }
    echo "  + Added attendance records for all students\n";
    
    // 5. Add exam results
    echo "\n📝 Adding exam results...\n";
    
    $resultStmt = $pdo->prepare("INSERT OR IGNORE INTO exam_results (student_id, subject_id, marks_obtained, max_marks, percentage, grade, result, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))");
    
    foreach ($students as $student) {
        foreach ($subjects as $subjectId) {
            $maxMarks = 100;
            $marksObtained = rand(60, 95);
            $percentage = ($marksObtained / $maxMarks) * 100;
            
            $grade = 'F';
            if ($percentage >= 90) $grade = 'A+';
            elseif ($percentage >= 80) $grade = 'A';
            elseif ($percentage >= 70) $grade = 'B+';
            elseif ($percentage >= 60) $grade = 'B';
            elseif ($percentage >= 50) $grade = 'C';
            
            $result = $percentage >= 40 ? 'pass' : 'fail';
            
            $resultStmt->execute([$student['id'], $subjectId, $marksObtained, $maxMarks, $percentage, $grade, $result]);
        }
    }
    echo "  + Added exam results for all students\n";
    
    // 6. Add principal user if missing
    echo "\n👑 Checking principal account...\n";
    
    $principalCheck = $pdo->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'principal' OR email LIKE '%principal%'")->fetch();
    
    if ($principalCheck['count'] == 0) {
        $principalStmt = $pdo->prepare("INSERT INTO users (name, email, password, user_type, created_at, updated_at) VALUES (?, ?, ?, 'principal', datetime('now'), datetime('now'))");
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        $principalStmt->execute(['Dr. Priya Mehta', 'principal@pvgs.edu', $hashedPassword]);
        echo "  + Added principal account: principal@pvgs.edu\n";
    } else {
        echo "  ✓ Principal account already exists\n";
    }
    
    // 7. Verify data
    echo "\n✅ Final Verification:\n";
    
    $counts = [
        'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'students' => $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn(),
        'student_fees' => $pdo->query("SELECT COUNT(*) FROM student_fees")->fetchColumn(),
        'attendance_records' => $pdo->query("SELECT COUNT(*) FROM attendance_records")->fetchColumn(),
        'exam_results' => $pdo->query("SELECT COUNT(*) FROM exam_results")->fetchColumn()
    ];
    
    foreach ($counts as $table => $count) {
        echo "  - $table: $count records\n";
    }
    
    // Show user types
    echo "\nUser Types:\n";
    $userTypes = $pdo->query("SELECT user_type, COUNT(*) as count FROM users GROUP BY user_type")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($userTypes as $type) {
        echo "  - {$type['user_type']}: {$type['count']} users\n";
    }
    
    echo "\n🎉 System fix completed successfully!\n";
    echo "\n📋 Updated Login Credentials:\n";
    echo "- Principal: principal@pvgs.edu / password123\n";
    echo "- Admin: admin@pvgs.edu / password123\n";
    echo "- Faculty: rajesh.kumar@pvgs.edu / password123\n";
    echo "- Student: aarav.sharma@pvgs.edu / password123\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}