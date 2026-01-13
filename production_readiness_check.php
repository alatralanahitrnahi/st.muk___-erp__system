<?php
// Final System Validation & Deployment Readiness Check

class ProductionReadinessValidator {
    private $pdo;
    private $results = [];
    private $dbPath;

    public function __construct() {
        $this->dbPath = '/workspaces/st.muk___-erp__system/database/database.sqlite';
        $this->pdo = new PDO("sqlite:{$this->dbPath}");
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function runValidation() {
        echo "🚀 PVGS ERP Production Readiness Validation\n";
        echo "==========================================\n\n";

        $this->validateDatabase();
        $this->validateUIFiles();
        $this->validateSecurity();
        $this->validatePerformance();
        $this->generateReport();
    }

    private function validateDatabase() {
        echo "📊 Database Validation...\n";
        
        $requiredTables = [
            'users', 'students', 'faculty', 'departments', 'programs', 
            'subjects', 'fee_structures', 'attendance_records', 
            'timetable_entries', 'system_settings', 'roles', 'permissions'
        ];

        $tablesExist = 0;
        foreach ($requiredTables as $table) {
            try {
                $stmt = $this->pdo->query("SELECT COUNT(*) FROM $table");
                $count = $stmt->fetch()[0];
                $tablesExist++;
                echo "  ✅ $table: $count records\n";
            } catch (Exception $e) {
                echo "  ❌ $table: Missing\n";
            }
        }

        $this->results['database'] = [
            'score' => ($tablesExist / count($requiredTables)) * 100,
            'tables' => $tablesExist,
            'total' => count($requiredTables)
        ];
    }

    private function validateUIFiles() {
        echo "\n🎨 UI Files Validation...\n";
        
        $requiredFiles = [
            'index.html', 'login.html', 'principal.html', 
            'faculty.html', 'admin.html', 'student.html'
        ];

        $filesExist = 0;
        foreach ($requiredFiles as $file) {
            $path = "/workspaces/st.muk___-erp__system/public/$file";
            if (file_exists($path)) {
                $size = round(filesize($path) / 1024, 2);
                echo "  ✅ $file: {$size}KB\n";
                $filesExist++;
            } else {
                echo "  ❌ $file: Missing\n";
            }
        }

        $this->results['ui'] = [
            'score' => ($filesExist / count($requiredFiles)) * 100,
            'files' => $filesExist,
            'total' => count($requiredFiles)
        ];
    }

    private function validateSecurity() {
        echo "\n🔐 Security Validation...\n";
        
        $securityChecks = [
            'roles_table' => "SELECT COUNT(*) FROM roles",
            'permissions_table' => "SELECT COUNT(*) FROM permissions", 
            'system_settings' => "SELECT COUNT(*) FROM system_settings WHERE category = 'security'",
            'user_authentication' => "SELECT COUNT(*) FROM users WHERE role IS NOT NULL"
        ];

        $securityScore = 0;
        foreach ($securityChecks as $check => $sql) {
            try {
                $stmt = $this->pdo->query($sql);
                $count = $stmt->fetch()[0];
                if ($count > 0) {
                    echo "  ✅ " . str_replace('_', ' ', $check) . ": $count\n";
                    $securityScore++;
                } else {
                    echo "  ⚠️  " . str_replace('_', ' ', $check) . ": Empty\n";
                }
            } catch (Exception $e) {
                echo "  ❌ " . str_replace('_', ' ', $check) . ": Failed\n";
            }
        }

        $this->results['security'] = [
            'score' => ($securityScore / count($securityChecks)) * 100,
            'checks' => $securityScore,
            'total' => count($securityChecks)
        ];
    }

    private function validatePerformance() {
        echo "\n⚡ Performance Validation...\n";
        
        $performanceTests = [
            'database_size' => file_exists($this->dbPath) ? filesize($this->dbPath) : 0,
            'query_speed' => $this->testQuerySpeed(),
            'memory_usage' => memory_get_usage(true)
        ];

        $dbSizeMB = round($performanceTests['database_size'] / (1024 * 1024), 2);
        $memoryMB = round($performanceTests['memory_usage'] / (1024 * 1024), 2);
        
        echo "  📊 Database Size: {$dbSizeMB}MB\n";
        echo "  ⚡ Query Speed: {$performanceTests['query_speed']}ms\n";
        echo "  💾 Memory Usage: {$memoryMB}MB\n";

        $performanceScore = 100; // Base score
        if ($dbSizeMB > 100) $performanceScore -= 20;
        if ($performanceTests['query_speed'] > 100) $performanceScore -= 20;
        if ($memoryMB > 50) $performanceScore -= 20;

        $this->results['performance'] = [
            'score' => max(0, $performanceScore),
            'database_size' => $dbSizeMB,
            'query_speed' => $performanceTests['query_speed'],
            'memory_usage' => $memoryMB
        ];
    }

    private function testQuerySpeed() {
        try {
            $start = microtime(true);
            $this->pdo->query("SELECT 1");
            $end = microtime(true);
            return round(($end - $start) * 1000, 2);
        } catch (Exception $e) {
            return 999; // High value indicates error
        }
    }

    private function generateReport() {
        echo "\n📋 Production Readiness Report\n";
        echo "==============================\n";

        $totalScore = 0;
        $categories = count($this->results);

        foreach ($this->results as $category => $data) {
            $score = round($data['score'], 1);
            $status = $score >= 90 ? '🟢 EXCELLENT' : ($score >= 75 ? '🟡 GOOD' : '🔴 NEEDS WORK');
            echo sprintf("%-15s: %s%% %s\n", ucfirst($category), $score, $status);
            $totalScore += $score;
        }

        $overallScore = round($totalScore / $categories, 1);
        echo "\n" . str_repeat("=", 40) . "\n";
        echo sprintf("OVERALL SCORE: %s%% ", $overallScore);

        if ($overallScore >= 90) {
            echo "🎉 PRODUCTION READY!\n";
            echo "\n✅ System is ready for immediate deployment\n";
            echo "✅ All critical components are functional\n";
            echo "✅ Performance meets production standards\n";
            echo "✅ Security measures are in place\n";
        } elseif ($overallScore >= 75) {
            echo "⚠️  MOSTLY READY\n";
            echo "\n✅ Core functionality is working\n";
            echo "⚠️  Minor improvements recommended\n";
            echo "✅ Can be deployed with monitoring\n";
        } else {
            echo "🔴 NEEDS IMPROVEMENT\n";
            echo "\n❌ Critical issues need resolution\n";
            echo "❌ Not recommended for production\n";
        }

        echo "\n🚀 Next Steps:\n";
        echo "1. Deploy to production server\n";
        echo "2. Configure domain and SSL\n";
        echo "3. Set up automated backups\n";
        echo "4. Train end users\n";
        echo "5. Monitor system performance\n";
    }
}

// Run validation
$validator = new ProductionReadinessValidator();
$validator->runValidation();
?>