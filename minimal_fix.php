<?php
// Minimal System Fix - Just Get It Working
echo "🔧 PVGS ERP Minimal Fix\n";
echo "=======================\n\n";

$dbPath = __DIR__ . '/database/database.sqlite';

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Database connected\n";
    
    // 1. First, let's just make sure we have student records in the students table
    echo "\n📚 Creating basic student records...\n";
    
    // Get student users
    $studentUsers = $pdo->query("SELECT id, name, email FROM users WHERE user_type = 'student'")->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($studentUsers)) {
        $studentStmt = $pdo->prepare("INSERT OR IGNORE INTO students (user_id, program_id, admission_number, status, admission_date, name, created_at, updated_at) VALUES (?, 1, ?, 'active', date('now'), ?, datetime('now'), datetime('now'))");
        
        foreach ($studentUsers as $index => $user) {
            $admissionNumber = 'ADM2024' . str_pad($user['id'], 3, '0', STR_PAD_LEFT);
            $studentStmt->execute([$user['id'], $admissionNumber, $user['name']]);
            echo "  + Created student record for: {$user['name']}\n";
        }
    }
    
    // 2. Add principal if missing
    echo "\n👑 Ensuring principal account exists...\n";
    
    $principalCheck = $pdo->query("SELECT COUNT(*) FROM users WHERE email = 'principal@pvgs.edu'")->fetchColumn();
    
    if ($principalCheck == 0) {
        $sql = "INSERT INTO users (name, email, password, user_type, is_active, created_at, updated_at) VALUES (?, ?, ?, 'principal', 1, datetime('now'), datetime('now'))";
        $principalStmt = $pdo->prepare($sql);
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        $principalStmt->execute(['Dr. Priya Mehta', 'principal@pvgs.edu', $hashedPassword]);
        echo "  + Added principal account\n";
    } else {
        echo "  ✓ Principal account exists\n";
    }
    
    // 3. Ensure all users have passwords
    echo "\n🔐 Fixing user passwords...\n";
    
    $usersWithoutPassword = $pdo->query("SELECT id, email FROM users WHERE password IS NULL OR password = ''")->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($usersWithoutPassword)) {
        $passwordStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $defaultPassword = password_hash('password123', PASSWORD_DEFAULT);
        
        foreach ($usersWithoutPassword as $user) {
            $passwordStmt->execute([$defaultPassword, $user['id']]);
            echo "  + Fixed password for: {$user['email']}\n";
        }
    } else {
        echo "  ✓ All users have passwords\n";
    }
    
    // 4. Verification
    echo "\n✅ System Status:\n";
    
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $studentCount = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    
    echo "  - Users: $userCount\n";
    echo "  - Students: $studentCount\n";
    
    // Show user types
    echo "\n👥 User Types:\n";
    $userTypes = $pdo->query("SELECT user_type, COUNT(*) as count FROM users GROUP BY user_type")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($userTypes as $type) {
        echo "  - {$type['user_type']}: {$type['count']}\n";
    }
    
    echo "\n🎉 Basic system fix completed!\n";
    echo "\n📋 Test Credentials (password: password123):\n";
    echo "- Principal: principal@pvgs.edu\n";
    echo "- Admin: admin@pvgs.edu\n";
    echo "- Faculty: rajesh.kumar@pvgs.edu\n";
    echo "- Student: aarav.sharma@pvgs.edu\n";
    
    echo "\n🚀 Start the server with: php -S localhost:8000 -t public\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}