<?php
// Quick Test Script for Secure PVGS ERP
echo "🧪 Testing Secure PVGS ERP System\n";
echo "=================================\n\n";

$dbPath = __DIR__ . '/database/database.sqlite';

try {
    // 1. Database Test
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database: Connected\n";
    
    // 2. Core Tables Test
    $coreTables = ['inquiries', 'visitors', 'gate_passes', 'student_enrollments_secure', 'payment_transactions'];
    $existing = 0;
    foreach ($coreTables as $table) {
        $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'")->fetch();
        if ($result) $existing++;
    }
    echo "✅ Core Tables: $existing/" . count($coreTables) . " created\n";
    
    // 3. Security Files Test
    $secureFiles = [
        'public/secure_login.html',
        'public/secure_principal.html'
    ];
    
    $fileCount = 0;
    foreach ($secureFiles as $file) {
        if (file_exists($file)) {
            $fileCount++;
            // Check for CSP header
            $content = file_get_contents($file);
            if (strpos($content, 'Content-Security-Policy') !== false) {
                echo "✅ Security: $file (CSP enabled)\n";
            } else {
                echo "⚠️  Security: $file (CSP missing)\n";
            }
        }
    }
    
    echo "\n🚀 Test Results:\n";
    echo "- Database: Ready\n";
    echo "- Core Tables: $existing tables\n";
    echo "- Secure Files: $fileCount files\n";
    
    echo "\n📋 Test Instructions:\n";
    echo "1. Start server: php -S localhost:8000 -t public\n";
    echo "2. Open: http://localhost:8000/secure_login.html\n";
    echo "3. Use quick login buttons to test each role\n";
    echo "4. Verify CSP in browser dev tools (no external resources)\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}