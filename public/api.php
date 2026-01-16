<?php
header('Content-Type: application/json');

$dbPath = __DIR__ . '/../database/database.sqlite';
$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Simple router
if ($method === 'POST' && $path === '/api/login') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $token = bin2hex(random_bytes(32));
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'user_type' => $user['user_type'],
                'role' => $user['role']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    }
}
elseif ($method === 'GET' && $path === '/api/departments') {
    $depts = $pdo->query('SELECT * FROM departments WHERE is_active = 1')->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $depts]);
}
elseif ($method === 'GET' && $path === '/api/students') {
    $students = $pdo->query('
        SELECT s.*, u.name, u.email, p.name as program_name, d.name as department_name
        FROM students s
        JOIN users u ON s.user_id = u.id
        JOIN programs p ON s.program_id = p.id
        JOIN departments d ON s.department_id = d.id
        LIMIT 10
    ')->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $students]);
}
elseif ($method === 'GET' && $path === '/api/programs') {
    $programs = $pdo->query('
        SELECT p.*, d.name as department_name
        FROM programs p
        JOIN departments d ON p.department_id = d.id
        WHERE p.is_active = 1
    ')->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $programs]);
}
else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
}
