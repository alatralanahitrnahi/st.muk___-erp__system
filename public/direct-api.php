<?php
require __DIR__.'/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Configuration
define('JWT_SECRET', getenv('JWT_SECRET') ?: 'pvgs-erp-secret-key-2026-minimum-256-bits-required-for-hs256-algorithm');
define('JWT_EXPIRY', 900); // 15 minutes
define('RATE_LIMIT', 60); // requests per minute
define('DB_PATH', __DIR__.'/../database/database.sqlite');

// Database connection
$pdo = new PDO('sqlite:'.DB_PATH);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Role permissions matrix
$PERMISSIONS = [
    'super-admin' => ['*'],
    'principal' => ['view_all', 'manage_all', 'approve_all'],
    'registrar' => ['view_department', 'manage_department', 'approve_department'],
    'faculty' => ['view_students', 'mark_attendance', 'enter_results'],
    'student' => ['view_own', 'view_results', 'pay_fees'],
];

// Helper functions
function respond($success, $message, $data = null, $meta = [], $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'meta' => array_merge(['timestamp' => date('c')], $meta)
    ]);
    exit;
}

function getAuthToken() {
    $headers = getallheaders();
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
        return $matches[1];
    }
    return null;
}

function verifyToken($token) {
    try {
        return (array) JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
    } catch (Exception $e) {
        return null;
    }
}

function checkPermission($user, $permission, $departmentId = null) {
    global $PERMISSIONS;
    $perms = $PERMISSIONS[$user['role']] ?? [];
    
    if (in_array('*', $perms)) return true;
    if (in_array($permission, $perms)) {
        if ($departmentId && $user['role'] === 'registrar') {
            // Check department access
            return true; // Simplified - should check user_departments table
        }
        return true;
    }
    return false;
}

function rateLimit($userId) {
    global $pdo;
    $key = "rate_limit_{$userId}_" . floor(time() / 60);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM activity_logs WHERE user_id = ? AND created_at > datetime('now', '-1 minute')");
    $stmt->execute([$userId]);
    $count = $stmt->fetchColumn();
    
    if ($count >= RATE_LIMIT) {
        respond(false, 'Rate limit exceeded', null, [], 429);
    }
}

// Parse request

// Public health check
if ($method === "GET" && $path === "/health") {
    include __DIR__ . "/health.php";
    exit;
}
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/direct-api.php', '', $path);
$segments = array_filter(explode('/', $path));
$input = json_decode(file_get_contents('php://input'), true) ?: [];

// Public endpoints
if ($method === 'POST' && $path === '/api/login') {
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($password, $user['password'])) {
        respond(false, 'Invalid credentials', null, [], 401);
    }
    
    $payload = [
        'user_id' => $user['id'],
        'email' => $user['email'],
        'role' => $user['role'],
        'user_type' => $user['user_type'],
        'iat' => time(),
        'exp' => time() + JWT_EXPIRY
    ];
    
    $token = JWT::encode($payload, JWT_SECRET, 'HS256');
    
    respond(true, 'Login successful', [
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'user_type' => $user['user_type']
        ]
    ]);
}

// Protected endpoints - require authentication
$token = getAuthToken();
if (!$token) {
    respond(false, 'Authentication required', null, [], 401);
}

$payload = verifyToken($token);
if (!$payload) {
    respond(false, 'Invalid or expired token', null, [], 401);
}

$userId = $payload['user_id'];
$userRole = $payload['role'];

// Rate limiting
rateLimit($userId);

// Get current user
if ($method === 'GET' && $path === '/api/user') {
    $stmt = $pdo->prepare('SELECT id, name, email, user_type, role FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    respond(true, 'User retrieved', $user);
}

// Departments
if ($method === 'GET' && $path === '/api/departments') {
    if (!checkPermission($payload, 'view_all') && !checkPermission($payload, 'view_department')) {
        respond(false, 'Access denied', null, [], 403);
    }
    
    $stmt = $pdo->query('SELECT * FROM departments WHERE is_active = 1');
    $departments = $stmt->fetchAll();
    respond(true, 'Departments retrieved', $departments);
}

// Students
if ($method === 'GET' && preg_match('#^/api/students$#', $path)) {
    $deptId = $_GET['department_id'] ?? null;
    
    $sql = 'SELECT s.*, u.name, u.email, p.name as program_name, d.name as department_name 
            FROM students s 
            JOIN users u ON s.user_id = u.id 
            JOIN programs p ON s.program_id = p.id 
            JOIN departments d ON s.department_id = d.id 
            WHERE 1=1';
    
    $params = [];
    if ($deptId) {
        $sql .= ' AND s.department_id = ?';
        $params[] = $deptId;
    }
    
    $sql .= ' LIMIT 50';
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll();
    
    respond(true, 'Students retrieved', $students, ['department_id' => $deptId]);
}

// Attendance
if ($method === 'GET' && $path === '/api/attendance') {
    $deptId = $_GET['department_id'] ?? null;
    $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');
    
    $sql = 'SELECT a.*, s.name as student_name, u.name as marked_by_name 
            FROM attendance_records a
            JOIN students st ON a.student_id = st.id
            JOIN users s ON st.user_id = s.id
            JOIN users u ON a.marked_by = u.id
            WHERE a.attendance_date BETWEEN ? AND ?';
    
    $params = [$dateFrom, $dateTo];
    if ($deptId) {
        $sql .= ' AND st.department_id = ?';
        $params[] = $deptId;
    }
    
    $sql .= ' ORDER BY a.attendance_date DESC LIMIT 100';
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $attendance = $stmt->fetchAll();
    
    respond(true, 'Attendance retrieved', $attendance);
}

if ($method === 'POST' && $path === '/api/attendance') {
    if (!checkPermission($payload, 'mark_attendance')) {
        respond(false, 'Access denied', null, [], 403);
    }
    
    $studentId = $input['student_id'] ?? null;
    $subjectId = $input['subject_id'] ?? null;
    $date = $input['date'] ?? date('Y-m-d');
    $status = $input['status'] ?? 'present';
    
    if (!$studentId || !$subjectId) {
        respond(false, 'Missing required fields', null, [], 400);
    }
    
    $stmt = $pdo->prepare('INSERT INTO attendance_records (student_id, subject_id, attendance_date, status, marked_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, datetime("now"), datetime("now"))');
    $stmt->execute([$studentId, $subjectId, $date, $status, $userId]);
    
    respond(true, 'Attendance marked', ['id' => $pdo->lastInsertId()], [], 201);
}

// Default 404
respond(false, 'Endpoint not found', null, [], 404);
