<?php
// Check Table Structures
echo "🔍 Checking Table Structures\n";
echo "============================\n\n";

$dbPath = __DIR__ . '/database/database.sqlite';

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check students table structure
    echo "📋 Students table structure:\n";
    $stmt = $pdo->query("PRAGMA table_info(students)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "  - {$column['name']} ({$column['type']})\n";
    }
    
    echo "\n📋 Users table structure:\n";
    $stmt = $pdo->query("PRAGMA table_info(users)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "  - {$column['name']} ({$column['type']})\n";
    }
    
    // Check if we need to add students to users table instead
    echo "\n📊 Current data:\n";
    echo "Users: " . $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() . "\n";
    echo "Students: " . $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn() . "\n";
    
    // Show sample users
    echo "\nSample users:\n";
    $stmt = $pdo->query("SELECT id, name, email, user_type FROM users LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['name']} ({$row['email']}) - {$row['user_type']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}