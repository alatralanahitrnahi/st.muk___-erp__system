<?php
// Database Diagnostic Script
echo "🔍 PVGS ERP Database Diagnostic\n";
echo "===============================\n\n";

$dbPath = __DIR__ . '/database/database.sqlite';

if (!file_exists($dbPath)) {
    echo "❌ Database file not found!\n";
    exit(1);
}

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Database connection successful\n";
    echo "📊 Database size: " . round(filesize($dbPath)/1024, 2) . " KB\n\n";
    
    // Get all tables
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📋 Tables found (" . count($tables) . "):\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    echo "\n";
    
    // Check key tables and their data
    $keyTables = ['users', 'students', 'faculty', 'subjects', 'timetable_entries'];
    
    foreach ($keyTables as $table) {
        if (in_array($table, $tables)) {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            echo "✓ $table: $count records\n";
            
            // Show sample data for small tables
            if ($count > 0 && $count <= 10) {
                $stmt = $pdo->query("SELECT * FROM $table LIMIT 3");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($rows) {
                    echo "  Sample data:\n";
                    foreach ($rows as $row) {
                        $preview = array_slice($row, 0, 3, true);
                        $previewStr = implode(', ', array_map(function($k, $v) {
                            return "$k: " . (strlen($v) > 20 ? substr($v, 0, 20) . '...' : $v);
                        }, array_keys($preview), $preview));
                        echo "    - $previewStr\n";
                    }
                }
            }
        } else {
            echo "❌ $table: Table missing\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}