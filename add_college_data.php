<?php

try {
    $pdo = new PDO('sqlite:database/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Adding BSc Computer Science data...\n";

    // Add BSc CS subjects directly to existing structure
    $subjects = [
        // SY BSc CS subjects
        ['Data Structures', 'CS201', 'theory', 4, 'Linear and non-linear data structures'],
        ['Data Structures Lab', 'CS201P', 'practical', 2, 'Implementation of data structures'],
        ['Computer Organization', 'CS202', 'theory', 4, 'Computer architecture'],
        ['Algorithms', 'CS204', 'theory', 4, 'Algorithm design and analysis'],
        ['Operating Systems', 'CS203', 'theory', 4, 'OS concepts'],
        
        // TY BSc CS subjects  
        ['Database Management', 'CS301', 'theory', 4, 'Database design and SQL'],
        ['DBMS Lab', 'CS301P', 'practical', 2, 'Database implementation'],
        ['Web Development', 'CS302', 'theory', 4, 'HTML, CSS, JavaScript, PHP'],
        ['Web Development Lab', 'CS302P', 'practical', 2, 'Web applications'],
        ['Software Engineering', 'CS303', 'theory', 4, 'SDLC and methodologies'],
        ['Computer Networks', 'CS304', 'theory', 4, 'Network protocols'],
        ['Artificial Intelligence', 'CS305', 'theory', 4, 'AI concepts'],
        ['Machine Learning', 'CS306', 'theory', 4, 'ML algorithms'],
        ['Cyber Security', 'CS307', 'theory', 4, 'Security practices'],
        ['Project Work', 'CS308', 'project', 6, 'Final year project']
    ];

    // Clear and insert subjects
    $pdo->exec("DELETE FROM subject_masters WHERE code LIKE 'CS%'");
    
    $stmt = $pdo->prepare("INSERT INTO subject_masters (name, code, type, credits, description) VALUES (?, ?, ?, ?, ?)");
    foreach ($subjects as $subject) {
        $stmt->execute($subject);
    }

    // Add realistic student data
    $students = [
        // SY BSc CS
        ['Aarav Sharma', 'CS2101', 'aarav.sharma@pvgs.edu', '9876543210', '2003-05-15', 'male', 'open'],
        ['Vivaan Patel', 'CS2102', 'vivaan.patel@pvgs.edu', '9876543211', '2003-08-22', 'male', 'obc'],
        ['Aditya Kumar', 'CS2103', 'aditya.kumar@pvgs.edu', '9876543212', '2003-03-10', 'male', 'open'],
        ['Vihaan Singh', 'CS2104', 'vihaan.singh@pvgs.edu', '9876543213', '2003-11-05', 'male', 'open'],
        ['Arjun Gupta', 'CS2105', 'arjun.gupta@pvgs.edu', '9876543214', '2003-07-18', 'male', 'sc'],
        ['Sai Reddy', 'CS2106', 'sai.reddy@pvgs.edu', '9876543215', '2003-09-12', 'male', 'open'],
        ['Reyansh Joshi', 'CS2107', 'reyansh.joshi@pvgs.edu', '9876543216', '2003-04-25', 'male', 'open'],
        ['Ayaan Shah', 'CS2108', 'ayaan.shah@pvgs.edu', '9876543217', '2003-12-08', 'male', 'obc'],
        ['Krishna Nair', 'CS2109', 'krishna.nair@pvgs.edu', '9876543218', '2003-06-30', 'male', 'open'],
        ['Ishaan Mehta', 'CS2110', 'ishaan.mehta@pvgs.edu', '9876543219', '2003-10-14', 'male', 'open'],
        
        // TY BSc CS
        ['Priya Sharma', 'CS3101', 'priya.sharma@pvgs.edu', '9876543301', '2002-04-12', 'female', 'open'],
        ['Ananya Patel', 'CS3102', 'ananya.patel@pvgs.edu', '9876543302', '2002-07-25', 'female', 'obc'],
        ['Kavya Kumar', 'CS3103', 'kavya.kumar@pvgs.edu', '9876543303', '2002-01-18', 'female', 'open'],
        ['Diya Singh', 'CS3104', 'diya.singh@pvgs.edu', '9876543304', '2002-09-08', 'female', 'open'],
        ['Aadhya Gupta', 'CS3105', 'aadhya.gupta@pvgs.edu', '9876543305', '2002-06-15', 'female', 'sc'],
        ['Saanvi Agarwal', 'CS3106', 'saanvi.agarwal@pvgs.edu', '9876543306', '2002-11-22', 'female', 'open'],
        ['Myra Kapoor', 'CS3107', 'myra.kapoor@pvgs.edu', '9876543307', '2002-03-05', 'female', 'ews'],
        ['Kiara Jain', 'CS3108', 'kiara.jain@pvgs.edu', '9876543308', '2002-08-28', 'female', 'open'],
        ['Riya Desai', 'CS3109', 'riya.desai@pvgs.edu', '9876543309', '2002-12-10', 'female', 'obc'],
        ['Tara Nair', 'CS3110', 'tara.nair@pvgs.edu', '9876543310', '2002-05-20', 'female', 'open']
    ];

    $pdo->exec("DELETE FROM students WHERE roll_number LIKE 'CS%'");
    
    $stmt = $pdo->prepare("INSERT INTO students (name, roll_number, email, phone, date_of_birth, gender, category) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($students as $student) {
        $stmt->execute($student);
    }

    // Add faculty
    $pdo->exec("INSERT OR REPLACE INTO users (name, email, password, user_type, phone) VALUES 
        ('Dr. Rajesh Kumar', 'rajesh.kumar@pvgs.edu', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', '9876501001'),
        ('Prof. Sunita Sharma', 'sunita.sharma@pvgs.edu', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', '9876501002')");

    echo "✅ BSc Computer Science data added successfully!\n";
    echo "📚 Subjects: " . count($subjects) . " CS subjects added\n";
    echo "👥 Students: " . count($students) . " students added\n";
    echo "👨🏫 Faculty: 2 faculty members added\n\n";
    
    echo "Faculty Login:\n";
    echo "Email: rajesh.kumar@pvgs.edu | Password: password\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>