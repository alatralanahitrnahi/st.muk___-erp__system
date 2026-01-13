<?php

try {
    $pdo = new PDO('sqlite:database/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Creating Holiday Management System...\n";

    // Create holidays table
    $pdo->exec("CREATE TABLE IF NOT EXISTS holidays (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(100) NOT NULL,
        date DATE NOT NULL,
        type VARCHAR(20) NOT NULL,
        is_recurring BOOLEAN DEFAULT 0,
        recurrence_pattern VARCHAR(50),
        description TEXT,
        created_by INTEGER,
        approved_by INTEGER,
        status VARCHAR(20) DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id),
        FOREIGN KEY (approved_by) REFERENCES users(id)
    )");

    // Create holiday exceptions table
    $pdo->exec("CREATE TABLE IF NOT EXISTS holiday_exceptions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        holiday_id INTEGER NOT NULL,
        exception_date DATE NOT NULL,
        reason TEXT,
        created_by INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (holiday_id) REFERENCES holidays(id),
        FOREIGN KEY (created_by) REFERENCES users(id)
    )");

    // Create makeup classes table
    $pdo->exec("CREATE TABLE IF NOT EXISTS makeup_classes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        original_timetable_id INTEGER NOT NULL,
        holiday_id INTEGER NOT NULL,
        makeup_date DATE NOT NULL,
        makeup_time_start TIME NOT NULL,
        makeup_time_end TIME NOT NULL,
        makeup_room VARCHAR(50),
        status VARCHAR(20) DEFAULT 'scheduled',
        created_by INTEGER,
        approved_by INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (original_timetable_id) REFERENCES timetable(id),
        FOREIGN KEY (holiday_id) REFERENCES holidays(id),
        FOREIGN KEY (created_by) REFERENCES users(id),
        FOREIGN KEY (approved_by) REFERENCES users(id)
    )");

    // Insert sample holidays for 2024-25
    $holidays = [
        // National Holidays
        ['Independence Day', '2024-08-15', 'national', 1, 'yearly', 'National holiday - India Independence Day', 4, 4, 'approved'],
        ['Gandhi Jayanti', '2024-10-02', 'national', 1, 'yearly', 'National holiday - Mahatma Gandhi Birthday', 4, 4, 'approved'],
        ['Republic Day', '2025-01-26', 'national', 1, 'yearly', 'National holiday - India Republic Day', 4, 4, 'approved'],
        
        // Religious Holidays
        ['Diwali', '2024-11-01', 'religious', 0, null, 'Festival of Lights', 4, 4, 'approved'],
        ['Holi', '2025-03-14', 'religious', 0, null, 'Festival of Colors', 4, 4, 'approved'],
        ['Eid ul-Fitr', '2024-04-11', 'religious', 0, null, 'Islamic festival', 4, 4, 'approved'],
        ['Christmas', '2024-12-25', 'religious', 1, 'yearly', 'Christian festival', 4, 4, 'approved'],
        
        // State/Regional Holidays
        ['Maharashtra Day', '2024-05-01', 'state', 1, 'yearly', 'Maharashtra state formation day', 4, 4, 'approved'],
        ['Gudi Padwa', '2024-04-09', 'regional', 0, null, 'Marathi New Year', 4, 4, 'approved'],
        
        // Academic Holidays
        ['Summer Vacation Start', '2024-04-15', 'academic', 0, null, 'Summer vacation begins', 4, 4, 'approved'],
        ['Summer Vacation End', '2024-06-14', 'academic', 0, null, 'Summer vacation ends', 4, 4, 'approved'],
        ['Diwali Vacation Start', '2024-10-30', 'academic', 0, null, 'Diwali break begins', 4, 4, 'approved'],
        ['Diwali Vacation End', '2024-11-05', 'academic', 0, null, 'Diwali break ends', 4, 4, 'approved'],
        
        // Emergency/Special
        ['Faculty Development Program', '2024-09-15', 'institutional', 0, null, 'Mandatory FDP - No classes', 1, null, 'pending'],
        ['Sports Day', '2024-12-10', 'institutional', 0, null, 'Annual sports event', 1, null, 'pending']
    ];

    $pdo->exec("DELETE FROM holidays");
    $stmt = $pdo->prepare("INSERT INTO holidays (name, date, type, is_recurring, recurrence_pattern, description, created_by, approved_by, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($holidays as $holiday) {
        $stmt->execute($holiday);
    }

    // Insert holiday exceptions (working days during vacation)
    $exceptions = [
        [10, '2024-04-20', 'Makeup classes for lost teaching days', 1], // Summer vacation exception
        [11, '2024-06-10', 'Faculty meeting and preparation', 1]
    ];

    $stmt = $pdo->prepare("INSERT INTO holiday_exceptions (holiday_id, exception_date, reason, created_by) VALUES (?, ?, ?, ?)");
    foreach ($exceptions as $exception) {
        $stmt->execute($exception);
    }

    // Insert sample makeup classes
    $makeupClasses = [
        [1, 1, '2024-08-17', '10:00', '11:00', 'Room 201', 'scheduled', 1, 4], // Independence Day makeup
        [3, 2, '2024-10-05', '14:00', '15:00', 'Room 301', 'scheduled', 1, 4]  // Gandhi Jayanti makeup
    ];

    $stmt = $pdo->prepare("INSERT INTO makeup_classes (original_timetable_id, holiday_id, makeup_date, makeup_time_start, makeup_time_end, makeup_room, status, created_by, approved_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($makeupClasses as $makeup) {
        $stmt->execute($makeup);
    }

    echo "✅ Holiday Management System Created!\n\n";
    echo "📅 HOLIDAY TYPES:\n";
    echo "🇮🇳 National: Independence Day, Republic Day, Gandhi Jayanti\n";
    echo "🕉️ Religious: Diwali, Holi, Eid, Christmas\n";
    echo "🏛️ State/Regional: Maharashtra Day, Gudi Padwa\n";
    echo "🎓 Academic: Summer/Diwali vacations\n";
    echo "🏢 Institutional: FDP, Sports Day\n\n";

    echo "⚠️ HOLIDAY EDGE CASES:\n\n";
    echo "🔴 CRITICAL CONFLICTS:\n";
    echo "1. Exam Scheduling on Holidays - Exams scheduled on religious holidays\n";
    echo "2. Mandatory Events on Holidays - Faculty meetings on national holidays\n";
    echo "3. Holiday Overlap - Multiple holidays on same date\n";
    echo "4. Vacation Period Classes - Regular classes during declared vacation\n";
    echo "5. Makeup Class Conflicts - Makeup classes conflicting with existing schedule\n\n";

    echo "🟡 POLICY VIOLATIONS:\n";
    echo "6. Insufficient Notice - Holiday declared less than 7 days in advance\n";
    echo "7. Excessive Makeup Load - More than 2 makeup classes per day\n";
    echo "8. Weekend Makeup Abuse - Too many weekend makeup classes\n";
    echo "9. Faculty Overload - Makeup classes causing faculty hour violations\n";
    echo "10. Room Unavailability - Makeup classes in occupied/unavailable rooms\n\n";

    echo "🔵 ADMINISTRATIVE ISSUES:\n";
    echo "11. Unapproved Holidays - Faculty creating holidays without approval\n";
    echo "12. Recurring Holiday Drift - Annual holidays shifting dates\n";
    echo "13. Cross-Department Impact - CS holiday affecting Commerce classes\n";
    echo "14. Student Notification Delay - Students not informed about changes\n";
    echo "15. Academic Calendar Mismatch - Holidays not aligned with university calendar\n\n";

    echo "🟢 BUSINESS LOGIC:\n";
    echo "16. Compensation Logic - How to handle lost teaching hours\n";
    echo "17. Attendance Impact - Holiday attendance marking policies\n";
    echo "18. Fee Adjustment - Fee implications for extended vacations\n";
    echo "19. Faculty Salary - Payment during extended holidays\n";
    echo "20. External Dependencies - Holidays affecting external partnerships\n\n";

    echo "👑 APPROVAL HIERARCHY:\n";
    echo "• National/Religious: Auto-approved (Government mandated)\n";
    echo "• State/Regional: Principal approval required\n";
    echo "• Academic: Principal + Academic Committee\n";
    echo "• Institutional: Department Head → Principal\n";
    echo "• Emergency: Principal immediate approval\n\n";

    echo "🔄 RECURRING HOLIDAY RULES:\n";
    echo "• Yearly: Same date every year (Independence Day)\n";
    echo "• Lunar: Based on lunar calendar (Diwali, Eid)\n";
    echo "• Calculated: Based on formula (Easter)\n";
    echo "• Floating: Different dates each year (Sports Day)\n\n";

    echo "📋 MAKEUP CLASS POLICIES:\n";
    echo "• Maximum 2 makeup classes per day per faculty\n";
    echo "• No makeup on consecutive holidays\n";
    echo "• Weekend makeup requires student consent\n";
    echo "• Makeup must be within 30 days of original class\n";
    echo "• Same room preference for makeup classes\n\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>