<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$health = [
    'status' => 'healthy',
    'timestamp' => date('c'),
    'version' => '1.0.0',
    'checks' => []
];

// Check 1: Database Connection
try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->query('SELECT 1');
    $health['checks']['database'] = ['status' => 'ok', 'message' => 'Connected'];
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
    'free_percent' => $percent,
    'free_gb' => round($free / 1024 / 1024 / 1024, 2)
];

// Check 3: Database Performance
$start = microtime(true);
$userCount = $pdo->query('SELECT COUNT(*) FROM users WHERE is_active = 1')->fetchColumn();
$queryTime = round((microtime(true) - $start) * 1000, 2);
$health['checks']['database_performance'] = [
    'status' => $queryTime < 100 ? 'ok' : 'warning',
    'query_time_ms' => $queryTime,
    'active_users' => $userCount
];

// Check 4: File Permissions
$writable = is_writable(__DIR__ . '/../storage/logs');
$health['checks']['file_permissions'] = [
    'status' => $writable ? 'ok' : 'error',
    'storage_writable' => $writable
];

// Overall status
if ($health['status'] === 'unhealthy') {
    http_response_code(503);
} else {
    http_response_code(200);
}

echo json_encode($health, JSON_PRETTY_PRINT);
