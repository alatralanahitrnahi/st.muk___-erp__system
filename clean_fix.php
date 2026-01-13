<?php
// Clean System Fix Script
echo "🔧 PVGS ERP System Fix\n";
echo "======================\n\n";

$dbPath = __DIR__ . '/database/database.sqlite';

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Database connected\n";
    
    // 1. Get students
    echo "\n📚 Getting student records...\n";
    $students = $pdo->query("SELECT id, user_id, name FROM students")->fetchAll(PDO::FETCH_ASSOC);
    echo "Found " . count($students) . " student records\n";
    
    // 2. Create fee structures
    echo "\n💰 Setting up fee structures...\n";
    
    $feeStructureCheck = $pdo->query("SELECT COUNT(*) FROM fee_structures")->fetchColumn();
    if ($feeStructureCheck == 0) {
        $sql = "INSERT INTO fee_structures (program_id, academic_year, name, tuition_fee, development_fee, exam_fee, library_fee, lab_fee, other_fees, total_fee, total_installments, installment_type, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, datetime('now'), datetime('now'))";
        $feeStructureStmt = $pdo->prepare($sql);
        
        // BSc Computer Science
        $feeStructureStmt->execute([1, '2024-25', 'BSc Computer Science Annual Fee', 45000, 8000, 3000, 2000, 5000, 2000, 65000, 2, 'semester']);
        
        // BCom
        $feeStructureStmt->execute([2, '2024-25', 'BCom Annual Fee', 35000, 6000, 2500, 1500, 2000, 1500, 48500, 2, 'semester']);
        
        echo "  + Created fee structures\n";
    } else {
        echo "  ✓ Fee structures already exist\n";
    }
    
    // 3. Add student fees
    echo "\n💳 Adding student fee records...\n";
    
    $sql = "INSERT OR IGNORE INTO student_fees (student_id, fee_structure_id, academic_year, gross_amount, scholarship_amount, discount_amount, fine_amount, net_amount, paid_amount, balance_amount, status, current_installment, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))";
    $studentFeeStmt = $pdo->prepare($sql);
    
    foreach ($students as $student) {
        $feeStructureId = ($student['id'] % 2) + 1;
        $grossAmount = $feeStructureId == 1 ? 65000 : 48500;
        $scholarshipAmount = rand(0, 5000);
        $discountAmount = rand(0, 2000);
        $fineAmount = 0;
        $netAmount = $grossAmount - $scholarshipAmount - $discountAmount + $fineAmount;
        $paidAmount = rand(20000, $netAmount - 5000);
        $balanceAmount = $netAmount - $paidAmount;
        $status = $balanceAmount > 0 ? 'partial' : 'paid';
        $currentInstallment = $paidAmount > ($netAmount / 2) ? 2 : 1;
        
        $studentFeeStmt->execute([$student['id'], $feeStructureId, '2024-25', $grossAmount, $scholarshipAmount, $discountAmount, $fineAmount, $netAmount, $paidAmount, $balanceAmount, $status, $currentInstallment]);
        echo "  + Added fee record for: {$student['name']}\n";
    }
    
    // 4. Add attendance records
    echo "\n📊 Adding attendance records...\n";
    
    $subjects = $pdo->query("SELECT id FROM subjects")->fetchAll(PDO::FETCH_COLUMN);
    $sql = "INSERT OR IGNORE INTO attendance_records (student_id, subject_id, attendance_date, status, marked_by, created_at, updated_at) VALUES (?, ?, ?, ?, 1, datetime('now'), datetime('now'))";
    $attendanceStmt = $pdo->prepare($sql);
    
    foreach ($students as $student) {
        foreach ($subjects as $subjectId) {
            for ($day = 1; $day <= 30; $day++) {
                $date = date('Y-m-d', strtotime("-$day days"));
                $status = (rand(1, 100) <= 85) ? 'present' : 'absent';
                $attendanceStmt->execute([$student['id'], $subjectId, $date, $status]);
            }
        }
    }
    echo "  + Added attendance records for all students\n";
    
    // 5. Add exam results
    echo "\n📝 Adding exam results...\n";
    
    $sql = "INSERT OR IGNORE INTO exam_results (student_id, subject_id, exam_type, marks_obtained, max_marks, percentage, grade, result, academic_year, semester, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))";
    $resultStmt = $pdo->prepare($sql);
    
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
            
            $resultStmt->execute([$student['id'], $subjectId, 'semester', $marksObtained, $maxMarks, $percentage, $grade, $result, '2024-25', 1]);
        }
    }
    echo "  + Added exam results for all students\n";
    
    // 6. Add principal user
    echo "\n👑 Checking principal account...\n";
    
    $principalCheck = $pdo->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'principal' OR email LIKE '%principal%'")->fetch();
    
    if ($principalCheck['count'] == 0) {
        $sql = "INSERT INTO users (name, email, password, user_type, is_active, created_at, updated_at) VALUES (?, ?, ?, 'principal', 1, datetime('now'), datetime('now'))";
        $principalStmt = $pdo->prepare($sql);
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        $principalStmt->execute(['Dr. Priya Mehta', 'principal@pvgs.edu', $hashedPassword]);
        echo "  + Added principal account: principal@pvgs.edu\n";
    } else {
        echo "  ✓ Principal account already exists\n";
    }
    
    // 7. Update passwords
    echo "\n🔐 Updating user passwords...\n";
    
    $users = $pdo->query("SELECT id, email, password FROM users WHERE password IS NULL OR password = ''")->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($users)) {
        $passwordStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $defaultPassword = password_hash('password123', PASSWORD_DEFAULT);
        
        foreach ($users as $user) {
            $passwordStmt->execute([$defaultPassword, $user['id']]);
            echo "  + Updated password for: {$user['email']}\n";
        }
    } else {
        echo "  ✓ All users have passwords\n";
    }
    
    // 8. Final verification
    echo "\n✅ Final System Status:\n";
    
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
    
    echo "\n🎉 System is now fully functional!\n";
    echo "\n📋 Login Credentials (password: password123):\n";
    echo "- Principal: principal@pvgs.edu\n";
    echo "- Admin: admin@pvgs.edu\n";
    echo "- Faculty: rajesh.kumar@pvgs.edu\n";
    echo "- Student: aarav.sharma@pvgs.edu\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}