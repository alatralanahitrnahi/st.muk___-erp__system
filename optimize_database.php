<?php
// Complete Database Schema Setup and Optimization
$dbPath = '/workspaces/st.muk___-erp__system/database/database.sqlite';

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "🔧 PVGS ERP Database Optimization\n";
    echo "==================================\n\n";

    // Create missing tables with proper schema
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
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (department_id) REFERENCES departments(id)
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
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (program_id) REFERENCES programs(id),
            FOREIGN KEY (department_id) REFERENCES departments(id)
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
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (subject_id) REFERENCES subjects(id),
            FOREIGN KEY (faculty_id) REFERENCES faculty(id)
        )",

        // Add missing columns to existing tables
        "ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'student'",
        "ALTER TABLE students ADD COLUMN name VARCHAR(100)",
        "ALTER TABLE students ADD COLUMN department_id INTEGER"
    ];

    foreach ($tables as $sql) {
        try {
            $pdo->exec($sql);
            echo "✅ Schema updated successfully\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'duplicate column name') === false && 
                strpos($e->getMessage(), 'already exists') === false) {
                echo "⚠️  Schema warning: " . $e->getMessage() . "\n";
            }
        }
    }

    // Insert sample data for testing
    echo "\n📊 Populating sample data...\n";

    // Sample faculty data
    $pdo->exec("INSERT OR IGNORE INTO faculty (name, email, department_id, designation) VALUES 
        ('Dr. Rajesh Kumar', 'rajesh@pvgs.edu', 1, 'Assistant Professor'),
        ('Prof. Sunita Sharma', 'sunita@pvgs.edu', 1, 'Associate Professor'),
        ('Dr. Amit Patel', 'amit@pvgs.edu', 2, 'Professor')");

    // Sample subjects data
    $pdo->exec("INSERT OR IGNORE INTO subjects (name, code, credits, program_id, department_id) VALUES 
        ('Data Structures', 'CS201', 4, 1, 1),
        ('Database Management', 'CS301', 4, 1, 1),
        ('Web Development', 'CS302', 3, 1, 1),
        ('Financial Accounting', 'COM101', 3, 2, 2)");

    // Sample timetable entries
    $pdo->exec("INSERT OR IGNORE INTO timetable_entries (subject_id, faculty_id, class_id, room_number, day_of_week, start_time, end_time) VALUES 
        (1, 1, 'SY-CS-A', 'Room-201', 1, '10:00', '11:00'),
        (2, 1, 'TY-CS-A', 'Room-301', 1, '13:00', '14:00'),
        (3, 1, 'TY-CS-A', 'Room-301', 2, '17:00', '18:00')");

    // Update student names
    $pdo->exec("UPDATE students SET name = 'Student ' || id WHERE name IS NULL");

    // Verify data integrity
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM students");
    $studentCount = $stmt->fetch()['count'];

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM faculty");
    $facultyCount = $stmt->fetch()['count'];

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM subjects");
    $subjectCount = $stmt->fetch()['count'];

    echo "\n📈 Data Summary:\n";
    echo "Students: $studentCount\n";
    echo "Faculty: $facultyCount\n";
    echo "Subjects: $subjectCount\n";

    echo "\n🎉 Database optimization completed successfully!\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>