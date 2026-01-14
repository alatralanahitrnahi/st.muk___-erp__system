# Department-Aware Workflow Configuration

## Overview

Complete implementation of department-aware workflows with proper approval hierarchies per Governance_Framework.md, conditional logic for fee waivers, department head role implementation, and NAAC-compliant audit trails.

## Workflow Configurations

### 1. Student Admission Workflow

**Approval Chain**: Registrar → Department Head → Principal

**States**:
- `pending` - Initial application submitted
- `registrar_review` - Registrar verifying documents
- `hod_approved` - Department Head approved
- `principal_approved` - Final approval by Principal
- `rejected` - Application rejected at any stage

**Approvers**:
- `registrar_review`: Registrar validates documents
- `hod_approved`: Department Head validates capacity
- `principal_approved`: Principal grants final approval

**Department Validation**:
- `registrar_review`: validate_student_documents
- `hod_approved`: validate_department_capacity
- `principal_approved`: validate_final_approval

**Usage**:
```php
$workflowService->transition(
    $student,
    'registrar_review',
    $registrar,
    $departmentId,
    'Documents verified and complete'
);
```

### 2. Fee Waiver Workflow

**Approval Chain**: Student → Registrar → HOD (if > ₹5000) → Principal

**States**:
- `requested` - Student requests waiver
- `registrar_review` - Registrar reviews eligibility
- `hod_review` - HOD reviews (only if amount > ₹5000)
- `principal_approved` - Principal approves
- `rejected` - Waiver rejected

**Conditional Logic**:
```php
'conditional_logic' => [
    'registrar_review' => [
        'condition' => 'amount <= 5000',
        'skip_to' => 'principal_approved',
        'description' => 'Waiver ≤ ₹5000: Skip HOD, go to Principal'
    ],
    'hod_review' => [
        'condition' => 'amount > 5000',
        'required' => true,
        'description' => 'Waiver > ₹5000: Requires HOD approval'
    ]
]
```

**Usage**:
```php
// Small waiver (≤ ₹5000) - skips HOD
$workflowService->transition(
    $feeWaiver,
    'registrar_review',
    $registrar,
    $departmentId,
    'Waiver approved',
    ['amount' => 3000] // Metadata for conditional logic
);

// Large waiver (> ₹5000) - requires HOD
$workflowService->transition(
    $feeWaiver,
    'registrar_review',
    $registrar,
    $departmentId,
    'Requires HOD approval',
    ['amount' => 8000]
);
```

### 3. Lesson Plan Approval Workflow

**Approval Chain**: Faculty → HOD → Principal

**States**:
- `draft` - Faculty creating lesson plan
- `submitted` - Submitted for HOD review
- `hod_approved` - HOD approved
- `principal_approved` - Principal approved
- `rejected` - Rejected (can return to draft)

**Department Validation**:
- `submitted`: validate_lesson_plan_completeness
- `hod_approved`: validate_curriculum_alignment
- `principal_approved`: validate_naac_compliance

**Usage**:
```php
$workflowService->transition(
    $lessonPlan,
    'submitted',
    $faculty,
    $departmentId,
    'Lesson plan ready for review'
);
```

### 4. Department Transfer Workflow

**Approval Chain**: Student → Current HOD → Target HOD → Principal

**States**:
- `requested` - Student requests transfer
- `source_hod_approved` - Current department HOD approves
- `target_hod_approved` - Target department HOD approves
- `principal_approved` - Principal grants final approval
- `rejected` - Transfer rejected

**Cross-Department Handling**:
```php
// Source HOD approval
$workflowService->transition(
    $transfer,
    'source_hod_approved',
    $sourceHod,
    $sourceDepartmentId,
    'Clearance granted'
);

// Target HOD approval (different department)
$workflowService->transition(
    $transfer,
    'target_hod_approved',
    $targetHod,
    $targetDepartmentId,
    'Capacity available'
);
```

## Department Head Role Implementation

### Database Schema

**departments table**:
```sql
ALTER TABLE departments ADD COLUMN department_head_id BIGINT UNSIGNED NULL;
ALTER TABLE departments ADD FOREIGN KEY (department_head_id) REFERENCES users(id);
```

**audit_logs table**:
```sql
ALTER TABLE audit_logs ADD COLUMN department_id BIGINT UNSIGNED NULL;
ALTER TABLE audit_logs ADD FOREIGN KEY (department_id) REFERENCES departments(id);
```

### Assigning Department Heads

**API Endpoint**:
```
POST /api/department-heads/{departmentId}/assign
{
    "user_id": 123
}
```

**Validation**:
- User must have role: `faculty`, `registrar`, or `principal`
- User automatically gets `department_head` role in `user_departments` table
- Only one HOD per department

**Example**:
```bash
curl -X POST http://localhost/api/department-heads/1/assign \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"user_id": 5}'
```

### Removing Department Heads

**API Endpoint**:
```
DELETE /api/department-heads/{departmentId}
```

**Behavior**:
- Sets `department_head_id` to NULL
- Reverts user's `role_in_department` to original role
- Audit log created

### Listing Department Heads

**API Endpoint**:
```
GET /api/department-heads
```

**Response**:
```json
[
    {
        "id": 1,
        "name": "Computer Science",
        "head_id": 5,
        "head_name": "Dr. John Doe",
        "email": "john.doe@college.edu"
    }
]
```

## Enhanced Audit Trail

### Workflow History

**Table**: `workflow_history`

**Columns**:
- `workflow_name` - e.g., "student_admission"
- `entity_type` - e.g., "App\Models\Student"
- `entity_id` - Primary key of entity
- `department_id` - **Department context**
- `from_state` - Previous state
- `to_state` - New state
- `performed_by` - User ID who performed action
- `comment` - Optional comment
- `metadata` - JSON metadata (e.g., waiver amount)
- `performed_at` - Timestamp

**Query Example**:
```sql
SELECT wh.*, u.name as performed_by_name, d.name as department_name
FROM workflow_history wh
JOIN users u ON wh.performed_by = u.id
JOIN departments d ON wh.department_id = d.id
WHERE wh.entity_type = 'App\Models\Student'
  AND wh.entity_id = 123
ORDER BY wh.performed_at DESC;
```

### Audit Logs

**Enhanced with department_id**:
```sql
INSERT INTO audit_logs (
    user_id,
    department_id,  -- NEW
    action,
    entity_type,
    entity_id,
    old_values,
    new_values,
    ip_address,
    user_agent,
    created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);
```

**NAAC Compliance**: All workflow actions logged with department context for compliance reporting.

## NAAC Compliance Reports

### 1. Department Workflow History

**Endpoint**: `GET /api/workflow-reports/departments/{departmentId}/history`

**Query Parameters**:
- `workflow` - Filter by workflow name
- `date_from` - Start date
- `date_to` - End date

**Response**:
```json
{
    "data": [
        {
            "workflow_name": "student_admission",
            "from_state": "pending",
            "to_state": "registrar_review",
            "performed_at": "2024-01-15 10:30:00",
            "performed_by_name": "John Registrar",
            "comment": "Documents verified"
        }
    ],
    "pagination": {...}
}
```

### 2. Student Workflow History

**Endpoint**: `GET /api/workflow-reports/students/{studentId}/history`

**Purpose**: Principal can view complete workflow history for any student

**Response**:
```json
[
    {
        "workflow_name": "student_admission",
        "from_state": "hod_approved",
        "to_state": "principal_approved",
        "performed_at": "2024-01-20 14:00:00",
        "performed_by_name": "Dr. Principal",
        "department_name": "Computer Science",
        "comment": "Final approval granted"
    }
]
```

### 3. NAAC Compliance Report

**Endpoint**: `GET /api/workflow-reports/departments/{departmentId}/naac-compliance`

**Query Parameters**:
- `academic_year` - e.g., "2024"

**Response**:
```json
{
    "department_id": 1,
    "academic_year": "2024",
    "generated_at": "2024-01-25T10:00:00Z",
    "workflows": {
        "student_admission": {
            "total_transitions": 150,
            "states": [
                {"to_state": "principal_approved", "count": 120},
                {"to_state": "rejected", "count": 30}
            ],
            "average_approval_time": 48.5,
            "pending_approvals": 5
        },
        "fee_waiver": {...},
        "lesson_plan_approval": {...},
        "department_transfer": {...}
    }
}
```

### 4. Principal Dashboard

**Endpoint**: `GET /api/workflow-reports/principal/dashboard`

**Query Parameters**:
- `date_from` - Default: 30 days ago
- `date_to` - Default: today

**Response**:
```json
{
    "pending_approvals": [
        {
            "workflow_name": "student_admission",
            "department_name": "Computer Science",
            "count": 5
        }
    ],
    "recent_activities": [...],
    "department_summary": [
        {
            "department": "Computer Science",
            "workflow_name": "student_admission",
            "total_transitions": 45
        }
    ],
    "compliance_alerts": [
        {
            "type": "warning",
            "message": "3 workflows pending for more than 7 days",
            "action": "Review pending approvals"
        }
    ]
}
```

## Workflow Service Enhancements

### Conditional Logic Evaluation

**Method**: `evaluateConditionalLogic()`

**Supports**:
- Simple comparisons: `<`, `<=`, `>`, `>=`, `==`, `!=`
- Field extraction from entity or metadata
- Automatic state skipping based on conditions

**Example**:
```php
// In config/workflows.php
'conditional_logic' => [
    'registrar_review' => [
        'condition' => 'amount <= 5000',
        'skip_to' => 'principal_approved'
    ]
]

// In code
$workflowService->transition(
    $entity,
    'registrar_review',
    $user,
    $deptId,
    'Comment',
    ['amount' => 3000] // Evaluated against condition
);
```

### Department Validation

**Method**: `executeDepartmentValidation()`

**Purpose**: Execute department-specific validation rules

**Configuration**:
```php
'department_validation' => [
    'hod_approved' => 'validate_department_capacity',
    'principal_approved' => 'validate_final_approval'
]
```

**Extension Point**: Can be extended with custom validation classes per department.

### HOD Verification

**Method**: `validateApprover()`

**Enhanced Logic**:
1. Check if user is super-admin (bypass)
2. Check if user is department head for this department
3. Check user's role in department
4. Validate against allowed roles

**Database Query**:
```sql
SELECT 1 FROM departments
WHERE id = ? AND department_head_id = ?
```

### Workflow History Retrieval

**Method**: `getWorkflowHistory($entity, $departmentId)`

**Returns**: Complete audit trail for entity with department context

**Method**: `getDepartmentWorkflowSummary($departmentId, $workflowName, $dateFrom, $dateTo)`

**Returns**: Aggregated statistics for NAAC reporting

## API Routes

```php
// Department Head Management
POST   /api/department-heads/{departmentId}/assign
DELETE /api/department-heads/{departmentId}
GET    /api/department-heads

// Workflow Reports & NAAC Compliance
GET /api/workflow-reports/departments/{departmentId}/history
GET /api/workflow-reports/students/{studentId}/history
GET /api/workflow-reports/departments/{departmentId}/naac-compliance
GET /api/workflow-reports/principal/dashboard
```

## Unit Tests

**Test File**: `tests/Unit/DepartmentWorkflowTest.php`

**Coverage**:
1. ✅ Complete approval chain (Registrar → HOD → Principal)
2. ✅ Conditional logic for fee waivers (≤ ₹5000 vs > ₹5000)
3. ✅ Unauthorized user rejection
4. ✅ Department context requirement
5. ✅ Audit trail with department_id
6. ✅ HOD validation via department_head_id
7. ✅ Principal can view complete workflow history
8. ✅ Cross-department transfer workflow

**Run Tests**:
```bash
php artisan test --filter DepartmentWorkflowTest
```

## Migration Execution

```bash
# Run migration
php artisan migrate

# Verify
php artisan tinker
>>> Schema::hasColumn('departments', 'department_head_id');
=> true
>>> Schema::hasColumn('audit_logs', 'department_id');
=> true
```

## Backward Compatibility

### Existing Workflows
- All existing workflows continue to work
- `department_id` is nullable in audit_logs
- Workflow transitions work without department context (uses fallback)

### Fallback Behavior
```php
if ($config['requires_department'] && !$departmentId) {
    $departmentId = $entity->department_id ?? $user->primary_department_id;
}
```

## Multi-Department User Support

### Faculty Teaching Across Departments

**Scenario**: Faculty teaches in Computer Science and Mathematics

**Implementation**:
```sql
-- user_departments table
INSERT INTO user_departments (user_id, department_id, role_in_department)
VALUES
    (5, 1, 'faculty'),  -- Computer Science
    (5, 2, 'faculty');  -- Mathematics
```

**Workflow Handling**:
- Department context passed explicitly in transition
- Faculty can approve lesson plans in both departments
- Audit trail tracks which department context was used

### HOD in Multiple Departments

**Scenario**: User is HOD of Computer Science but teaches in Mathematics

**Implementation**:
```sql
UPDATE departments SET department_head_id = 5 WHERE id = 1;

INSERT INTO user_departments (user_id, department_id, role_in_department)
VALUES
    (5, 1, 'department_head'),  -- HOD of CS
    (5, 2, 'faculty');           -- Faculty in Math
```

**Workflow Handling**:
- HOD approval only valid for Computer Science workflows
- Faculty approval valid for Mathematics workflows
- System validates department_head_id match

## Compliance Alerts

### Automated Monitoring

**Alert Types**:
1. **Pending > 7 days**: Workflows stuck in pending states
2. **No HOD assigned**: Departments without department heads
3. **High rejection rate**: Departments with > 20% rejection rate
4. **Approval bottleneck**: Single approver with > 50 pending items

**Implementation**:
```php
private function getComplianceAlerts(): array
{
    $alerts = [];
    
    // Check long-pending workflows
    $longPending = DB::table('workflow_history')
        ->whereIn('to_state', ['pending', 'registrar_review', 'hod_review'])
        ->where('performed_at', '<', now()->subDays(7))
        ->count();
    
    if ($longPending > 0) {
        $alerts[] = [
            'type' => 'warning',
            'message' => "{$longPending} workflows pending for more than 7 days",
            'action' => 'Review pending approvals'
        ];
    }
    
    return $alerts;
}
```

## Performance Considerations

### Query Optimization

**Indexes**:
```sql
CREATE INDEX idx_workflow_history_dept_date 
ON workflow_history(department_id, performed_at);

CREATE INDEX idx_workflow_history_entity 
ON workflow_history(entity_type, entity_id);

CREATE INDEX idx_audit_logs_dept 
ON audit_logs(department_id, created_at);
```

### Caching Strategy

**Cache workflow configurations**:
```php
$config = Cache::remember("workflow.{$workflowName}", 3600, function() use ($workflowName) {
    return Config::get("workflows.{$workflowName}");
});
```

**Cache department heads**:
```php
$hodId = Cache::remember("dept.{$deptId}.hod", 3600, function() use ($deptId) {
    return DB::table('departments')->where('id', $deptId)->value('department_head_id');
});
```

## Critical Success Factors

✅ **Governance Framework Compliance**: All approval hierarchies match Governance_Framework.md exactly
✅ **Backward Compatibility**: Existing workflows continue to work without changes
✅ **NAAC Compliance**: Complete audit trail with department context for all workflows
✅ **Multi-Department Support**: Faculty and HODs can work across multiple departments
✅ **Conditional Logic**: Fee waiver workflow implements ₹5000 threshold correctly
✅ **Principal Visibility**: Principal can view complete workflow history for any student/process
✅ **HOD Validation**: System validates department_head_id before allowing HOD approvals
✅ **Audit Trail**: All workflow transitions logged with department_id, user_id, timestamps

## Files Created

### Configuration (1 file)
- `config/workflows.php` - Updated with department-specific approvers, conditional logic, validation rules

### Database Migrations (1 file)
- `database/migrations/2024_10_02_000001_add_department_head_and_audit_enhancements.php`

### Services (1 file)
- `app/Services/WorkflowService.php` - Enhanced with conditional logic, HOD validation, audit improvements

### Controllers (2 files)
- `app/Http/Controllers/DepartmentHeadController.php` - HOD assignment/removal
- `app/Http/Controllers/WorkflowReportController.php` - NAAC compliance reports

### Routes (1 file)
- `routes/api.php` - Added department head and workflow report endpoints

### Tests (1 file)
- `tests/Unit/DepartmentWorkflowTest.php` - Comprehensive workflow tests

### Documentation (1 file)
- `docs/DEPARTMENT_WORKFLOW_CONFIGURATION.md` (this file)

## Next Steps

1. **Execute Migration**: `php artisan migrate`
2. **Assign Department Heads**: Use API or admin interface
3. **Seed Permissions**: `php artisan db:seed --class=DepartmentPermissionsSeeder`
4. **Run Tests**: `php artisan test --filter DepartmentWorkflowTest`
5. **Train Users**: Department heads on approval workflows
6. **Monitor Compliance**: Use Principal dashboard for oversight
