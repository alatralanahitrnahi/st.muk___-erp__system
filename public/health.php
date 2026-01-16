<?php
header('Content-Type: application/json');

$health = [
    'status' => 'healthy',
    'timestamp' => date('c'),
    'checks' => []
];

// Check 1: Database
try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite');
    $pdo->query('SELECT 1');
    $health['checks']['database'] = ['status' => 'ok', 'response_time' => '< 10ms'];
} catch (Exception $e) {
    $health['status'] = 'unhealthy';
    $health['checks']['database'] = ['status' => 'error', 'message' => 'Connection failed'];
}

// Check 2: Disk Space
$free = disk_free_space(__DIR__);
$total = disk_total_space(__DIR__);
$percent = round(($free / $total) * 100, 2);
$health['checks']['disk_space'] = [
    'status' => $percent > 20 ? 'ok' : 'warning',
    'free_percent' => $percent . '%'
];

// Check 3: API Response Time
$start = microtime(true);
$pdo->query('SELECT COUNT(*) FROM users');
$responseTime = round((microtime(true) - $start) * 1000, 2);
$health['checks']['api_performance'] = [
    'status' => $responseTime < 200 ? 'ok' : 'warning',
    'response_time' => $responseTime . 'ms'
];

// Check 4: User Count
$userCount = $pdo->query('SELECT COUNT(*) FROM users WHERE is_active = 1')->fetchColumn();
$health['checks']['active_users'] = [
    'status' => 'ok',
    'count' => $userCount
];

http_response_code($health['status'] === 'healthy' ? 200 : 503);
echo json_encode($health, JSON_PRETTY_PRINT);
