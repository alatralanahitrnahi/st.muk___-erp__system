#!/usr/bin/env php
<?php

$baseUrl = 'http://localhost:8000';

echo "=== Workflow Engine Test Suite ===\n\n";

// Test 1: Create Student Admission Workflow
echo "Test 1: Create Student Admission Workflow\n";
$response = file_get_contents("$baseUrl/api/workflows", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode([
            'workflow_type' => 'student_admission',
            'entity_type' => 'student',
            'entity_id' => 1,
            'department_id' => 1,
            'metadata' => ['student_name' => 'Test Student']
        ])
    ]
]));
$result = json_decode($response, true);
echo "Result: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
echo "Workflow ID: " . ($result['workflow_id'] ?? 'N/A') . "\n";
echo "Initial State: " . ($result['state'] ?? 'N/A') . "\n\n";
$workflowId = $result['workflow_id'] ?? null;

// Test 2: Transition Workflow (Registrar Approve)
if ($workflowId) {
    echo "Test 2: Registrar Approves Application\n";
    $response = file_get_contents("$baseUrl/api/workflows/$workflowId/transition", false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode([
                'action' => 'approve',
                'comments' => 'Documents verified'
            ])
        ]
    ]));
    $result = json_decode($response, true);
    echo "Result: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
    echo "New State: " . ($result['new_state'] ?? 'N/A') . "\n\n";
}

// Test 3: Create Fee Waiver (Amount > 5000)
echo "Test 3: Create Fee Waiver (₹6000 - Requires HOD)\n";
$response = file_get_contents("$baseUrl/api/workflows", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode([
            'workflow_type' => 'fee_waiver',
            'entity_type' => 'fee_payment',
            'entity_id' => 1,
            'department_id' => 1,
            'metadata' => ['amount' => 6000, 'reason' => 'Financial hardship']
        ])
    ]
]));
$result = json_decode($response, true);
echo "Result: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
echo "Workflow ID: " . ($result['workflow_id'] ?? 'N/A') . "\n\n";
$feeWaiverId = $result['workflow_id'] ?? null;

// Test 4: Fee Waiver Conditional Routing
if ($feeWaiverId) {
    echo "Test 4: Registrar Approves Fee Waiver (Should route to HOD)\n";
    $response = file_get_contents("$baseUrl/api/workflows/$feeWaiverId/transition", false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode([
                'action' => 'approve',
                'comments' => 'Eligibility verified'
            ])
        ]
    ]));
    $result = json_decode($response, true);
    echo "Result: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
    echo "New State: " . ($result['new_state'] ?? 'N/A') . " (Expected: pending_hod)\n\n";
}

// Test 5: List Workflows
echo "Test 5: List All Workflows\n";
$response = file_get_contents("$baseUrl/api/workflows");
$result = json_decode($response, true);
echo "Result: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
echo "Total Workflows: " . count($result['data'] ?? []) . "\n\n";

// Test 6: Get Workflow Details
if ($workflowId) {
    echo "Test 6: Get Workflow Details with History\n";
    $response = file_get_contents("$baseUrl/api/workflows/$workflowId");
    $result = json_decode($response, true);
    echo "Result: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
    echo "Transitions: " . count($result['data']['transitions'] ?? []) . "\n\n";
}

// Test 7: Create Lesson Plan Workflow
echo "Test 7: Create Lesson Plan Workflow\n";
$response = file_get_contents("$baseUrl/api/workflows", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode([
            'workflow_type' => 'lesson_plan',
            'entity_type' => 'lesson_plan',
            'entity_id' => 1,
            'department_id' => 1,
            'metadata' => ['subject' => 'Physics', 'semester' => 1]
        ])
    ]
]));
$result = json_decode($response, true);
echo "Result: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
echo "Initial State: " . ($result['state'] ?? 'N/A') . " (Expected: draft)\n\n";

echo "=== Test Suite Complete ===\n";
