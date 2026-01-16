<?php

$db = new PDO('sqlite:database/database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Clear existing data
$db->exec('DELETE FROM users');
$db->exec('DELETE FROM students');

// Insert test users
$users = [
    ['Super Admin', 'admin@pvgs.edu', 'admin'],
    ['Principal', 'principal@pvgs.edu', 'admin'],
    ['Registrar', 'registrar@pvgs.edu', 'staff'],
    ['Faculty 1', 'faculty1@pvgs.edu', 'faculty'],
    ['Student 1', 'student1@pvgs.edu', 'student'],
];

foreach ($users as $user) {
    $stmt = $db->prepare('INSERT INTO users (name, email, password, user_type, created_at, updated_at) VALUES (?, ?, ?, ?, datetime("now"), datetime("now"))');
    $stmt->execute([$user[0], $user[1], password_hash('password123', PASSWORD_BCRYPT), $user[2]]);

    if ($user[2] === 'student') {
        $userId = $db->lastInsertId();
        $studentStmt = $db->prepare('INSERT INTO students (user_id, program_id, category_id, application_status, application_date, application_fee_paid, parent_name, parent_phone, created_at, updated_at) VALUES (?, 1, 1, "approved", date("now"), 1, "Parent", "1234567890", datetime("now"), datetime("now"))');
        $studentStmt->execute([$userId]);
    }
}

echo "Test users created successfully!\n";
echo "Login credentials:\n";
echo "Admin: admin@pvgs.edu / password123\n";
echo "Principal: principal@pvgs.edu / password123\n";
echo "Registrar: registrar@pvgs.edu / password123\n";
echo "Faculty: faculty1@pvgs.edu / password123\n";
echo "Student: student1@pvgs.edu / password123\n";
