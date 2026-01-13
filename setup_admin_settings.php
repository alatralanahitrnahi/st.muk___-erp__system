<?php
// Simplified Admin Settings System
$dbPath = '/workspaces/st.muk___-erp__system/database/database.sqlite';

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // System Settings Table (main settings storage)
    $pdo->exec("CREATE TABLE IF NOT EXISTS system_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category VARCHAR(50) NOT NULL,
        setting_key VARCHAR(100) NOT NULL,
        setting_value TEXT,
        data_type TEXT DEFAULT 'string',
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(category, setting_key)
    )");

    // Custom Fields for dynamic form fields
    $pdo->exec("CREATE TABLE IF NOT EXISTS custom_fields (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        module VARCHAR(50) NOT NULL,
        field_name VARCHAR(100) NOT NULL,
        field_label VARCHAR(150) NOT NULL,
        field_type TEXT NOT NULL,
        field_options TEXT,
        is_required BOOLEAN DEFAULT 0,
        is_active BOOLEAN DEFAULT 1,
        sort_order INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // System Backup Logs
    $pdo->exec("CREATE TABLE IF NOT EXISTS backup_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        backup_type TEXT NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        file_size INTEGER,
        status TEXT DEFAULT 'pending',
        created_by INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        completed_at DATETIME
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
        ['general', 'language', 'en', 'string', 'Default Language'],
        
        // Session Settings
        ['session', 'session_timeout', '3600', 'number', 'Session timeout in seconds'],
        ['session', 'max_login_attempts', '5', 'number', 'Maximum login attempts'],
        ['session', 'password_expiry_days', '90', 'number', 'Password expiry in days'],
        ['session', 'auto_logout', '1', 'boolean', 'Enable auto logout'],
        
        // Notification Settings
        ['notification', 'email_notifications', '1', 'boolean', 'Enable email notifications'],
        ['notification', 'sms_notifications', '1', 'boolean', 'Enable SMS notifications'],
        ['notification', 'push_notifications', '0', 'boolean', 'Enable push notifications'],
        ['notification', 'notification_sound', '1', 'boolean', 'Enable notification sounds'],
        
        // SMS Settings
        ['sms', 'sms_provider', 'textlocal', 'string', 'SMS Service Provider'],
        ['sms', 'sms_api_key', '', 'string', 'SMS API Key'],
        ['sms', 'sms_sender_id', 'PVGS', 'string', 'SMS Sender ID'],
        ['sms', 'sms_template_admission', 'Welcome to PVGS! Your admission is confirmed.', 'string', 'Admission SMS Template'],
        
        // Email Settings
        ['email', 'smtp_host', 'smtp.gmail.com', 'string', 'SMTP Host'],
        ['email', 'smtp_port', '587', 'number', 'SMTP Port'],
        ['email', 'smtp_username', '', 'string', 'SMTP Username'],
        ['email', 'smtp_password', '', 'string', 'SMTP Password'],
        ['email', 'from_email', 'noreply@pvgs.edu.in', 'string', 'From Email Address'],
        ['email', 'from_name', 'PVGS College', 'string', 'From Name'],
        
        // Payment Settings
        ['payment', 'razorpay_enabled', '1', 'boolean', 'Enable Razorpay'],
        ['payment', 'razorpay_key_id', 'rzp_test_1234567890', 'string', 'Razorpay Key ID'],
        ['payment', 'razorpay_key_secret', '', 'string', 'Razorpay Secret Key'],
        ['payment', 'offline_payment', '1', 'boolean', 'Enable Offline Payment'],
        ['payment', 'payment_gateway_fee', '2.5', 'number', 'Payment Gateway Fee %'],
        
        // Fee Settings
        ['fees', 'late_fee_percentage', '2', 'number', 'Late fee percentage per month'],
        ['fees', 'grace_period_days', '7', 'number', 'Grace period for fee payment'],
        ['fees', 'installment_mode', '1', 'boolean', 'Enable installment payments'],
        ['fees', 'fee_reminder_days', '5', 'number', 'Fee reminder before due date'],
        
        // Attendance Settings
        ['attendance', 'minimum_percentage', '75', 'number', 'Minimum attendance percentage'],
        ['attendance', 'grace_minutes', '15', 'number', 'Grace period for late arrival'],
        ['attendance', 'auto_absent_hours', '2', 'number', 'Auto mark absent after hours'],
        
        // Examination Settings
        ['examination', 'passing_marks', '40', 'number', 'Minimum passing marks percentage'],
        ['examination', 'grace_marks', '5', 'number', 'Maximum grace marks allowed'],
        ['examination', 'result_publish_auto', '0', 'boolean', 'Auto publish results'],
        
        // Print Settings
        ['print', 'thermal_printer', '0', 'boolean', 'Enable thermal printer'],
        ['print', 'print_logo', '1', 'boolean', 'Include logo in prints'],
        ['print', 'header_text', 'PVG\'s College of Science & Commerce', 'string', 'Print header text'],
        ['print', 'footer_text', 'Thank you for choosing PVGS', 'string', 'Print footer text'],
        
        // Security Settings
        ['security', 'captcha_enabled', '1', 'boolean', 'Enable CAPTCHA'],
        ['security', 'two_factor_auth', '0', 'boolean', 'Enable 2FA'],
        ['security', 'password_complexity', '1', 'boolean', 'Enforce password complexity'],
        ['security', 'login_ip_restriction', '0', 'boolean', 'Restrict login by IP'],
        
        // Backup Settings
        ['backup', 'auto_backup', '1', 'boolean', 'Enable automatic backup'],
        ['backup', 'backup_frequency', 'daily', 'string', 'Backup frequency'],
        ['backup', 'backup_retention_days', '30', 'number', 'Backup retention period'],
        
        // Front CMS Settings
        ['cms', 'website_title', 'PVGS College', 'string', 'Website Title'],
        ['cms', 'website_description', 'Leading institution for Science & Commerce', 'string', 'Website Description'],
        ['cms', 'contact_email', 'info@pvgs.edu.in', 'string', 'Contact Email'],
        ['cms', 'contact_phone', '+91-9876543210', 'string', 'Contact Phone'],
        ['cms', 'address', 'Mumbai, Maharashtra, India', 'string', 'Institution Address']
    ];

    $stmt = $pdo->prepare("INSERT OR IGNORE INTO system_settings (category, setting_key, setting_value, data_type, description) VALUES (?, ?, ?, ?, ?)");
    foreach ($defaultSettings as $setting) {
        $stmt->execute($setting);
    }

    echo "✅ Admin Settings System created successfully!\n";
    echo "📊 System Settings: " . count($defaultSettings) . " default settings added\n";
    echo "🔧 Categories: General, Session, Notification, SMS, Email, Payment, Fees, Attendance, Examination, Print, Security, Backup, CMS\n";

} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>