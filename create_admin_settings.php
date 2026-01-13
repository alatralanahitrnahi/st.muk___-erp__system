<?php
// Database setup for Admin Settings System
$dbPath = '/workspaces/st.muk___-erp__system/database/database.sqlite';

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // System Settings Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS system_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category VARCHAR(50) NOT NULL,
        setting_key VARCHAR(100) NOT NULL,
        setting_value TEXT,
        data_type TEXT DEFAULT 'string' CHECK(data_type IN ('string', 'number', 'boolean', 'json', 'file')),
        is_encrypted BOOLEAN DEFAULT 0,
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(category, setting_key)
    )");

    // User Roles and Permissions
    $pdo->exec("CREATE TABLE IF NOT EXISTS roles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(50) UNIQUE NOT NULL,
        display_name VARCHAR(100) NOT NULL,
        description TEXT,
        is_system_role BOOLEAN DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS permissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(100) UNIQUE NOT NULL,
        display_name VARCHAR(150) NOT NULL,
        module VARCHAR(50) NOT NULL,
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS role_permissions (
        role_id INTEGER,
        permission_id INTEGER,
        PRIMARY KEY (role_id, permission_id),
        FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
        FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
    )");

    // Custom Fields
    $pdo->exec("CREATE TABLE IF NOT EXISTS custom_fields (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        module VARCHAR(50) NOT NULL,
        field_name VARCHAR(100) NOT NULL,
        field_label VARCHAR(150) NOT NULL,
        field_type TEXT CHECK(field_type IN ('text', 'number', 'email', 'phone', 'date', 'select', 'checkbox', 'textarea', 'file')) NOT NULL,
        field_options TEXT,
        is_required BOOLEAN DEFAULT 0,
        is_active BOOLEAN DEFAULT 1,
        sort_order INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // System Backup Logs
    $pdo->exec("CREATE TABLE IF NOT EXISTS backup_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        backup_type TEXT CHECK(backup_type IN ('manual', 'scheduled')) NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        file_size INTEGER,
        status TEXT DEFAULT 'pending' CHECK(status IN ('pending', 'completed', 'failed')),
        created_by INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        completed_at DATETIME,
        FOREIGN KEY (created_by) REFERENCES users(id)
    )");

    // Insert Default System Settings
    $defaultSettings = [
        // General Settings
        ['general', 'institution_name', 'PVG\'s College of Science & Commerce', 'string', 'Institution Name'],
        ['general', 'institution_code', 'PVGS001', 'string', 'Institution Code'],
        ['general', 'academic_year', '2024-25', 'string', 'Current Academic Year'],
        ['general', 'timezone', 'Asia/Kolkata', 'string', 'System Timezone'],
        ['general', 'date_format', 'd-m-Y', 'string', 'Date Display Format'],
        ['general', 'currency', 'INR', 'string', 'Default Currency'],
        
        // Session Settings
        ['session', 'session_timeout', '3600', 'number', 'Session timeout in seconds'],
        ['session', 'max_login_attempts', '5', 'number', 'Maximum login attempts'],
        ['session', 'password_expiry_days', '90', 'number', 'Password expiry in days'],
        
        // Notification Settings
        ['notification', 'email_notifications', 'true', 'boolean', 'Enable email notifications'],
        ['notification', 'sms_notifications', 'true', 'boolean', 'Enable SMS notifications'],
        ['notification', 'push_notifications', 'false', 'boolean', 'Enable push notifications'],
        
        // Fee Settings
        ['fees', 'late_fee_percentage', '2', 'number', 'Late fee percentage per month'],
        ['fees', 'grace_period_days', '7', 'number', 'Grace period for fee payment'],
        ['fees', 'installment_mode', 'true', 'boolean', 'Enable installment payments'],
        
        // Attendance Settings
        ['attendance', 'minimum_percentage', '75', 'number', 'Minimum attendance percentage'],
        ['attendance', 'grace_minutes', '15', 'number', 'Grace period for late arrival'],
        
        // Examination Settings
        ['examination', 'passing_marks', '40', 'number', 'Minimum passing marks percentage'],
        ['examination', 'grace_marks', '5', 'number', 'Maximum grace marks allowed']
    ];

    $stmt = $pdo->prepare("INSERT OR IGNORE INTO system_settings (category, setting_key, setting_value, data_type, description) VALUES (?, ?, ?, ?, ?)");
    foreach ($defaultSettings as $setting) {
        $stmt->execute($setting);
    }

    // Insert Default Roles
    $defaultRoles = [
        ['principal', 'Principal', 'Institution Principal with full system access', 1],
        ['hod', 'Head of Department', 'Department head with departmental access', 1],
        ['faculty', 'Faculty', 'Teaching faculty with class management access', 1],
        ['accounts', 'Accounts Officer', 'Financial management and fee collection', 1],
        ['front_office', 'Front Office', 'Student admission and general queries', 1],
        ['librarian', 'Librarian', 'Library management access', 1],
        ['exam_controller', 'Exam Controller', 'Examination and result management', 1],
        ['it_admin', 'IT Administrator', 'System administration and technical support', 1]
    ];

    $stmt = $pdo->prepare("INSERT OR IGNORE INTO roles (name, display_name, description, is_system_role) VALUES (?, ?, ?, ?)");
    foreach ($defaultRoles as $role) {
        $stmt->execute($role);
    }

    // Insert Default Permissions
    $defaultPermissions = [
        // System Management
        ['system.settings.view', 'View System Settings', 'system'],
        ['system.settings.edit', 'Edit System Settings', 'system'],
        ['system.backup.create', 'Create System Backup', 'system'],
        ['system.backup.restore', 'Restore System Backup', 'system'],
        ['system.users.manage', 'Manage System Users', 'system'],
        
        // Academic Management
        ['academic.students.view', 'View Students', 'academic'],
        ['academic.students.edit', 'Edit Students', 'academic'],
        ['academic.faculty.view', 'View Faculty', 'academic'],
        ['academic.faculty.edit', 'Edit Faculty', 'academic'],
        ['academic.timetable.view', 'View Timetable', 'academic'],
        ['academic.timetable.edit', 'Edit Timetable', 'academic'],
        
        // Financial Management
        ['finance.fees.view', 'View Fee Records', 'finance'],
        ['finance.fees.collect', 'Collect Fees', 'finance'],
        ['finance.reports.view', 'View Financial Reports', 'finance'],
        
        // Examination
        ['exam.results.view', 'View Exam Results', 'examination'],
        ['exam.results.edit', 'Edit Exam Results', 'examination'],
        ['exam.schedule.manage', 'Manage Exam Schedule', 'examination']
    ];

    $stmt = $pdo->prepare("INSERT OR IGNORE INTO permissions (name, display_name, module) VALUES (?, ?, ?)");
    foreach ($defaultPermissions as $permission) {
        $stmt->execute($permission);
    }

    echo "✅ Admin Settings System created successfully!\n";
    echo "📊 System Settings: " . count($defaultSettings) . " default settings added\n";
    echo "👥 Roles: " . count($defaultRoles) . " default roles created\n";
    echo "🔐 Permissions: " . count($defaultPermissions) . " default permissions added\n";

} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>