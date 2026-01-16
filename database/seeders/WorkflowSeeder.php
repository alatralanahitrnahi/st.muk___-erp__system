<?php

$dbPath = __DIR__ . '/../database.sqlite';
$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$definitions = [
    [
        'workflow_type' => 'student_admission',
        'name' => 'Student Admission Workflow',
        'states' => json_encode(['pending_registrar', 'pending_hod', 'pending_principal', 'approved', 'rejected']),
        'transitions' => json_encode([
            'pending_registrar' => ['approve' => 'pending_hod', 'reject' => 'rejected'],
            'pending_hod' => ['approve' => 'pending_principal', 'reject' => 'rejected'],
            'pending_principal' => ['approve' => 'approved', 'reject' => 'rejected']
        ])
    ],
    [
        'workflow_type' => 'fee_waiver',
        'name' => 'Fee Waiver Workflow',
        'states' => json_encode(['pending_registrar', 'pending_hod', 'pending_principal', 'approved', 'rejected']),
        'transitions' => json_encode([
            'pending_registrar' => ['approve' => 'pending_hod', 'approve_direct' => 'pending_principal', 'reject' => 'rejected'],
            'pending_hod' => ['approve' => 'pending_principal', 'reject' => 'rejected'],
            'pending_principal' => ['approve' => 'approved', 'reject' => 'rejected']
        ])
    ],
    [
        'workflow_type' => 'lesson_plan',
        'name' => 'Lesson Plan Approval Workflow',
        'states' => json_encode(['draft', 'pending_hod', 'revision_required', 'pending_principal', 'approved', 'rejected']),
        'transitions' => json_encode([
            'draft' => ['submit' => 'pending_hod'],
            'pending_hod' => ['approve' => 'pending_principal', 'request_revision' => 'revision_required', 'reject' => 'rejected'],
            'revision_required' => ['resubmit' => 'pending_hod'],
            'pending_principal' => ['approve' => 'approved', 'reject' => 'rejected']
        ])
    ],
    [
        'workflow_type' => 'department_transfer',
        'name' => 'Department Transfer Workflow',
        'states' => json_encode(['pending_source_hod', 'pending_target_hod', 'pending_principal', 'approved', 'rejected']),
        'transitions' => json_encode([
            'pending_source_hod' => ['approve' => 'pending_target_hod', 'reject' => 'rejected'],
            'pending_target_hod' => ['approve' => 'pending_principal', 'reject' => 'rejected'],
            'pending_principal' => ['approve' => 'approved', 'reject' => 'rejected']
        ])
    ]
];

foreach ($definitions as $def) {
    $stmt = $pdo->prepare('INSERT OR REPLACE INTO workflow_definitions (workflow_type, name, states, transitions) VALUES (?, ?, ?, ?)');
    $stmt->execute([$def['workflow_type'], $def['name'], $def['states'], $def['transitions']]);
}

echo "Workflow definitions seeded successfully\n";
