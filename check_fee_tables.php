<?php
// Check Fee Tables Structure
echo "🔍 Checking Fee Tables Structure\n";
echo "================================\n\n";

$dbPath = __DIR__ . '/database/database.sqlite';

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check all fee-related tables
    $feeTables = ['student_fees', 'fee_structures', 'fee_payments', 'fee_installments'];
    
    foreach ($feeTables as $table) {
        echo "📋 $table structure:\n";
        try {
            $stmt = $pdo->query("PRAGMA table_info($table)");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($columns)) {
                echo "  ❌ Table does not exist\n\n";
                continue;
            }
            
            foreach ($columns as $column) {
                echo "  - {$column['name']} ({$column['type']})\n";
            }
            echo "\n";
        } catch (Exception $e) {
            echo "  ❌ Error: {$e->getMessage()}\n\n";
        }
    }
    
    // Check attendance_records structure
    echo "📋 attendance_records structure:\n";
    $stmt = $pdo->query("PRAGMA table_info(attendance_records)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "  - {$column['name']} ({$column['type']})\n";
    }
    echo "\n";
    
    // Check exam_results structure
    echo "📋 exam_results structure:\n";
    $stmt = $pdo->query("PRAGMA table_info(exam_results)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "  - {$column['name']} ({$column['type']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}