<?php

try {
    $pdo = new PDO('sqlite:database/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Creating Timetable & Hierarchy System...\n";

    // Create timetable table
    $pdo->exec("CREATE TABLE IF NOT EXISTS timetable (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        subject_id INTEGER NOT NULL,
        faculty_id INTEGER NOT NULL,
        academic_unit_id INTEGER NOT NULL,
        day_of_week VARCHAR(10) NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        room VARCHAR(50),
        academic_year VARCHAR(10) NOT NULL,
        semester INTEGER NOT NULL,
        batch VARCHAR(10),
        is_active BOOLEAN DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (subject_id) REFERENCES subject_masters(id),
        FOREIGN KEY (faculty_id) REFERENCES users(id),
        FOREIGN KEY (academic_unit_id) REFERENCES academic_units(id)
    )");

    // Create rooms table
    $pdo->exec("CREATE TABLE IF NOT EXISTS rooms (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(50) NOT NULL,
        capacity INTEGER NOT NULL,
        type VARCHAR(20) DEFAULT 'classroom',
        equipment TEXT,
        is_available BOOLEAN DEFAULT 1
    )");

    // Add hierarchy roles to users
    $pdo->exec("ALTER TABLE users ADD COLUMN designation VARCHAR(50) DEFAULT 'faculty'");
    $pdo->exec("ALTER TABLE users ADD COLUMN is_department_head BOOLEAN DEFAULT 0");

    // Insert rooms
    $rooms = [
        ['Room 101', 60, 'classroom', 'Projector, Whiteboard'],
        ['Room 201', 60, 'classroom', 'Projector, Whiteboard, AC'],
        ['Room 301', 60, 'classroom', 'Projector, Whiteboard'],
        ['Computer Lab 1', 30, 'lab', 'Computers, Projector, AC'],
        ['Computer Lab 2', 30, 'lab', 'Computers, Projector'],
        ['Physics Lab', 25, 'lab', 'Equipment, Safety gear'],
        ['Auditorium', 200, 'hall', 'Audio system, Projector']
    ];

    $pdo->exec("DELETE FROM rooms");
    $stmt = $pdo->prepare("INSERT INTO rooms (name, capacity, type, equipment) VALUES (?, ?, ?, ?)");
    foreach ($rooms as $room) {
        $stmt->execute($room);
    }

    // Add hierarchy users
    $pdo->exec("INSERT OR REPLACE INTO users (name, email, password, user_type, phone, designation, is_department_head) VALUES 
        ('Dr. Rajesh Kumar', 'rajesh.kumar@pvgs.edu', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', '9876501001', 'Assistant Professor', 1),
        ('Prof. Sunita Sharma', 'sunita.sharma@pvgs.edu', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', '9876501002', 'Associate Professor', 0),
        ('Dr. Amit Patel', 'amit.patel@pvgs.edu', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', '9876501003', 'Professor', 0),
        ('Dr. Priya Mehta', 'principal@pvgs.edu', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '9876500001', 'Principal', 0)");

    // Create comprehensive timetable for Dr. Rajesh Kumar
    $timetable = [
        // Monday
        [1, 1, 1, 'Monday', '10:00', '11:00', 'Room 201', '2023-24', 3], // Data Structures - SY
        [3, 1, 3, 'Monday', '13:00', '14:00', 'Room 301', '2022-23', 5], // DBMS - TY
        [2, 1, 1, 'Monday', '14:00', '16:00', 'Computer Lab 1', '2023-24', 3], // DS Lab - SY
        
        // Tuesday  
        [1, 1, 1, 'Tuesday', '09:00', '10:00', 'Room 201', '2023-24', 3], // Data Structures - SY
        [5, 1, 3, 'Tuesday', '17:00', '18:00', 'Room 301', '2022-23', 5], // Web Dev - TY
        
        // Wednesday
        [3, 1, 3, 'Wednesday', '11:00', '12:00', 'Room 301', '2022-23', 5], // DBMS - TY
        [4, 1, 3, 'Wednesday', '14:00', '16:00', 'Computer Lab 2', '2022-23', 5], // DBMS Lab - TY
        
        // Thursday
        [1, 1, 1, 'Thursday', '10:00', '11:00', 'Room 201', '2023-24', 3], // Data Structures - SY
        [6, 1, 3, 'Thursday', '15:00', '17:00', 'Computer Lab 2', '2022-23', 5], // Web Dev Lab - TY
        
        // Friday
        [5, 1, 3, 'Friday', '09:00', '10:00', 'Room 301', '2022-23', 5], // Web Dev - TY
        [2, 1, 1, 'Friday', '11:00', '13:00', 'Computer Lab 1', '2023-24', 3] // DS Lab - SY
    ];

    $pdo->exec("DELETE FROM timetable");
    $stmt = $pdo->prepare("INSERT INTO timetable (subject_id, faculty_id, academic_unit_id, day_of_week, start_time, end_time, room, academic_year, semester) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($timetable as $slot) {
        $stmt->execute($slot);
    }

    // Create conflicts detection table
    $pdo->exec("CREATE TABLE IF NOT EXISTS timetable_conflicts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        conflict_type VARCHAR(50) NOT NULL,
        description TEXT NOT NULL,
        timetable_id_1 INTEGER,
        timetable_id_2 INTEGER,
        severity VARCHAR(20) DEFAULT 'medium',
        resolved BOOLEAN DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    echo "✅ Timetable System Created!\n\n";
    echo "📅 Timetable: Dr. Rajesh Kumar teaches across multiple years\n";
    echo "🏢 Rooms: 7 rooms including labs and auditorium\n";
    echo "👥 Hierarchy: Principal → Dept Head → Faculty\n";
    echo "⚠️ Conflict Detection: System ready\n\n";

    echo "EDGE CASES IDENTIFIED:\n";
    echo "1. 🔴 Faculty Double Booking - Same teacher, same time, different rooms\n";
    echo "2. 🔴 Room Conflicts - Same room, same time, different classes\n";
    echo "3. 🔴 Student Conflicts - Same student in multiple classes simultaneously\n";
    echo "4. 🟡 Back-to-back Classes - No break between classes\n";
    echo "5. 🟡 Room Capacity - More students than room capacity\n";
    echo "6. 🟡 Equipment Mismatch - Lab subject in regular classroom\n";
    echo "7. 🟡 Late Evening Classes - Classes after 6 PM\n";
    echo "8. 🟡 Faculty Overload - More than 6 hours per day\n";
    echo "9. 🟡 Lunch Break Violation - Classes during 12-1 PM\n";
    echo "10. 🔵 Holiday Scheduling - Classes on holidays\n\n";

    echo "HIERARCHY ROLES:\n";
    echo "👑 Principal: Dr. Priya Mehta - Full system access\n";
    echo "🏢 Dept Head: Dr. Rajesh Kumar - Department oversight\n";
    echo "👨🏫 Faculty: Prof. Sunita, Dr. Amit - Teaching duties\n\n";

    echo "LOGIN CREDENTIALS:\n";
    echo "Principal: principal@pvgs.edu / password\n";
    echo "Dept Head: rajesh.kumar@pvgs.edu / password\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>