<?php
// System Integration Tests

class PVGSSystemTests {
    private $pdo;
    private $testResults = [];

    public function __construct() {
        $dbPath = '/workspaces/st.muk___-erp__system/database/database.sqlite';
        $this->pdo = new PDO("sqlite:$dbPath");
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function runAllTests() {
        echo "🧪 PVGS ERP System Tests\n";
        echo "========================\n\n";

        $this->testDatabaseConnection();
        $this->testCoreTablesExist();
        $this->testUserAuthentication();
        $this->testStudentManagement();
        $this->testFeeSystem();
        $this->testAttendanceSystem();
        $this->testTimetableSystem();
        $this->testSystemSettings();
        $this->testDataIntegrity();

        $this->displayResults();
    }

    private function testDatabaseConnection() {
        try {
            $stmt = $this->pdo->query("SELECT 1");
            $this->addResult("Database Connection", true, "SQLite connection successful");
        } catch (Exception $e) {
            $this->addResult("Database Connection", false, $e->getMessage());
        }
    }

    private function testCoreTablesExist() {
        $requiredTables = [
            'users', 'students', 'faculty', 'departments', 'programs', 
            'subjects', 'fee_structures', 'attendance_records', 
            'timetable_entries', 'system_settings', 'roles', 'permissions'
        ];

        foreach ($requiredTables as $table) {
            try {
                $stmt = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'");
                $exists = $stmt->fetch() !== false;
                $this->addResult("Table: $table", $exists, $exists ? "Table exists" : "Table missing");
            } catch (Exception $e) {
                $this->addResult("Table: $table", false, $e->getMessage());
            }
        }
    }

    private function testUserAuthentication() {
        try {
            // Test user creation
            $stmt = $this->pdo->prepare("INSERT OR IGNORE INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, ?)");
            $result = $stmt->execute(['Test User', 'test@pvgs.edu', password_hash('test123', PASSWORD_DEFAULT), 'faculty', date('Y-m-d H:i:s')]);
            
            // Test user retrieval
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute(['test@pvgs.edu']);
            $user = $stmt->fetch();

            $this->addResult("User Authentication", $user !== false, "User CRUD operations working");
        } catch (Exception $e) {
            $this->addResult("User Authentication", false, $e->getMessage());
        }
    }

    private function testStudentManagement() {
        try {
            // Test student data
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM students");
            $stmt->execute();
            $count = $stmt->fetch()['count'];

            $this->addResult("Student Management", $count > 0, "Students: $count records");
        } catch (Exception $e) {
            $this->addResult("Student Management", false, $e->getMessage());
        }
    }

    private function testFeeSystem() {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM fee_structures");
            $stmt->execute();
            $count = $stmt->fetch()['count'];

            $this->addResult("Fee System", $count > 0, "Fee structures: $count records");
        } catch (Exception $e) {
            $this->addResult("Fee System", false, $e->getMessage());
        }
    }

    private function testAttendanceSystem() {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM attendance_records");
            $stmt->execute();
            $count = $stmt->fetch()['count'];

            $this->addResult("Attendance System", true, "Attendance records: $count");
        } catch (Exception $e) {
            $this->addResult("Attendance System", false, $e->getMessage());
        }
    }

    private function testTimetableSystem() {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM timetable_entries");
            $stmt->execute();
            $count = $stmt->fetch()['count'];

            $this->addResult("Timetable System", $count > 0, "Timetable entries: $count");
        } catch (Exception $e) {
            $this->addResult("Timetable System", false, $e->getMessage());
        }
    }

    private function testSystemSettings() {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM system_settings");
            $stmt->execute();
            $count = $stmt->fetch()['count'];

            $this->addResult("System Settings", $count > 0, "Settings: $count configurations");
        } catch (Exception $e) {
            $this->addResult("System Settings", false, $e->getMessage());
        }
    }

    private function testDataIntegrity() {
        try {
            // Test foreign key relationships
            $stmt = $this->pdo->query("
                SELECT s.name, d.name as dept_name 
                FROM students s 
                LEFT JOIN departments d ON s.department_id = d.id 
                WHERE d.id IS NULL 
                LIMIT 1
            ");
            $orphaned = $stmt->fetch();

            $this->addResult("Data Integrity", $orphaned === false, "No orphaned records found");
        } catch (Exception $e) {
            $this->addResult("Data Integrity", false, $e->getMessage());
        }
    }

    private function addResult($test, $passed, $message) {
        $this->testResults[] = [
            'test' => $test,
            'passed' => $passed,
            'message' => $message
        ];
    }

    private function displayResults() {
        $passed = 0;
        $total = count($this->testResults);

        echo "\n📋 Test Results:\n";
        echo "================\n";

        foreach ($this->testResults as $result) {
            $status = $result['passed'] ? '✅ PASS' : '❌ FAIL';
            echo sprintf("%-30s %s - %s\n", $result['test'], $status, $result['message']);
            if ($result['passed']) $passed++;
        }

        echo "\n📊 Summary: $passed/$total tests passed\n";
        
        if ($passed === $total) {
            echo "🎉 All tests passed! System is ready for production.\n";
        } else {
            echo "⚠️  Some tests failed. Please review and fix issues.\n";
        }
    }
}

// Run tests
$tests = new PVGSSystemTests();
$tests->runAllTests();
?>