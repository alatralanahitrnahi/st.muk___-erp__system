<?php

try {
    $pdo = new PDO('sqlite:database/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Adding BSc Computer Science data...\n";

    // Add CS subjects
    $subjects = [
        ['Data Structures', 'CS201', 'theory', 4, 'Linear and non-linear data structures'],
        ['Data Structures Lab', 'CS201P', 'practical', 2, 'Implementation of data structures'],
        ['Database Management', 'CS301', 'theory', 4, 'Database design and SQL'],
        ['DBMS Lab', 'CS301P', 'practical', 2, 'Database implementation'],
        ['Web Development', 'CS302', 'theory', 4, 'HTML, CSS, JavaScript, PHP'],
        ['Web Development Lab', 'CS302P', 'practical', 2, 'Web applications'],
        ['Computer Networks', 'CS304', 'theory', 4, 'Network protocols'],
        ['Artificial Intelligence', 'CS305', 'theory', 4, 'AI concepts'],
        ['Machine Learning', 'CS306', 'theory', 4, 'ML algorithms'],
        ['Cyber Security', 'CS307', 'theory', 4, 'Security practices']
    ];

    $pdo->exec("DELETE FROM subject_masters WHERE code LIKE 'CS%'");
    $stmt = $pdo->prepare("INSERT INTO subject_masters (name, code, type, credits, description) VALUES (?, ?, ?, ?, ?)");
    foreach ($subjects as $subject) {
        $stmt->execute($subject);
    }

    // Add users first (students need user_id)
    $users = [
        ['Aarav Sharma', 'aarav.sharma@pvgs.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '9876543210'],
        ['Vivaan Patel', 'vivaan.patel@pvgs.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '9876543211'],
        ['Priya Sharma', 'priya.sharma@pvgs.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '9876543301'],
        ['Ananya Patel', 'ananya.patel@pvgs.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '9876543302'],
        ['Dr. Rajesh Kumar', 'rajesh.kumar@pvgs.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', '9876501001']
    ];

    $stmt = $pdo->prepare("INSERT OR REPLACE INTO users (name, email, password, user_type, phone) VALUES (?, ?, ?, ?, ?)");
    foreach ($users as $user) {
        $stmt->execute($user);
    }

    // Get user IDs for students
    $studentUsers = $pdo->query("SELECT id, name FROM users WHERE user_type = 'student' ORDER BY id DESC LIMIT 4")->fetchAll();

    // Add student records
    $students = [
        [$studentUsers[0]['id'], 1, 1, 'CS2101', 'PRN2101', 'active', '2023-06-15', 'approved', '2023-06-01', 1],
        [$studentUsers[1]['id'], 1, 1, 'CS2102', 'PRN2102', 'active', '2023-06-15', 'approved', '2023-06-01', 1],
        [$studentUsers[2]['id'], 1, 1, 'CS3101', 'PRN3101', 'active', '2022-06-15', 'approved', '2022-06-01', 1],
        [$studentUsers[3]['id'], 1, 1, 'CS3102', 'PRN3102', 'active', '2022-06-15', 'approved', '2022-06-01', 1]
    ];

    $stmt = $pdo->prepare("INSERT INTO students (user_id, program_id, category_id, admission_number, prn_number, status, admission_date, application_status, application_date, application_fee_paid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($students as $student) {
        $stmt->execute($student);
    }

    echo "✅ BSc Computer Science data added successfully!\n";
    echo "📚 Subjects: " . count($subjects) . " CS subjects\n";
    echo "👥 Students: " . count($students) . " students\n";
    echo "👨🏫 Faculty: Dr. Rajesh Kumar\n\n";
    
    echo "Login Credentials:\n";
    echo "Faculty: rajesh.kumar@pvgs.edu / password\n";
    echo "Student: aarav.sharma@pvgs.edu / password\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>