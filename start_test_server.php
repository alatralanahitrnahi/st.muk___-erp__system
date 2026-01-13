<?php
// Simple Test Server for PVGS ERP
echo "🚀 Starting PVGS ERP Test Server\n";
echo "================================\n\n";

// Check if database exists and has data
$dbPath = __DIR__ . '/database/database.sqlite';

if (file_exists($dbPath)) {
    try {
        $pdo = new PDO('sqlite:' . $dbPath);
        $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        echo "✓ Database ready with $userCount users\n";
    } catch (Exception $e) {
        echo "⚠️ Database issue: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Database not found!\n";
    exit(1);
}

echo "\n📋 Access URLs:\n";
echo "- Login Page: http://localhost:8000/login.html\n";
echo "- System Status: http://localhost:8000/index.html\n";
echo "- Principal Dashboard: http://localhost:8000/principal.html\n";
echo "- Faculty Portal: http://localhost:8000/faculty.html\n";
echo "- Admin Portal: http://localhost:8000/admin.html\n";
echo "- Student Portal: http://localhost:8000/student.html\n";

echo "\n🔑 Quick Login Credentials:\n";
echo "- Principal: principal@pvgs.edu / password123\n";
echo "- Admin: admin@pvgs.edu / password123\n";
echo "- Faculty: rajesh.kumar@pvgs.edu / password123\n";
echo "- Student: aarav.sharma@pvgs.edu / password123\n";

echo "\n🌐 Starting server on http://localhost:8000\n";
echo "Press Ctrl+C to stop\n\n";

// Change to public directory and start server
chdir(__DIR__ . '/public');
passthru('php -S localhost:8000');