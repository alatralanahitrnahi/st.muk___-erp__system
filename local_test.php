<?php
// Local Testing Script for PVGS ERP System
echo "🎓 PVGS ERP System - Local Testing\n";
echo "================================\n\n";

// Check PHP version
echo "✓ PHP Version: " . PHP_VERSION . "\n";

// Check if database exists
$dbPath = __DIR__ . '/database/database.sqlite';
if (file_exists($dbPath)) {
    echo "✓ Database found: " . round(filesize($dbPath)/1024, 2) . " KB\n";
} else {
    echo "❌ Database not found\n";
    exit(1);
}

// Test database connection
try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Database connection successful\n";
    
    // Count tables
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM sqlite_master WHERE type='table'");
    $tableCount = $stmt->fetch()['count'];
    echo "✓ Tables found: $tableCount\n";
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

// Check key files
$files = [
    'public/index.html' => 'System Status Page',
    'public/login.html' => 'Login Page',
    'public/principal.html' => 'Principal Dashboard',
    'public/faculty.html' => 'Faculty Portal',
    'public/admin.html' => 'Admin Portal',
    'public/student.html' => 'Student Portal'
];

echo "\n📁 File Check:\n";
foreach ($files as $file => $desc) {
    if (file_exists($file)) {
        echo "✓ $desc\n";
    } else {
        echo "❌ $desc (missing)\n";
    }
}

echo "\n🚀 Starting local server...\n";
echo "Access URLs:\n";
echo "- System Status: http://localhost:8000\n";
echo "- Login: http://localhost:8000/login.html\n";
echo "- Principal: http://localhost:8000/principal.html\n";
echo "- Faculty: http://localhost:8000/faculty.html\n";
echo "- Admin: http://localhost:8000/admin.html\n";
echo "- Student: http://localhost:8000/student.html\n\n";

// Start PHP built-in server
$command = "php -S localhost:8000 -t public";
echo "Running: $command\n";
echo "Press Ctrl+C to stop\n\n";

passthru($command);