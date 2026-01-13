<?php
// Complete Sample Data Population
$dbPath = '/workspaces/st.muk___-erp__system/database/database.sqlite';

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "📊 PVGS ERP Sample Data Population\n";
    echo "==================================\n\n";

    // Add departments if missing
    $pdo->exec("INSERT OR IGNORE INTO departments (id, name, code) VALUES 
        (1, 'Computer Science', 'CS'),
        (2, 'Commerce', 'COM'),
        (3, 'Arts', 'ARTS')");
    echo "✅ Departments added\n";

    // Add programs if missing
    $pdo->exec("INSERT OR IGNORE INTO programs (id, name, code, department_id, duration_years) VALUES 
        (1, 'Bachelor of Science in Computer Science', 'BSc CS', 1, 3),
        (2, 'Bachelor of Commerce', 'BCom', 2, 3),
        (3, 'Bachelor of Arts', 'BA', 3, 3)");
    echo "✅ Programs added\n";

    // Add sample students
    $students = [
        ['Rahul Sharma', '2023001', 1, 1, 2],
        ['Priya Patel', '2023002', 1, 1, 2],
        ['Amit Kumar', '2023003', 1, 1, 2],
        ['Sneha Joshi', '2023004', 2, 2, 1],
        ['Vikram Singh', '2023005', 2, 2, 1],
        ['Anita Desai', '2023006', 3, 3, 3]
    ];

    $stmt = $pdo->prepare("INSERT OR IGNORE INTO students (name, roll_number, department_id, program_id, current_semester) VALUES (?, ?, ?, ?, ?)");
    foreach ($students as $student) {
        $stmt->execute($student);
    }
    echo "✅ Sample students added\n";

    // Add fee structures
    $feeStructures = [
        [1, 'Tuition Fee', 45000, 'annual'],
        [1, 'Lab Fee', 5000, 'annual'],
        [2, 'Tuition Fee', 35000, 'annual'],
        [2, 'Library Fee', 2000, 'annual'],
        [3, 'Tuition Fee', 30000, 'annual']
    ];

    $stmt = $pdo->prepare("INSERT OR IGNORE INTO fee_structures (program_id, fee_type, amount, frequency) VALUES (?, ?, ?, ?)");
    foreach ($feeStructures as $fee) {
        $stmt->execute($fee);
    }
    echo "✅ Fee structures added\n";

    // Add timetable entries
    $timetableEntries = [
        [1, 1, 'SY-CS-A', 'Room-201', 1, '10:00', '11:00'],
        [2, 1, 'TY-CS-A', 'Room-301', 1, '13:00', '14:00'],
        [3, 1, 'TY-CS-A', 'Room-301', 2, '17:00', '18:00'],
        [1, 2, 'SY-CS-B', 'Room-202', 3, '11:00', '12:00'],
        [4, 3, 'FY-COM-A', 'Room-101', 1, '09:00', '10:00']
    ];

    $stmt = $pdo->prepare("INSERT OR IGNORE INTO timetable_entries (subject_id, faculty_id, class_id, room_number, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($timetableEntries as $entry) {
        $stmt->execute($entry);
    }
    echo "✅ Timetable entries added\n";

    // Verify final counts
    $tables = ['students', 'faculty', 'subjects', 'departments', 'programs', 'fee_structures', 'timetable_entries'];
    echo "\n📈 Final System Status:\n";
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            echo sprintf("%-20s: %d records\n", ucfirst($table), $count);
        } catch (PDOException $e) {
            echo sprintf("%-20s: Error - %s\n", ucfirst($table), $e->getMessage());
        }
    }

    echo "\n🎉 Sample data population completed successfully!\n";
    echo "🚀 System is now ready for testing and demonstration.\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>