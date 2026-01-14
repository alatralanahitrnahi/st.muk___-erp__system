# Workflow Test Plans - Department-Aware Architecture

**Date**: 2024-01-14  
**Status**: Ready for Execution  
**Workflows**: 4 critical workflows

---

## Workflow 1: Student Admission

### Test Case: SA-001 - Complete Admission Flow

**Actors**:
- Student (applicant)
- Registrar (department=1, Science)
- HOD (department=1, Science)
- Principal

**Preconditions**:
- Student has submitted application
- Application assigned to Science department

**Test Steps**:

| Step | Actor | Action | Expected Result | Validation |
|------|-------|--------|-----------------|------------|
| 1 | Student | Submit admission application | State: `pending_registrar` | workflow_history entry created |
| 2 | Registrar | Review documents | Documents marked reviewed | audit_trail updated |
| 3 | Registrar | Approve application | State: `pending_hod` | Routed to HOD |
| 4 | HOD | Review application | Application visible in HOD queue | department_id=1 verified |
| 5 | HOD | Approve application | State: `pending_principal` | Routed to Principal |
| 6 | Principal | Final approval | State: `approved` | Student record created |
| 7 | System | Generate admission letter | Letter generated | department_id=1 in letter |

**Expected Audit Trail**:
```json
[
  {"from_state": null, "to_state": "pending_registrar", "performed_by": "student", "department_id": 1},
  {"from_state": "pending_registrar", "to_state": "pending_hod", "performed_by": "registrar", "department_id": 1},
  {"from_state": "pending_hod", "to_state": "pending_principal", "performed_by": "hod", "department_id": 1},
  {"from_state": "pending_principal", "to_state": "approved", "performed_by": "principal", "department_id": 1}
]
```

**Success Criteria**:
- ✅ All state transitions recorded
- ✅ Department context maintained throughout
- ✅ Audit trail complete with timestamps
- ✅ NAAC compliance data captured

---

## Workflow 2: Fee Waiver

### Test Case: FW-001 - Fee Waiver > ₹5000 (Requires HOD)

**Actors**:
- Student (department=1, Science)
- Registrar (department=1)
- HOD (department=1)
- Principal

**Preconditions**:
- Student enrolled in Science department
- Fee waiver amount: ₹6,000

**Test Steps**:

| Step | Actor | Action | Expected Result | Validation |
|------|-------|--------|-----------------|------------|
| 1 | Student | Submit waiver request (₹6,000) | State: `pending_registrar` | Amount > ₹5,000 flagged |
| 2 | Registrar | Verify eligibility | Documents checked | Comments added |
| 3 | Registrar | Approve request | State: `pending_hod` | Conditional logic: amount > ₹5,000 |
| 4 | HOD | Review financial impact | Impact assessed | Department budget checked |
| 5 | HOD | Approve waiver | State: `pending_principal` | Routed to Principal |
| 6 | Principal | Final approval | State: `approved` | Fee record updated |
| 7 | System | Update fee status | Waiver applied | Student balance reduced |

**Conditional Logic Test**:
```
IF amount <= ₹5,000:
    pending_registrar → approved (skip HOD)
ELSE:
    pending_registrar → pending_hod → pending_principal → approved
```

**Success Criteria**:
- ✅ Conditional routing works correctly
- ✅ HOD approval required for amount > ₹5,000
- ✅ Department budget impact tracked
- ✅ Fee records updated atomically

### Test Case: FW-002 - Fee Waiver ≤ ₹5000 (Skips HOD)

**Test Steps**:

| Step | Actor | Action | Expected Result |
|------|-------|--------|-----------------|
| 1 | Student | Submit waiver request (₹4,000) | State: `pending_registrar` |
| 2 | Registrar | Approve request | State: `pending_principal` |
| 3 | Principal | Final approval | State: `approved` |

**Validation**: HOD step skipped, workflow goes directly from Registrar to Principal

---

## Workflow 3: Lesson Plan Approval

### Test Case: LP-001 - Lesson Plan Submission & Approval

**Actors**:
- Faculty (department=1, Science)
- HOD (department=1)
- Principal

**Preconditions**:
- Faculty assigned to Science department
- Academic year and semester defined

**Test Steps**:

| Step | Actor | Action | Expected Result | Validation |
|------|-------|--------|-----------------|------------|
| 1 | Faculty | Create lesson plan | Draft created | department_id=1 |
| 2 | Faculty | Submit for approval | State: `pending_hod` | Routed to HOD |
| 3 | HOD | Review plan | Plan reviewed | Alignment with curriculum checked |
| 4 | HOD | Request changes | State: `revision_required` | Returned to Faculty |
| 5 | Faculty | Update plan | Plan revised | Version incremented |
| 6 | Faculty | Resubmit | State: `pending_hod` | New version submitted |
| 7 | HOD | Approve plan | State: `pending_principal` | Routed to Principal |
| 8 | Principal | Final approval | State: `approved` | Plan activated |

**NAAC Compliance**:
- Planned vs Actual teaching tracked
- Lesson plan version history maintained
- Approval timestamps recorded
- Department-wise curriculum alignment

**Success Criteria**:
- ✅ Revision workflow works correctly
- ✅ Version history maintained
- ✅ NAAC data captured
- ✅ Department context preserved

---

## Workflow 4: Department Transfer

### Test Case: DT-001 - Student Transfer Between Departments

**Actors**:
- Student (current department=1, Science)
- Source HOD (department=1, Science)
- Target HOD (department=2, Commerce)
- Principal

**Preconditions**:
- Student enrolled in Science department
- Transfer request to Commerce department
- Both departments have capacity

**Test Steps**:

| Step | Actor | Action | Expected Result | Validation |
|------|-------|--------|-----------------|------------|
| 1 | Student | Submit transfer request | State: `pending_source_hod` | Source dept=1, Target dept=2 |
| 2 | Source HOD | Review request | Request reviewed | Academic standing checked |
| 3 | Source HOD | Approve release | State: `pending_target_hod` | Routed to target HOD |
| 4 | Target HOD | Review capacity | Capacity checked | Seat availability verified |
| 5 | Target HOD | Approve admission | State: `pending_principal` | Routed to Principal |
| 6 | Principal | Final approval | State: `approved` | Transfer authorized |
| 7 | System | Execute transfer | department_id updated | Student.department_id: 1→2 |
| 8 | System | Update records | Historical data preserved | Old dept records retained |

**Data Integrity Checks**:
- Student attendance history preserved with original department_id
- Fee records maintain department context
- Result records keep original department_id
- Audit trail shows department change

**Success Criteria**:
- ✅ Cross-department workflow works
- ✅ Data integrity maintained
- ✅ Historical records preserved
- ✅ Both HODs involved in approval

---

## Permission Matrix Testing

### Test Matrix: 5 Roles × 4 Workflows

| Workflow | Super Admin | Principal | Registrar | Faculty | Student |
|----------|-------------|-----------|-----------|---------|---------|
| **Student Admission** |
| Submit | ✅ | ✅ | ✅ | ❌ | ✅ (own) |
| Registrar Approve | ✅ | ✅ | ✅ | ❌ | ❌ |
| HOD Approve | ✅ | ✅ | ❌ | ✅ (if HOD) | ❌ |
| Principal Approve | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Fee Waiver** |
| Submit | ✅ | ✅ | ✅ | ❌ | ✅ (own) |
| Registrar Approve | ✅ | ✅ | ✅ | ❌ | ❌ |
| HOD Approve | ✅ | ✅ | ❌ | ✅ (if HOD) | ❌ |
| Principal Approve | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Lesson Plan** |
| Submit | ✅ | ✅ | ❌ | ✅ | ❌ |
| HOD Approve | ✅ | ✅ | ❌ | ✅ (if HOD) | ❌ |
| Principal Approve | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Department Transfer** |
| Submit | ✅ | ✅ | ✅ | ❌ | ✅ (own) |
| Source HOD Approve | ✅ | ✅ | ❌ | ✅ (if HOD) | ❌ |
| Target HOD Approve | ✅ | ✅ | ❌ | ✅ (if HOD) | ❌ |
| Principal Approve | ✅ | ✅ | ❌ | ❌ | ❌ |

---

## Edge Cases & Error Scenarios

### EC-001: Workflow Without Department Context

**Test**: Submit workflow without department_id
**Expected**: 400 Bad Request - "department_id required"

### EC-002: User Accessing Wrong Department Workflow

**Test**: Faculty from Science tries to approve Commerce workflow
**Expected**: 403 Forbidden - "Access denied to this department"

### EC-003: Invalid State Transition

**Test**: Try to move from `pending_registrar` to `approved` (skipping steps)
**Expected**: 400 Bad Request - "Invalid state transition"

### EC-004: Concurrent Workflow Operations

**Test**: Two users try to approve same workflow simultaneously
**Expected**: One succeeds, other gets 409 Conflict - "Workflow already processed"

### EC-005: Workflow with Missing Approver

**Test**: Submit workflow when HOD not assigned to department
**Expected**: Workflow queued, notification sent to Principal to assign HOD

---

## Performance Benchmarks

### Target Metrics

| Operation | Target | Measurement |
|-----------|--------|-------------|
| Workflow submission | <200ms | Time to create workflow record |
| State transition | <300ms | Time to update state + audit trail |
| Approval queue load | <500ms | Time to fetch pending approvals |
| Workflow history | <500ms | Time to fetch complete history |
| Concurrent operations | 1000/min | Workflows processed per minute |

### Load Test Scenarios

**Scenario 1: Normal Load**
- 100 concurrent users
- 500 workflow operations/hour
- Expected: 99.9% success rate, <500ms p95

**Scenario 2: Peak Load**
- 500 concurrent users
- 2000 workflow operations/hour
- Expected: 99% success rate, <1s p95

**Scenario 3: Stress Test**
- 1000 concurrent users
- 5000 workflow operations/hour
- Expected: Graceful degradation, no data loss

---

## NAAC Compliance Validation

### Required Audit Trail Fields

For each workflow transition:
- ✅ `workflow_id` - Unique identifier
- ✅ `entity_type` - Type of workflow
- ✅ `entity_id` - Related entity ID
- ✅ `department_id` - Department context
- ✅ `from_state` - Previous state
- ✅ `to_state` - New state
- ✅ `performed_by` - User ID
- ✅ `performed_at` - Timestamp
- ✅ `metadata` - Additional context (JSON)

### Compliance Checks

**Check 1**: All workflow transitions recorded
**Check 2**: Department context in all records
**Check 3**: Timestamps accurate and sequential
**Check 4**: User attribution complete
**Check 5**: Metadata includes approval comments

---

## Test Execution Checklist

### Pre-Execution

- [ ] Test database seeded with realistic data
- [ ] All 5 user roles have test accounts
- [ ] Department structure configured (Science, Commerce, Arts)
- [ ] HODs assigned to each department
- [ ] API endpoints deployed and accessible

### Execution

- [ ] Run all 4 workflow test cases
- [ ] Execute permission matrix tests (20 tests)
- [ ] Test all edge cases (5 scenarios)
- [ ] Run performance benchmarks
- [ ] Validate NAAC compliance

### Post-Execution

- [ ] Document all failures with screenshots
- [ ] Generate test report with metrics
- [ ] Create bug tickets for failures
- [ ] Update test cases based on findings
- [ ] Archive test results

---

## Expected Outcomes

**Success Criteria**:
- ✅ 100% of workflow transitions maintain data consistency
- ✅ 100% of audit trails complete and accurate
- ✅ 0 permission leaks (students cannot access admin functions)
- ✅ 0 cross-department data leaks
- ✅ 95% of operations complete within performance targets

**Deliverables**:
- Comprehensive test report (pass/fail for all tests)
- Performance metrics with recommendations
- Security validation report
- NAAC compliance verification
- Ready-to-ship backend confirmation

---

**Document Status**: ✅ Ready for Execution  
**Test Coverage**: 4 workflows × 5 roles × 3 departments = 60+ test cases  
**Estimated Execution Time**: 4-6 hours  
**Prerequisites**: Live API environment with test data
