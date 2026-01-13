<?php
// Comprehensive Fix Script for PVGS ERP Issues
echo "🔧 PVGS ERP System Fix\n";
echo "======================\n\n";

$dbPath = __DIR__ . '/database/database.sqlite';

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Database connected\n";
    
    // 1. Add sample students (the main issue)
    echo "\n📚 Adding sample students...\n";
    
    $students = [
        ['name' => 'Rahul Sharma', 'email' => 'rahul.sharma@student.pvgs.edu', 'admission_number' => 'ADM2024001', 'program' => 'BSc Computer Science', 'phone' => '+91 9876543210'],
        ['name' => 'Priya Patel', 'email' => 'priya.patel@student.pvgs.edu', 'admission_number' => 'ADM2024002', 'program' => 'BSc Computer Science', 'phone' => '+91 9876543211'],
        ['name' => 'Amit Kumar', 'email' => 'amit.kumar@student.pvgs.edu', 'admission_number' => 'ADM2024003', 'program' => 'BSc Computer Science', 'phone' => '+91 9876543212'],
        ['name' => 'Sneha Gupta', 'email' => 'sneha.gupta@student.pvgs.edu', 'admission_number' => 'ADM2024004', 'program' => 'BCom', 'phone' => '+91 9876543213'],
        ['name' => 'Vikash Singh', 'email' => 'vikash.singh@student.pvgs.edu', 'admission_number' => 'ADM2024005', 'program' => 'BCom', 'phone' => '+91 9876543214']
    ];
    
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO students (name, email, admission_number, program, phone, created_at, updated_at) VALUES (?, ?, ?, ?, ?, datetime('now'), datetime('now'))");
    
    foreach ($students as $student) {
        $stmt->execute([$student['name'], $student['email'], $student['admission_number'], $student['program'], $student['phone']]);
        echo "  + Added student: {$student['name']}\n";
    }
    
    // 2. Add student users for login
    echo "\n👤 Adding student user accounts...\n";
    
    $userStmt = $pdo->prepare("INSERT OR IGNORE INTO users (name, email, password, user_type, created_at, updated_at) VALUES (?, ?, ?, 'student', datetime('now'), datetime('now'))");
    
    foreach ($students as $student) {
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        $userStmt->execute([$student['name'], $student['email'], $hashedPassword]);
        echo "  + Added user: {$student['email']}\n";
    }
    
    // 3. Add sample fee records
    echo "\n💰 Adding fee records...\n";
    
    $feeStmt = $pdo->prepare("INSERT OR IGNORE INTO student_fees (student_id, total_amount, paid_amount, balance_amount, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, datetime('now'), datetime('now'))");
    
    for ($i = 1; $i <= 5; $i++) {
        $totalAmount = 60000;
        $paidAmount = rand(20000, 50000);
        $balanceAmount = $totalAmount - $paidAmount;
        $status = $balanceAmount > 0 ? 'partial' : 'paid';
        
        $feeStmt->execute([$i, $totalAmount, $paidAmount, $balanceAmount, $status]);
        echo "  + Added fee record for student ID: $i\n";
    }
    
    // 4. Add attendance records
    echo "\n📊 Adding attendance records...\n";
    
    $attendanceStmt = $pdo->prepare("INSERT OR IGNORE INTO attendance_records (student_id, subject_id, date, status, created_at, updated_at) VALUES (?, ?, ?, ?, datetime('now'), datetime('now'))");
    
    $subjects = [1, 2, 3, 4]; // Subject IDs
    $statuses = ['present', 'absent'];
    
    for ($studentId = 1; $studentId <= 5; $studentId++) {
        foreach ($subjects as $subjectId) {
            // Generate 30 days of attendance
            for ($day = 1; $day <= 30; $day++) {
                $date = date('Y-m-d', strtotime("-$day days"));
                $status = $statuses[array_rand($statuses)];
                // 85% attendance rate
                $status = (rand(1, 100) <= 85) ? 'present' : 'absent';
                
                $attendanceStmt->execute([$studentId, $subjectId, $date, $status]);
            }
        }
    }
    echo "  + Added attendance records for all students\n";
    
    // 5. Add exam results
    echo "\n📝 Adding exam results...\n";
    
    $resultStmt = $pdo->prepare("INSERT OR IGNORE INTO exam_results (student_id, subject_id, marks_obtained, max_marks, percentage, grade, result, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))");
    
    for ($studentId = 1; $studentId <= 5; $studentId++) {
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
            
            $resultStmt->execute([$studentId, $subjectId, $marksObtained, $maxMarks, $percentage, $grade, $result]);
        }
    }
    echo "  + Added exam results for all students\n";
    
    // 6. Add principal user if missing
    echo "\n👑 Checking principal account...\n";
    
    $principalCheck = $pdo->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'principal'")->fetch();
    
    if ($principalCheck['count'] == 0) {
        $principalStmt = $pdo->prepare("INSERT INTO users (name, email, password, user_type, created_at, updated_at) VALUES (?, ?, ?, 'principal', datetime('now'), datetime('now'))");
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        $principalStmt->execute(['Dr. Priya Mehta', 'principal@pvgs.edu', $hashedPassword]);
        echo "  + Added principal account: principal@pvgs.edu\n";
    } else {
        echo "  ✓ Principal account already exists\n";
    }
    
    // 7. Verify data
    echo "\n✅ Verification:\n";
    
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
    
    echo "\n🎉 Database fix completed successfully!\n";
    echo "\nDemo Login Credentials:\n";
    echo "- Principal: principal@pvgs.edu / password123\n";
    echo "- Admin: admin@pvgs.edu / password123\n";
    echo "- Faculty: faculty@pvgs.edu / password123\n";
    echo "- Student: rahul.sharma@student.pvgs.edu / password123\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}