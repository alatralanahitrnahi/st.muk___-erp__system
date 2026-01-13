<?php

try {
    $pdo = new PDO('sqlite:database/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Adding minimal BSc CS data...\n";

    // Add CS subjects only
    $subjects = [
        ['Data Structures', 'CS201', 'theory', 4, 'Linear and non-linear data structures'],
        ['Data Structures Lab', 'CS201P', 'practical', 2, 'Implementation of data structures'],
        ['Database Management', 'CS301', 'theory', 4, 'Database design and SQL'],
        ['DBMS Lab', 'CS301P', 'practical', 2, 'Database implementation'],
        ['Web Development', 'CS302', 'theory', 4, 'HTML, CSS, JavaScript, PHP'],
        ['Web Development Lab', 'CS302P', 'practical', 2, 'Web applications']
    ];

    $pdo->exec("DELETE FROM subject_masters WHERE code LIKE 'CS%'");
    $stmt = $pdo->prepare("INSERT INTO subject_masters (name, code, type, credits, description) VALUES (?, ?, ?, ?, ?)");
    foreach ($subjects as $subject) {
        $stmt->execute($subject);
    }

    // Add faculty user only
    $pdo->exec("INSERT OR REPLACE INTO users (name, email, password, user_type, phone) VALUES 
        ('Dr. Rajesh Kumar', 'rajesh.kumar@pvgs.edu', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', '9876501001')");

    echo "✅ Basic BSc CS data added!\n";
    echo "📚 Subjects: " . count($subjects) . " CS subjects\n";
    echo "👨🏫 Faculty: Dr. Rajesh Kumar\n";
    echo "\nFaculty Login: rajesh.kumar@pvgs.edu / password\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>