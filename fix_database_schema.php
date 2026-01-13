<?php
// SQLite Compatible Database Schema Setup
$dbPath = '/workspaces/st.muk___-erp__system/database/database.sqlite';

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "🔧 PVGS ERP Database Schema Fix\n";
    echo "===============================\n\n";

    // Create missing tables with SQLite-compatible syntax
    $tables = [
        "CREATE TABLE IF NOT EXISTS faculty (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            phone VARCHAR(15),
            department_id INTEGER,
            designation VARCHAR(50),
            qualification VARCHAR(100),
            experience_years INTEGER DEFAULT 0,
            salary DECIMAL(10,2),
            joining_date DATE,
            status TEXT DEFAULT 'active' CHECK(status IN ('active', 'inactive')),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",

        "CREATE TABLE IF NOT EXISTS subjects (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            code VARCHAR(20) UNIQUE NOT NULL,
            credits INTEGER DEFAULT 3,
            semester INTEGER,
            program_id INTEGER,
            department_id INTEGER,
            theory_hours INTEGER DEFAULT 0,
            practical_hours INTEGER DEFAULT 0,
            is_elective BOOLEAN DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",

        "CREATE TABLE IF NOT EXISTS timetable_entries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            subject_id INTEGER,
            faculty_id INTEGER,
            class_id VARCHAR(50),
            room_number VARCHAR(20),
            day_of_week INTEGER,
            start_time TIME,
            end_time TIME,
            semester INTEGER,
            academic_year VARCHAR(10),
            is_lab BOOLEAN DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )"
    ];

    foreach ($tables as $sql) {
        try {
            $pdo->exec($sql);
            echo "✅ Table created/updated\n";
        } catch (PDOException $e) {
            echo "⚠️  Table warning: " . $e->getMessage() . "\n";
        }
    }

    // Add missing columns safely
    $columns = [
        "ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'student'",
        "ALTER TABLE students ADD COLUMN name VARCHAR(100)",
        "ALTER TABLE students ADD COLUMN department_id INTEGER"
    ];

    foreach ($columns as $sql) {
        try {
            $pdo->exec($sql);
            echo "✅ Column added\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'duplicate column') === false) {
                echo "⚠️  Column warning: " . $e->getMessage() . "\n";
            }
        }
    }

    // Insert sample data
    echo "\n📊 Populating sample data...\n";

    // Check if faculty table exists and insert data
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='faculty'");
    if ($stmt->fetch()) {
        $pdo->exec("INSERT OR IGNORE INTO faculty (name, email, department_id, designation) VALUES 
            ('Dr. Rajesh Kumar', 'rajesh@pvgs.edu', 1, 'Assistant Professor'),
            ('Prof. Sunita Sharma', 'sunita@pvgs.edu', 1, 'Associate Professor'),
            ('Dr. Amit Patel', 'amit@pvgs.edu', 2, 'Professor')");
        echo "✅ Faculty data inserted\n";
    }

    // Check if subjects table exists and insert data
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='subjects'");
    if ($stmt->fetch()) {
        $pdo->exec("INSERT OR IGNORE INTO subjects (name, code, credits, program_id, department_id) VALUES 
            ('Data Structures', 'CS201', 4, 1, 1),
            ('Database Management', 'CS301', 4, 1, 1),
            ('Web Development', 'CS302', 3, 1, 1),
            ('Financial Accounting', 'COM101', 3, 2, 2)");
        echo "✅ Subjects data inserted\n";
    }

    // Update student names if column exists
    try {
        $pdo->exec("UPDATE students SET name = 'Student ' || id WHERE name IS NULL OR name = ''");
        echo "✅ Student names updated\n";
    } catch (PDOException $e) {
        echo "⚠️  Student update warning: " . $e->getMessage() . "\n";
    }

    // Verify final state
    $tables = ['students', 'faculty', 'subjects', 'departments', 'programs', 'system_settings'];
    echo "\n📈 Final Data Summary:\n";
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            echo "$table: $count records\n";
        } catch (PDOException $e) {
            echo "$table: Table not found\n";
        }
    }

    echo "\n🎉 Database schema optimization completed!\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>