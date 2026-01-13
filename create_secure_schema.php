<?php
// Secure Database Schema for 8 Core Institutional Modules
// PVGS ERP - Production Ready Implementation

$dbPath = __DIR__ . '/database/database.sqlite';

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔒 Creating Secure Database Schema for 8 Core Modules\n";
    echo "====================================================\n\n";
    
    // 1. FRONT OFFICE MODULE
    echo "1️⃣ Front Office (Gateway) Module...\n";
    
    // Inquiry & CRM
    $pdo->exec("CREATE TABLE IF NOT EXISTS inquiries (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        inquiry_number VARCHAR(20) UNIQUE NOT NULL,
        source ENUM('walk_in', 'phone', 'web', 'referral') NOT NULL,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(15) NOT NULL,
        email VARCHAR(100),
        program_interest VARCHAR(100),
        counselor_id INTEGER,
        status ENUM('new', 'contacted', 'follow_up', 'converted', 'lost') DEFAULT 'new',
        follow_up_date DATE,
        conversion_date DATE,
        notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (counselor_id) REFERENCES users(id)
    )");
    
    // Visitor Management
    $pdo->exec("CREATE TABLE IF NOT EXISTS visitors (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        visitor_name VARCHAR(100) NOT NULL,
        phone VARCHAR(15) NOT NULL,
        purpose VARCHAR(200) NOT NULL,
        visiting_person VARCHAR(100),
        visiting_department VARCHAR(50),
        photo_path VARCHAR(255),
        entry_time DATETIME DEFAULT CURRENT_TIMESTAMP,
        exit_time DATETIME,
        gate_pass_code VARCHAR(20) UNIQUE,
        status ENUM('in', 'out') DEFAULT 'in',
        created_by INTEGER,
        FOREIGN KEY (created_by) REFERENCES users(id)
    )");
    
    // Gate Pass System
    $pdo->exec("CREATE TABLE IF NOT EXISTS gate_passes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        pass_number VARCHAR(20) UNIQUE NOT NULL,
        student_id INTEGER,
        visitor_id INTEGER,
        type ENUM('student_early_exit', 'visitor_entry', 'visitor_exit') NOT NULL,
        qr_code VARCHAR(255) NOT NULL,
        valid_until DATETIME NOT NULL,
        used_at DATETIME,
        issued_by INTEGER NOT NULL,
        status ENUM('active', 'used', 'expired') DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id),
        FOREIGN KEY (visitor_id) REFERENCES visitors(id),
        FOREIGN KEY (issued_by) REFERENCES users(id)
    )");
    
    // Postal Dispatch
    $pdo->exec("CREATE TABLE IF NOT EXISTS postal_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tracking_number VARCHAR(50) UNIQUE NOT NULL,
        sender_name VARCHAR(100) NOT NULL,
        recipient_department VARCHAR(50) NOT NULL,
        recipient_person VARCHAR(100),
        item_type ENUM('letter', 'package', 'courier', 'official') NOT NULL,
        photo_path VARCHAR(255),
        received_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        delivered_at DATETIME,
        delivered_to INTEGER,
        status ENUM('received', 'notified', 'delivered') DEFAULT 'received',
        notes TEXT,
        FOREIGN KEY (delivered_to) REFERENCES users(id)
    )");
    
    // 2. STUDENT SECTION MODULE
    echo "2️⃣ Student Section (Lifeblood) Module...\n";
    
    // Enhanced Student Enrollment
    $pdo->exec("CREATE TABLE IF NOT EXISTS student_enrollments_new (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        student_id INTEGER NOT NULL,
        prn_number VARCHAR(20) UNIQUE NOT NULL,
        division VARCHAR(10),
        batch VARCHAR(10),
        enrollment_status ENUM('pending', 'verified', 'approved', 'rejected') DEFAULT 'pending',
        document_verification_status ENUM('pending', 'partial', 'complete') DEFAULT 'pending',
        verified_by INTEGER,
        verified_at DATETIME,
        academic_year VARCHAR(10) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id),
        FOREIGN KEY (verified_by) REFERENCES users(id)
    )");
    
    // Digital Document Locker
    $pdo->exec("CREATE TABLE IF NOT EXISTS student_documents_new (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        student_id INTEGER NOT NULL,
        document_type ENUM('marksheet_10', 'marksheet_12', 'caste_certificate', 'leaving_certificate', 'photo', 'signature', 'other') NOT NULL,
        document_name VARCHAR(100) NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        file_hash VARCHAR(64) NOT NULL,
        verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
        verified_by INTEGER,
        verified_at DATETIME,
        expiry_date DATE,
        is_original BOOLEAN DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id),
        FOREIGN KEY (verified_by) REFERENCES users(id)
    )");
    
    // Bulk Document Templates
    $pdo->exec("CREATE TABLE IF NOT EXISTS document_templates (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        template_name VARCHAR(100) NOT NULL,
        template_type ENUM('bonafide', 'id_card', 'hall_ticket', 'certificate') NOT NULL,
        template_content TEXT NOT NULL,
        variables JSON,
        is_active BOOLEAN DEFAULT 1,
        created_by INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id)
    )");
    
    // 3. ACCOUNTS SECTION MODULE
    echo "3️⃣ Accounts Section (Financial Hub) Module...\n";
    
    // Enhanced Fee Engine
    $pdo->exec("CREATE TABLE IF NOT EXISTS fee_categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_name VARCHAR(50) NOT NULL,
        category_code VARCHAR(10) UNIQUE NOT NULL,
        description TEXT,
        is_mandatory BOOLEAN DEFAULT 1,
        is_active BOOLEAN DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Payment Gateway Integration
    $pdo->exec("CREATE TABLE IF NOT EXISTS payment_transactions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        transaction_id VARCHAR(50) UNIQUE NOT NULL,
        student_fee_id INTEGER NOT NULL,
        gateway_name VARCHAR(20) NOT NULL,
        gateway_transaction_id VARCHAR(100),
        amount DECIMAL(10,2) NOT NULL,
        gateway_fee DECIMAL(10,2) DEFAULT 0,
        net_amount DECIMAL(10,2) NOT NULL,
        payment_method ENUM('upi', 'card', 'netbanking', 'wallet') NOT NULL,
        status ENUM('initiated', 'processing', 'success', 'failed', 'refunded') DEFAULT 'initiated',
        gateway_response JSON,
        initiated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        completed_at DATETIME,
        FOREIGN KEY (student_fee_id) REFERENCES student_fees(id)
    )");
    
    // Expense Management
    $pdo->exec("CREATE TABLE IF NOT EXISTS expenses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        expense_number VARCHAR(20) UNIQUE NOT NULL,
        department_id INTEGER,
        category ENUM('office_supplies', 'maintenance', 'utilities', 'travel', 'equipment', 'other') NOT NULL,
        vendor_name VARCHAR(100),
        invoice_number VARCHAR(50),
        amount DECIMAL(10,2) NOT NULL,
        expense_date DATE NOT NULL,
        approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        approved_by INTEGER,
        approved_at DATETIME,
        payment_status ENUM('pending', 'paid') DEFAULT 'pending',
        notes TEXT,
        created_by INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (department_id) REFERENCES departments(id),
        FOREIGN KEY (approved_by) REFERENCES users(id),
        FOREIGN KEY (created_by) REFERENCES users(id)
    )");
    
    // Payroll System
    $pdo->exec("CREATE TABLE IF NOT EXISTS payroll (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        employee_id INTEGER NOT NULL,
        pay_period VARCHAR(7) NOT NULL,
        basic_salary DECIMAL(10,2) NOT NULL,
        allowances DECIMAL(10,2) DEFAULT 0,
        overtime_amount DECIMAL(10,2) DEFAULT 0,
        gross_salary DECIMAL(10,2) NOT NULL,
        pf_deduction DECIMAL(10,2) DEFAULT 0,
        professional_tax DECIMAL(10,2) DEFAULT 0,
        tds_deduction DECIMAL(10,2) DEFAULT 0,
        other_deductions DECIMAL(10,2) DEFAULT 0,
        net_salary DECIMAL(10,2) NOT NULL,
        payment_date DATE,
        status ENUM('draft', 'approved', 'paid') DEFAULT 'draft',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (employee_id) REFERENCES users(id)
    )");
    
    // 4. LAB & RESOURCE MANAGEMENT MODULE
    echo "4️⃣ Lab & Resource Management Module...\n";
    
    // Inventory Tracking
    $pdo->exec("CREATE TABLE IF NOT EXISTS inventory_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        item_code VARCHAR(20) UNIQUE NOT NULL,
        item_name VARCHAR(100) NOT NULL,
        category ENUM('consumable', 'non_consumable', 'equipment', 'furniture') NOT NULL,
        department_id INTEGER,
        location VARCHAR(100),
        current_stock INTEGER DEFAULT 0,
        minimum_stock INTEGER DEFAULT 0,
        unit_price DECIMAL(10,2),
        supplier_name VARCHAR(100),
        purchase_date DATE,
        warranty_expiry DATE,
        condition_status ENUM('excellent', 'good', 'fair', 'poor', 'damaged') DEFAULT 'excellent',
        is_active BOOLEAN DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (department_id) REFERENCES departments(id)
    )");
    
    // Stock Movements
    $pdo->exec("CREATE TABLE IF NOT EXISTS stock_movements (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        item_id INTEGER NOT NULL,
        movement_type ENUM('in', 'out', 'transfer', 'adjustment') NOT NULL,
        quantity INTEGER NOT NULL,
        reference_number VARCHAR(50),
        from_location VARCHAR(100),
        to_location VARCHAR(100),
        moved_by INTEGER NOT NULL,
        movement_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        notes TEXT,
        FOREIGN KEY (item_id) REFERENCES inventory_items(id),
        FOREIGN KEY (moved_by) REFERENCES users(id)
    )");
    
    // Maintenance Logs
    $pdo->exec("CREATE TABLE IF NOT EXISTS maintenance_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        item_id INTEGER NOT NULL,
        maintenance_type ENUM('routine', 'repair', 'calibration', 'inspection') NOT NULL,
        scheduled_date DATE,
        completed_date DATE,
        technician_name VARCHAR(100),
        cost DECIMAL(10,2),
        status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
        notes TEXT,
        next_maintenance_date DATE,
        created_by INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (item_id) REFERENCES inventory_items(id),
        FOREIGN KEY (created_by) REFERENCES users(id)
    )");
    
    // Lab Scheduling
    $pdo->exec("CREATE TABLE IF NOT EXISTS lab_bookings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        lab_id INTEGER NOT NULL,
        equipment_id INTEGER,
        booked_by INTEGER NOT NULL,
        booking_date DATE NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        purpose VARCHAR(200) NOT NULL,
        status ENUM('booked', 'confirmed', 'cancelled', 'completed') DEFAULT 'booked',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (lab_id) REFERENCES rooms(id),
        FOREIGN KEY (equipment_id) REFERENCES inventory_items(id),
        FOREIGN KEY (booked_by) REFERENCES users(id)
    )");
    
    // 5. TEACHERS & FACULTY MODULE
    echo "5️⃣ Teachers & Faculty Module...\n";
    
    // Daily Logbook
    $pdo->exec("CREATE TABLE IF NOT EXISTS teaching_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        faculty_id INTEGER NOT NULL,
        subject_id INTEGER NOT NULL,
        class_date DATE NOT NULL,
        period_number INTEGER,
        topics_covered TEXT NOT NULL,
        syllabus_percentage DECIMAL(5,2),
        attendance_count INTEGER,
        total_students INTEGER,
        teaching_method ENUM('lecture', 'practical', 'tutorial', 'seminar') NOT NULL,
        resources_used TEXT,
        assignments_given TEXT,
        next_class_plan TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (faculty_id) REFERENCES users(id),
        FOREIGN KEY (subject_id) REFERENCES subjects(id)
    )");
    
    // Assessment Tools
    $pdo->exec("CREATE TABLE IF NOT EXISTS assessments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        assessment_code VARCHAR(20) UNIQUE NOT NULL,
        subject_id INTEGER NOT NULL,
        faculty_id INTEGER NOT NULL,
        title VARCHAR(200) NOT NULL,
        type ENUM('quiz', 'assignment', 'project', 'presentation') NOT NULL,
        max_marks INTEGER NOT NULL,
        due_date DATETIME,
        instructions TEXT,
        is_published BOOLEAN DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (subject_id) REFERENCES subjects(id),
        FOREIGN KEY (faculty_id) REFERENCES users(id)
    )");
    
    // Mentor-Mentee System
    $pdo->exec("CREATE TABLE IF NOT EXISTS mentorship (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        mentor_id INTEGER NOT NULL,
        mentee_id INTEGER NOT NULL,
        assigned_date DATE NOT NULL,
        status ENUM('active', 'inactive', 'completed') DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (mentor_id) REFERENCES users(id),
        FOREIGN KEY (mentee_id) REFERENCES students(id)
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS mentorship_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        mentorship_id INTEGER NOT NULL,
        meeting_date DATE NOT NULL,
        discussion_topics TEXT,
        student_concerns TEXT,
        action_items TEXT,
        next_meeting_date DATE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (mentorship_id) REFERENCES mentorship(id)
    )");
    
    // Leave Management
    $pdo->exec("CREATE TABLE IF NOT EXISTS leave_applications (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        applicant_id INTEGER NOT NULL,
        leave_type ENUM('casual', 'medical', 'earned', 'maternity', 'emergency') NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        total_days INTEGER NOT NULL,
        reason TEXT NOT NULL,
        substitute_arranged INTEGER,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        approved_by INTEGER,
        approved_at DATETIME,
        rejection_reason TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (applicant_id) REFERENCES users(id),
        FOREIGN KEY (substitute_arranged) REFERENCES users(id),
        FOREIGN KEY (approved_by) REFERENCES users(id)
    )");
    
    // 6. SUBJECT & DEPARTMENT MODULE
    echo "6️⃣ Subject & Department Module...\n";
    
    // OBE Mapping
    $pdo->exec("CREATE TABLE IF NOT EXISTS course_outcomes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        subject_id INTEGER NOT NULL,
        outcome_code VARCHAR(10) NOT NULL,
        outcome_description TEXT NOT NULL,
        bloom_level ENUM('remember', 'understand', 'apply', 'analyze', 'evaluate', 'create') NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (subject_id) REFERENCES subjects(id)
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS program_outcomes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        program_id INTEGER NOT NULL,
        outcome_code VARCHAR(10) NOT NULL,
        outcome_description TEXT NOT NULL,
        category ENUM('knowledge', 'skill', 'attitude') NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (program_id) REFERENCES programs(id)
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS co_po_mapping (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        course_outcome_id INTEGER NOT NULL,
        program_outcome_id INTEGER NOT NULL,
        correlation_level ENUM('low', 'medium', 'high') NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_outcome_id) REFERENCES course_outcomes(id),
        FOREIGN KEY (program_outcome_id) REFERENCES program_outcomes(id)
    )");
    
    // Syllabus Versioning
    $pdo->exec("CREATE TABLE IF NOT EXISTS syllabus_versions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        subject_id INTEGER NOT NULL,
        version_number VARCHAR(10) NOT NULL,
        academic_year VARCHAR(10) NOT NULL,
        syllabus_content TEXT NOT NULL,
        effective_from DATE NOT NULL,
        effective_to DATE,
        is_current BOOLEAN DEFAULT 0,
        approved_by INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (subject_id) REFERENCES subjects(id),
        FOREIGN KEY (approved_by) REFERENCES users(id)
    )");
    
    // 7. LIBRARY MANAGEMENT MODULE
    echo "7️⃣ Library Management Module...\n";
    
    // Enhanced Book Management
    $pdo->exec("CREATE TABLE IF NOT EXISTS library_books (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        isbn VARCHAR(20),
        title VARCHAR(200) NOT NULL,
        author VARCHAR(200) NOT NULL,
        publisher VARCHAR(100),
        publication_year INTEGER,
        category VARCHAR(50),
        location_code VARCHAR(20),
        total_copies INTEGER DEFAULT 1,
        available_copies INTEGER DEFAULT 1,
        price DECIMAL(10,2),
        acquisition_date DATE,
        condition_status ENUM('excellent', 'good', 'fair', 'poor') DEFAULT 'excellent',
        is_active BOOLEAN DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Circulation System
    $pdo->exec("CREATE TABLE IF NOT EXISTS book_transactions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        book_id INTEGER NOT NULL,
        student_id INTEGER NOT NULL,
        transaction_type ENUM('issue', 'return', 'renew') NOT NULL,
        issue_date DATE NOT NULL,
        due_date DATE NOT NULL,
        return_date DATE,
        fine_amount DECIMAL(10,2) DEFAULT 0,
        status ENUM('issued', 'returned', 'overdue', 'lost') DEFAULT 'issued',
        issued_by INTEGER NOT NULL,
        returned_to INTEGER,
        notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (book_id) REFERENCES library_books(id),
        FOREIGN KEY (student_id) REFERENCES students(id),
        FOREIGN KEY (issued_by) REFERENCES users(id),
        FOREIGN KEY (returned_to) REFERENCES users(id)
    )");
    
    // E-Resource Portal
    $pdo->exec("CREATE TABLE IF NOT EXISTS digital_resources (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        resource_name VARCHAR(100) NOT NULL,
        resource_type ENUM('journal', 'database', 'ebook', 'video', 'software') NOT NULL,
        provider VARCHAR(100),
        access_url VARCHAR(500),
        username VARCHAR(100),
        password_encrypted TEXT,
        subscription_start DATE,
        subscription_end DATE,
        concurrent_users INTEGER DEFAULT 1,
        is_active BOOLEAN DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 8. SPORTS & ATHLETICS MODULE
    echo "8️⃣ Sports & Athletics Module...\n";
    
    // Sports Kit Management
    $pdo->exec("CREATE TABLE IF NOT EXISTS sports_equipment (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        equipment_code VARCHAR(20) UNIQUE NOT NULL,
        equipment_name VARCHAR(100) NOT NULL,
        sport_category VARCHAR(50) NOT NULL,
        size VARCHAR(20),
        condition_status ENUM('excellent', 'good', 'fair', 'poor', 'damaged') DEFAULT 'excellent',
        purchase_date DATE,
        cost DECIMAL(10,2),
        current_location VARCHAR(100),
        is_available BOOLEAN DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS equipment_issues (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        equipment_id INTEGER NOT NULL,
        student_id INTEGER NOT NULL,
        issue_date DATE NOT NULL,
        expected_return_date DATE NOT NULL,
        actual_return_date DATE,
        condition_at_issue ENUM('excellent', 'good', 'fair', 'poor') NOT NULL,
        condition_at_return ENUM('excellent', 'good', 'fair', 'poor'),
        issued_by INTEGER NOT NULL,
        returned_to INTEGER,
        status ENUM('issued', 'returned', 'overdue', 'lost', 'damaged') DEFAULT 'issued',
        notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (equipment_id) REFERENCES sports_equipment(id),
        FOREIGN KEY (student_id) REFERENCES students(id),
        FOREIGN KEY (issued_by) REFERENCES users(id),
        FOREIGN KEY (returned_to) REFERENCES users(id)
    )");
    
    // Event Management
    $pdo->exec("CREATE TABLE IF NOT EXISTS sports_events (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        event_name VARCHAR(100) NOT NULL,
        event_type ENUM('internal', 'inter_college', 'tournament') NOT NULL,
        sport_category VARCHAR(50) NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        venue VARCHAR(100),
        max_participants INTEGER,
        registration_deadline DATE,
        entry_fee DECIMAL(10,2) DEFAULT 0,
        status ENUM('planned', 'registration_open', 'ongoing', 'completed', 'cancelled') DEFAULT 'planned',
        organized_by INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (organized_by) REFERENCES users(id)
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS event_participants (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        event_id INTEGER NOT NULL,
        student_id INTEGER NOT NULL,
        registration_date DATE NOT NULL,
        medical_clearance BOOLEAN DEFAULT 0,
        fitness_test_score DECIMAL(5,2),
        participation_status ENUM('registered', 'confirmed', 'participated', 'withdrawn') DEFAULT 'registered',
        performance_notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES sports_events(id),
        FOREIGN KEY (student_id) REFERENCES students(id)
    )");
    
    // Fitness Tracking
    $pdo->exec("CREATE TABLE IF NOT EXISTS fitness_records (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        student_id INTEGER NOT NULL,
        test_date DATE NOT NULL,
        height DECIMAL(5,2),
        weight DECIMAL(5,2),
        bmi DECIMAL(5,2),
        blood_pressure VARCHAR(10),
        fitness_score DECIMAL(5,2),
        medical_clearance BOOLEAN DEFAULT 0,
        clearance_valid_until DATE,
        tested_by INTEGER NOT NULL,
        notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id),
        FOREIGN KEY (tested_by) REFERENCES users(id)
    )");
    
    echo "\n✅ All 8 Core Module Tables Created Successfully!\n";
    echo "📊 Total Tables: " . count($pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll()) . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}