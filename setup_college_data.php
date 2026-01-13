<?php

try {
    $pdo = new PDO('sqlite:database/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Setting up comprehensive BSc Computer Science data...\n";

    // Clear existing data
    $pdo->exec("DELETE FROM subjects");
    $pdo->exec("DELETE FROM students");
    $pdo->exec("DELETE FROM programs");
    $pdo->exec("DELETE FROM departments");

    // Insert Departments
    $pdo->exec("INSERT INTO departments (name, code, description, is_active) VALUES 
        ('Computer Science', 'CS', 'Department of Computer Science and Information Technology', 1),
        ('Commerce', 'COM', 'Department of Commerce and Management', 1),
        ('Arts', 'ARTS', 'Department of Arts and Humanities', 1)");

    // Insert Programs
    $pdo->exec("INSERT INTO programs (name, code, department_id, level, duration_years, total_semesters, description, is_active) VALUES 
        ('Bachelor of Science in Computer Science', 'BSc CS', 1, 'UG', 3, 6, 'Undergraduate program in Computer Science', 1),
        ('Bachelor of Commerce', 'BCom', 2, 'UG', 3, 6, 'Undergraduate program in Commerce', 1),
        ('Bachelor of Arts', 'BA', 3, 'UG', 3, 6, 'Undergraduate program in Arts', 1)");

    // Insert BSc CS Subjects - Complete 3-year curriculum
    $subjects = [
        // First Year (FY) - Semester 1
        ['Programming Fundamentals', 'CS101', 1, 1, 1, 'Theory', 4, 'Introduction to programming concepts and C language'],
        ['Programming Lab', 'CS101P', 1, 1, 1, 'Practical', 2, 'Hands-on programming practice'],
        ['Mathematics I', 'MATH101', 1, 1, 1, 'Theory', 4, 'Calculus and analytical geometry'],
        ['Physics', 'PHY101', 1, 1, 1, 'Theory', 4, 'Basic physics concepts'],
        ['English Communication', 'ENG101', 1, 1, 1, 'Theory', 3, 'Communication skills'],
        ['Environmental Studies', 'EVS101', 1, 1, 1, 'Theory', 2, 'Environmental awareness'],

        // First Year - Semester 2
        ['Object Oriented Programming', 'CS102', 1, 1, 2, 'Theory', 4, 'OOP concepts using C++'],
        ['OOP Lab', 'CS102P', 1, 1, 2, 'Practical', 2, 'C++ programming lab'],
        ['Mathematics II', 'MATH102', 1, 1, 2, 'Theory', 4, 'Linear algebra and statistics'],
        ['Digital Electronics', 'CS103', 1, 1, 2, 'Theory', 4, 'Digital logic and circuits'],
        ['Digital Electronics Lab', 'CS103P', 1, 1, 2, 'Practical', 2, 'Digital circuits lab'],
        ['Computer Fundamentals', 'CS104', 1, 1, 2, 'Theory', 3, 'Basic computer concepts'],

        // Second Year (SY) - Semester 3
        ['Data Structures', 'CS201', 1, 2, 3, 'Theory', 4, 'Linear and non-linear data structures'],
        ['Data Structures Lab', 'CS201P', 1, 2, 3, 'Practical', 2, 'Implementation of data structures'],
        ['Computer Organization', 'CS202', 1, 2, 3, 'Theory', 4, 'Computer architecture and organization'],
        ['Discrete Mathematics', 'MATH201', 1, 2, 3, 'Theory', 4, 'Mathematical foundations for CS'],
        ['Operating Systems', 'CS203', 1, 2, 3, 'Theory', 4, 'OS concepts and principles'],
        ['Technical Communication', 'ENG201', 1, 2, 3, 'Theory', 2, 'Technical writing skills'],

        // Second Year - Semester 4
        ['Algorithms', 'CS204', 1, 2, 4, 'Theory', 4, 'Algorithm design and analysis'],
        ['Algorithms Lab', 'CS204P', 1, 2, 4, 'Practical', 2, 'Algorithm implementation'],
        ['Computer Networks', 'CS205', 1, 2, 4, 'Theory', 4, 'Network protocols and concepts'],
        ['Software Engineering', 'CS206', 1, 2, 4, 'Theory', 4, 'Software development lifecycle'],
        ['Java Programming', 'CS207', 1, 2, 4, 'Theory', 4, 'Java language and concepts'],
        ['Java Lab', 'CS207P', 1, 2, 4, 'Practical', 2, 'Java programming practice'],

        // Third Year (TY) - Semester 5
        ['Database Management Systems', 'CS301', 1, 3, 5, 'Theory', 4, 'Database design and SQL'],
        ['DBMS Lab', 'CS301P', 1, 3, 5, 'Practical', 2, 'Database implementation'],
        ['Web Development', 'CS302', 1, 3, 5, 'Theory', 4, 'HTML, CSS, JavaScript, PHP'],
        ['Web Development Lab', 'CS302P', 1, 3, 5, 'Practical', 2, 'Web application development'],
        ['Computer Graphics', 'CS303', 1, 3, 5, 'Theory', 4, 'Graphics algorithms and techniques'],
        ['Graphics Lab', 'CS303P', 1, 3, 5, 'Practical', 2, 'Graphics programming'],
        ['Artificial Intelligence', 'CS304', 1, 3, 5, 'Theory', 4, 'AI concepts and techniques'],

        // Third Year - Semester 6
        ['Machine Learning', 'CS305', 1, 3, 6, 'Theory', 4, 'ML algorithms and applications'],
        ['ML Lab', 'CS305P', 1, 3, 6, 'Practical', 2, 'ML implementation'],
        ['Mobile App Development', 'CS306', 1, 3, 6, 'Theory', 4, 'Android/iOS development'],
        ['Mobile Lab', 'CS306P', 1, 3, 6, 'Practical', 2, 'Mobile app projects'],
        ['Cyber Security', 'CS307', 1, 3, 6, 'Theory', 4, 'Security concepts and practices'],
        ['Project Work', 'CS308', 1, 3, 6, 'Project', 6, 'Final year project'],
        ['Seminar', 'CS309', 1, 3, 6, 'Seminar', 2, 'Technical presentation']
    ];

    $stmt = $pdo->prepare("INSERT INTO subjects (name, code, program_id, academic_unit_id, semester, type, credits, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($subjects as $subject) {
        $stmt->execute($subject);
    }

    // Insert realistic student data
    $students = [
        // Second Year BSc CS Students
        ['Aarav Sharma', 'CS2101', 'aarav.sharma@pvgs.edu', '9876543210', '2003-05-15', 'male', 'open', 1, 2, '2023-24', 'Mumbai', 'Maharashtra', '400001'],
        ['Vivaan Patel', 'CS2102', 'vivaan.patel@pvgs.edu', '9876543211', '2003-08-22', 'male', 'obc', 1, 2, '2023-24', 'Pune', 'Maharashtra', '411001'],
        ['Aditya Kumar', 'CS2103', 'aditya.kumar@pvgs.edu', '9876543212', '2003-03-10', 'male', 'open', 1, 2, '2023-24', 'Nagpur', 'Maharashtra', '440001'],
        ['Vihaan Singh', 'CS2104', 'vihaan.singh@pvgs.edu', '9876543213', '2003-11-05', 'male', 'open', 1, 2, '2023-24', 'Nashik', 'Maharashtra', '422001'],
        ['Arjun Gupta', 'CS2105', 'arjun.gupta@pvgs.edu', '9876543214', '2003-07-18', 'male', 'sc', 1, 2, '2023-24', 'Aurangabad', 'Maharashtra', '431001'],
        ['Sai Reddy', 'CS2106', 'sai.reddy@pvgs.edu', '9876543215', '2003-09-12', 'male', 'open', 1, 2, '2023-24', 'Solapur', 'Maharashtra', '413001'],
        ['Reyansh Joshi', 'CS2107', 'reyansh.joshi@pvgs.edu', '9876543216', '2003-04-25', 'male', 'open', 1, 2, '2023-24', 'Kolhapur', 'Maharashtra', '416001'],
        ['Ayaan Shah', 'CS2108', 'ayaan.shah@pvgs.edu', '9876543217', '2003-12-08', 'male', 'obc', 1, 2, '2023-24', 'Sangli', 'Maharashtra', '416401'],
        ['Krishna Nair', 'CS2109', 'krishna.nair@pvgs.edu', '9876543218', '2003-06-30', 'male', 'open', 1, 2, '2023-24', 'Satara', 'Maharashtra', '415001'],
        ['Ishaan Mehta', 'CS2110', 'ishaan.mehta@pvgs.edu', '9876543219', '2003-10-14', 'male', 'open', 1, 2, '2023-24', 'Ahmednagar', 'Maharashtra', '414001'],
        ['Aryan Verma', 'CS2111', 'aryan.verma@pvgs.edu', '9876543220', '2003-02-28', 'male', 'ews', 1, 2, '2023-24', 'Latur', 'Maharashtra', '413512'],
        ['Rudra Pandey', 'CS2112', 'rudra.pandey@pvgs.edu', '9876543221', '2003-08-16', 'male', 'open', 1, 2, '2023-24', 'Nanded', 'Maharashtra', '431601'],
        ['Atharv Mishra', 'CS2113', 'atharv.mishra@pvgs.edu', '9876543222', '2003-05-03', 'male', 'obc', 1, 2, '2023-24', 'Jalgaon', 'Maharashtra', '425001'],

        // Third Year BSc CS Students
        ['Priya Sharma', 'CS3101', 'priya.sharma@pvgs.edu', '9876543301', '2002-04-12', 'female', 'open', 1, 3, '2022-23', 'Mumbai', 'Maharashtra', '400002'],
        ['Ananya Patel', 'CS3102', 'ananya.patel@pvgs.edu', '9876543302', '2002-07-25', 'female', 'obc', 1, 3, '2022-23', 'Pune', 'Maharashtra', '411002'],
        ['Kavya Kumar', 'CS3103', 'kavya.kumar@pvgs.edu', '9876543303', '2002-01-18', 'female', 'open', 1, 3, '2022-23', 'Nagpur', 'Maharashtra', '440002'],
        ['Diya Singh', 'CS3104', 'diya.singh@pvgs.edu', '9876543304', '2002-09-08', 'female', 'open', 1, 3, '2022-23', 'Nashik', 'Maharashtra', '422002'],
        ['Aadhya Gupta', 'CS3105', 'aadhya.gupta@pvgs.edu', '9876543305', '2002-06-15', 'female', 'sc', 1, 3, '2022-23', 'Aurangabad', 'Maharashtra', '431002'],
        ['Saanvi Agarwal', 'CS3106', 'saanvi.agarwal@pvgs.edu', '9876543306', '2002-11-22', 'female', 'open', 1, 3, '2022-23', 'Solapur', 'Maharashtra', '413002'],
        ['Myra Kapoor', 'CS3107', 'myra.kapoor@pvgs.edu', '9876543307', '2002-03-05', 'female', 'ews', 1, 3, '2022-23', 'Kolhapur', 'Maharashtra', '416002'],
        ['Kiara Jain', 'CS3108', 'kiara.jain@pvgs.edu', '9876543308', '2002-08-28', 'female', 'open', 1, 3, '2022-23', 'Sangli', 'Maharashtra', '416402'],
        ['Riya Desai', 'CS3109', 'riya.desai@pvgs.edu', '9876543309', '2002-12-10', 'female', 'obc', 1, 3, '2022-23', 'Satara', 'Maharashtra', '415002'],
        ['Tara Nair', 'CS3110', 'tara.nair@pvgs.edu', '9876543310', '2002-05-20', 'female', 'open', 1, 3, '2022-23', 'Ahmednagar', 'Maharashtra', '414002']
    ];

    $stmt = $pdo->prepare("INSERT INTO students (name, roll_number, email, phone, date_of_birth, gender, category, program_id, current_academic_unit_id, academic_year, city, state, pincode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($students as $student) {
        $stmt->execute($student);
    }

    // Insert Faculty Data
    $pdo->exec("INSERT OR REPLACE INTO users (name, email, password, user_type, phone, department_id, employee_id) VALUES 
        ('Dr. Rajesh Kumar', 'rajesh.kumar@pvgs.edu', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', '9876501001', 1, 'FAC001'),
        ('Prof. Sunita Sharma', 'sunita.sharma@pvgs.edu', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', '9876501002', 1, 'FAC002'),
        ('Dr. Amit Patel', 'amit.patel@pvgs.edu', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', '9876501003', 1, 'FAC003'),
        ('Prof. Meera Joshi', 'meera.joshi@pvgs.edu', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', '9876501004', 1, 'FAC004')");

    // Create timetable table
    $pdo->exec("CREATE TABLE IF NOT EXISTS timetable (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        subject_id INTEGER NOT NULL,
        faculty_id INTEGER NOT NULL,
        day_of_week VARCHAR(10) NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        room VARCHAR(50),
        academic_year VARCHAR(10),
        semester INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (subject_id) REFERENCES subjects(id),
        FOREIGN KEY (faculty_id) REFERENCES users(id)
    )");

    // Insert sample timetable
    $pdo->exec("INSERT INTO timetable (subject_id, faculty_id, day_of_week, start_time, end_time, room, academic_year, semester) VALUES 
        (13, 1, 'Monday', '09:00', '10:00', 'Room 201', '2023-24', 3),
        (14, 1, 'Monday', '11:15', '13:15', 'Computer Lab 1', '2023-24', 3),
        (25, 1, 'Monday', '14:00', '15:00', 'Room 301', '2023-24', 5),
        (13, 1, 'Tuesday', '10:00', '11:00', 'Room 201', '2023-24', 3),
        (25, 1, 'Wednesday', '09:00', '10:00', 'Room 301', '2023-24', 5),
        (26, 1, 'Wednesday', '11:15', '13:15', 'Computer Lab 2', '2023-24', 5)");

    echo "✅ BSc Computer Science data setup completed!\n";
    echo "📚 Subjects: " . count($subjects) . " subjects across 6 semesters\n";
    echo "👥 Students: " . count($students) . " students (SY & TY BSc CS)\n";
    echo "👨‍🏫 Faculty: 4 faculty members\n";
    echo "🏫 Departments: Computer Science, Commerce, Arts\n";
    echo "🎓 Programs: BSc CS, BCom, BA\n";
    echo "⏰ Timetable: Sample schedule created\n\n";

    echo "Faculty Login Credentials:\n";
    echo "Email: rajesh.kumar@pvgs.edu | Password: password\n";
    echo "Email: sunita.sharma@pvgs.edu | Password: password\n\n";

    echo "Sample Student Roll Numbers:\n";
    echo "SY BSc CS: CS2101 to CS2113\n";
    echo "TY BSc CS: CS3101 to CS3110\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>