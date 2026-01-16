<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$dbPath = __DIR__ . '/../database/database.sqlite';
$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/workflow-api.php', '', $path);

// Workflow definitions
$WORKFLOW_DEFINITIONS = [
    'student_admission' => [
        'states' => ['pending_registrar', 'pending_hod', 'pending_principal', 'approved', 'rejected'],
        'transitions' => [
            'pending_registrar' => ['approve' => 'pending_hod', 'reject' => 'rejected'],
            'pending_hod' => ['approve' => 'pending_principal', 'reject' => 'rejected'],
            'pending_principal' => ['approve' => 'approved', 'reject' => 'rejected']
        ]
    ],
    'fee_waiver' => [
        'states' => ['pending_registrar', 'pending_hod', 'pending_principal', 'approved', 'rejected'],
        'transitions' => [
            'pending_registrar' => ['approve' => 'pending_hod', 'approve_direct' => 'pending_principal', 'reject' => 'rejected'],
            'pending_hod' => ['approve' => 'pending_principal', 'reject' => 'rejected'],
            'pending_principal' => ['approve' => 'approved', 'reject' => 'rejected']
        ]
    ],
    'lesson_plan' => [
        'states' => ['draft', 'pending_hod', 'revision_required', 'pending_principal', 'approved', 'rejected'],
        'transitions' => [
            'draft' => ['submit' => 'pending_hod'],
            'pending_hod' => ['approve' => 'pending_principal', 'request_revision' => 'revision_required', 'reject' => 'rejected'],
            'revision_required' => ['resubmit' => 'pending_hod'],
            'pending_principal' => ['approve' => 'approved', 'reject' => 'rejected']
        ]
    ],
    'department_transfer' => [
        'states' => ['pending_source_hod', 'pending_target_hod', 'pending_principal', 'approved', 'rejected'],
        'transitions' => [
            'pending_source_hod' => ['approve' => 'pending_target_hod', 'reject' => 'rejected'],
            'pending_target_hod' => ['approve' => 'pending_principal', 'reject' => 'rejected'],
            'pending_principal' => ['approve' => 'approved', 'reject' => 'rejected']
        ]
    ]
];

function getUserFromToken($pdo, $token) {
    // Simplified auth for testing - return admin user
    $stmt = $pdo->prepare('SELECT * FROM users WHERE role = ? LIMIT 1');
    $stmt->execute(['super_admin']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        // Fallback to any user
        $stmt = $pdo->prepare('SELECT * FROM users LIMIT 1');
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return $user;
}

function canPerformAction($user, $workflow, $action) {
    $state = $workflow['current_state'];
    $type = $workflow['workflow_type'];
    
    // Super admin can do anything
    if (in_array($user['role'], ['super_admin', 'super-admin'])) return true;
    
    // Principal can approve at any stage
    if ($user['role'] === 'principal') return true;
    
    // Registrar can approve at registrar stage
    if ($user['role'] === 'registrar' && $state === 'pending_registrar') return true;
    
    // HOD can approve at HOD stage for their department
    if ($user['role'] === 'hod' && strpos($state, 'hod') !== false) {
        if (isset($user['department_id']) && $user['department_id'] == $workflow['department_id']) {
            return true;
        }
    }
    
    return false;
}

// POST /workflows - Create workflow
if ($method === 'POST' && $path === '/workflows') {
    $input = json_decode(file_get_contents('php://input'), true);
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? 'test';
    $user = getUserFromToken($pdo, $token);
    
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    $type = $input['workflow_type'] ?? '';
    $entityType = $input['entity_type'] ?? '';
    $entityId = $input['entity_id'] ?? 0;
    $deptId = $input['department_id'] ?? 0;
    $metadata = json_encode($input['metadata'] ?? []);
    
    if (!isset($WORKFLOW_DEFINITIONS[$type])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid workflow type']);
        exit;
    }
    
    if (!$deptId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'department_id required']);
        exit;
    }
    
    $initialState = $WORKFLOW_DEFINITIONS[$type]['states'][0];
    
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('INSERT INTO workflows (workflow_type, entity_type, entity_id, current_state, department_id, initiated_by, metadata) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$type, $entityType, $entityId, $initialState, $deptId, $user['id'], $metadata]);
        $workflowId = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare('INSERT INTO workflow_transitions (workflow_id, from_state, to_state, performed_by, action, department_id) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$workflowId, null, $initialState, $user['id'], 'create', $deptId]);
        
        $pdo->commit();
        
        echo json_encode(['success' => true, 'workflow_id' => $workflowId, 'state' => $initialState]);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// POST /workflows/:id/transition - Transition workflow
if ($method === 'POST' && preg_match('#^/workflows/(\d+)/transition$#', $path, $matches)) {
    $workflowId = $matches[1];
    $input = json_decode(file_get_contents('php://input'), true);
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $user = getUserFromToken($pdo, $token);
    
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    $action = $input['action'] ?? '';
    $comments = $input['comments'] ?? '';
    
    $stmt = $pdo->prepare('SELECT * FROM workflows WHERE id = ?');
    $stmt->execute([$workflowId]);
    $workflow = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$workflow) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Workflow not found']);
        exit;
    }
    
    if (!canPerformAction($user, $workflow, $action)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }
    
    $currentState = $workflow['current_state'];
    $type = $workflow['workflow_type'];
    $transitions = $WORKFLOW_DEFINITIONS[$type]['transitions'][$currentState] ?? [];
    
    if (!isset($transitions[$action])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid state transition']);
        exit;
    }
    
    $newState = $transitions[$action];
    
    // Special logic for fee waiver
    if ($type === 'fee_waiver' && $currentState === 'pending_registrar' && $action === 'approve') {
        $metadata = json_decode($workflow['metadata'], true);
        $amount = $metadata['amount'] ?? 0;
        $newState = $amount <= 5000 ? 'pending_principal' : 'pending_hod';
    }
    
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('UPDATE workflows SET current_state = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$newState, $workflowId]);
        
        $stmt = $pdo->prepare('INSERT INTO workflow_transitions (workflow_id, from_state, to_state, performed_by, action, comments, department_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$workflowId, $currentState, $newState, $user['id'], $action, $comments, $workflow['department_id']]);
        
        $pdo->commit();
        
        echo json_encode(['success' => true, 'new_state' => $newState]);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// GET /workflows - List workflows
if ($method === 'GET' && $path === '/workflows') {
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $user = getUserFromToken($pdo, $token);
    
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    $state = $_GET['state'] ?? '';
    $type = $_GET['type'] ?? '';
    $deptId = $_GET['department_id'] ?? '';
    
    $sql = 'SELECT w.*, u.name as initiated_by_name FROM workflows w JOIN users u ON w.initiated_by = u.id WHERE 1=1';
    $params = [];
    
    if ($state) {
        $sql .= ' AND w.current_state = ?';
        $params[] = $state;
    }
    if ($type) {
        $sql .= ' AND w.workflow_type = ?';
        $params[] = $type;
    }
    if ($deptId) {
        $sql .= ' AND w.department_id = ?';
        $params[] = $deptId;
    }
    
    // Filter by user role
    if ($user['role'] === 'hod') {
        $sql .= ' AND w.department_id = ?';
        $params[] = $user['department_id'];
    }
    
    $sql .= ' ORDER BY w.created_at DESC LIMIT 100';
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $workflows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $workflows]);
    exit;
}

// GET /workflows/:id - Get workflow details
if ($method === 'GET' && preg_match('#^/workflows/(\d+)$#', $path, $matches)) {
    $workflowId = $matches[1];
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $user = getUserFromToken($pdo, $token);
    
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    $stmt = $pdo->prepare('SELECT * FROM workflows WHERE id = ?');
    $stmt->execute([$workflowId]);
    $workflow = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$workflow) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Workflow not found']);
        exit;
    }
    
    $stmt = $pdo->prepare('SELECT t.*, u.name as performed_by_name FROM workflow_transitions t JOIN users u ON t.performed_by = u.id WHERE t.workflow_id = ? ORDER BY t.created_at ASC');
    $stmt->execute([$workflowId]);
    $transitions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $workflow['transitions'] = $transitions;
    
    echo json_encode(['success' => true, 'data' => $workflow]);
    exit;
}

http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
