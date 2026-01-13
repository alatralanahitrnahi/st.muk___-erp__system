<?php

try {
    $pdo = new PDO('sqlite:database/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check existing tables
    $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
    echo "Existing tables:\n";
    while ($row = $result->fetch()) {
        echo "- " . $row['name'] . "\n";
    }

    // Check students table structure
    $result = $pdo->query("PRAGMA table_info(students)");
    echo "\nStudents table columns:\n";
    while ($row = $result->fetch()) {
        echo "- " . $row['name'] . " (" . $row['type'] . ")\n";
    }

    // Add BSc CS data using correct column names
    echo "\nAdding BSc Computer Science data...\n";

    // Add subjects to subject_masters
    $subjects = [
        ['Data Structures', 'CS201', 'theory', 4, 'Linear and non-linear data structures'],
        ['Data Structures Lab', 'CS201P', 'practical', 2, 'Implementation of data structures'],
        ['Database Management', 'CS301', 'theory', 4, 'Database design and SQL'],
        ['DBMS Lab', 'CS301P', 'practical', 2, 'Database implementation'],
        ['Web Development', 'CS302', 'theory', 4, 'HTML, CSS, JavaScript, PHP'],
        ['Web Development Lab', 'CS302P', 'practical', 2, 'Web applications'],
        ['Computer Networks', 'CS304', 'theory', 4, 'Network protocols'],
        ['Artificial Intelligence', 'CS305', 'theory', 4, 'AI concepts']
    ];

    $pdo->exec("DELETE FROM subject_masters WHERE code LIKE 'CS%'");
    $stmt = $pdo->prepare("INSERT INTO subject_masters (name, code, type, credits, description) VALUES (?, ?, ?, ?, ?)");
    foreach ($subjects as $subject) {
        $stmt->execute($subject);
    }

    // Add students using correct column names
    $students = [
        ['Aarav Sharma', 'aarav.sharma@pvgs.edu', '9876543210', '2003-05-15', 'male', 'open', 1, 2, '2023-24'],
        ['Vivaan Patel', 'vivaan.patel@pvgs.edu', '9876543211', '2003-08-22', 'male', 'obc', 1, 2, '2023-24'],
        ['Priya Sharma', 'priya.sharma@pvgs.edu', '9876543301', '2002-04-12', 'female', 'open', 1, 3, '2022-23'],
        ['Ananya Patel', 'ananya.patel@pvgs.edu', '9876543302', '2002-07-25', 'female', 'obc', 1, 3, '2022-23']
    ];

    $stmt = $pdo->prepare("INSERT INTO students (name, email, phone, date_of_birth, gender, category, program_id, current_academic_unit_id, academic_year) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($students as $student) {
        $stmt->execute($student);
    }

    // Add faculty
    $pdo->exec("INSERT OR REPLACE INTO users (name, email, password, user_type, phone) VALUES 
        ('Dr. Rajesh Kumar', 'rajesh.kumar@pvgs.edu', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', '9876501001')");

    echo "✅ Data added successfully!\n";
    echo "📚 Subjects: " . count($subjects) . " CS subjects\n";
    echo "👥 Students: " . count($students) . " students\n";
    echo "👨🏫 Faculty: Dr. Rajesh Kumar added\n";
    echo "\nLogin: rajesh.kumar@pvgs.edu / password\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>