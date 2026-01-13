<?php
// Simple Data Population - Working with Existing Schema
$dbPath = '/workspaces/st.muk___-erp__system/database/database.sqlite';

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "📊 PVGS ERP Simple Data Population\n";
    echo "==================================\n\n";

    // Check existing table structures and add data accordingly
    
    // Add basic students (using only existing columns)
    try {
        $pdo->exec("INSERT OR IGNORE INTO students (id, name, department_id) VALUES 
            (1, 'Rahul Sharma', 1),
            (2, 'Priya Patel', 1),
            (3, 'Amit Kumar', 1),
            (4, 'Sneha Joshi', 2),
            (5, 'Vikram Singh', 2),
            (6, 'Anita Desai', 3)");
        echo "✅ Students added\n";
    } catch (PDOException $e) {
        echo "⚠️  Students: " . $e->getMessage() . "\n";
    }

    // Add basic fee structures
    try {
        $pdo->exec("INSERT OR IGNORE INTO fee_structures (id, amount, fee_type) VALUES 
            (1, 45000, 'Tuition Fee'),
            (2, 5000, 'Lab Fee'),
            (3, 35000, 'Commerce Fee'),
            (4, 2000, 'Library Fee')");
        echo "✅ Fee structures added\n";
    } catch (PDOException $e) {
        echo "⚠️  Fee structures: " . $e->getMessage() . "\n";
    }

    // Add timetable entries
    try {
        $pdo->exec("INSERT OR IGNORE INTO timetable_entries (id, subject_id, faculty_id, class_id, room_number, day_of_week, start_time, end_time) VALUES 
            (1, 1, 1, 'SY-CS-A', 'Room-201', 1, '10:00', '11:00'),
            (2, 2, 1, 'TY-CS-A', 'Room-301', 1, '13:00', '14:00'),
            (3, 3, 1, 'TY-CS-A', 'Room-301', 2, '17:00', '18:00')");
        echo "✅ Timetable entries added\n";
    } catch (PDOException $e) {
        echo "⚠️  Timetable: " . $e->getMessage() . "\n";
    }

    // Verify final counts
    $tables = ['students', 'faculty', 'subjects', 'fee_structures', 'timetable_entries', 'system_settings'];
    echo "\n📈 System Data Summary:\n";
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            echo sprintf("%-20s: %d records\n", ucfirst(str_replace('_', ' ', $table)), $count);
        } catch (PDOException $e) {
            echo sprintf("%-20s: Table not found\n", ucfirst(str_replace('_', ' ', $table)));
        }
    }

    echo "\n🎉 Data population completed!\n";
    echo "🚀 System ready for final testing.\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>