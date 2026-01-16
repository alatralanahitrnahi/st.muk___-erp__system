# Day 2: Backend API Testing with Real-World Data

**Date**: 2024-01-14  
**Status**: Ready to Execute  
**Duration**: 4 hours

---

## What We're Testing

Complete real-world college setup from scratch:

1. **System Setup** - Super Admin configures system
2. **Department Creation** - 3 departments (Science, Commerce, Arts)
3. **Academic Structure** - 9 programs, 27 subjects
4. **User Creation** - Principal, HODs, Registrar, Faculty, Students
5. **Fee Management** - Fee structures, payments
6. **Admissions** - 100 student applications → approvals
7. **Attendance** - 30 days of attendance marking
8. **Lesson Plans** - Faculty create → HOD approve → Principal approve
9. **Results** - Marks entry for all students
10. **Workflows** - Test all 4 approval workflows

---

## Step 1: Reset Database with Test Data (10 minutes)

```bash
# Option 1: Keep existing migrations, just reseed
php artisan test:reset

# Option 2: Fresh start (deletes everything)
php artisan test:reset --fresh
```

**What This Creates:**
- 3 Departments
- 9 Programs (3 per department)
- 27 Subjects (3 per program)
- 15 Faculty (5 per department)
- 100 Students (distributed across programs)
- 3,000 Attendance records (30 days × 100 students)
- 300 Results (3 subjects per student)
- 120+ Workflow transitions

---

## Step 2: Test Users Created

| Role | Email | Password | Access |
|------|-------|----------|--------|
| Super Admin | admin@pvgs.edu | password123 | All departments |
| Principal | principal@pvgs.edu | password123 | All departments |
| HOD Science | hod.science@pvgs.edu | password123 | Science only |
| HOD Commerce | hod.commerce@pvgs.edu | password123 | Commerce only |
| HOD Arts | hod.arts@pvgs.edu | password123 | Arts only |
| Registrar | registrar@pvgs.edu | password123 | All departments |
| Faculty 1-15 | faculty1@pvgs.edu | password123 | Assigned department |
| Students 1-100 | student1@pvgs.edu | password123 | Assigned department |

---

## Step 3: Manual API Testing (30 minutes)

### Test 1: Authentication

```bash
# Login as Principal
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "principal@pvgs.edu",
    "password": "password123"
  }'

# Save the token from response
TOKEN="your_token_here"
```

### Test 2: Get Students (Department-Scoped)

```bash
# Get Science department students
curl -X GET http://localhost:8000/api/v1/departments/1/students \
  -H "Authorization: Bearer $TOKEN"

# Should return ~33 students from Science department
```

### Test 3: Get Attendance Report

```bash
# Get attendance report for Science department
curl -X GET "http://localhost:8000/api/v1/departments/1/attendance/report?date_from=2024-01-01&date_to=2024-01-31" \
  -H "Authorization: Bearer $TOKEN"

# Should show attendance percentage (~85%)
```

### Test 4: Get Results

```bash
# Get results for Science department
curl -X GET http://localhost:8000/api/v1/departments/1/results \
  -H "Authorization: Bearer $TOKEN"

# Should return results for Science students
```

### Test 5: Get Fee Summary

```bash
# Get fee summary for Science department
curl -X GET http://localhost:8000/api/v1/departments/1/fees/summary \
  -H "Authorization: Bearer $TOKEN"

# Should show total fees, paid, pending
```

---

## Step 4: Automated API Testing (20 minutes)

```bash
# Run comprehensive API test suite
bash tests/api-test-realworld.sh

# Tests:
# - Authentication (5 users)
# - Department access control
# - Student management
# - Attendance tracking
# - Results management
# - Fee management
```

**Expected Output:**
```
========================================
TEST SUMMARY
========================================
Total Tests: 15
Passed: 15
Failed: 0
Success Rate: 100%
========================================
```

---

## Step 5: Test Workflows (60 minutes)

### Workflow 1: Student Admission

**Scenario**: New student applies → Registrar reviews → HOD approves → Principal approves

```bash
# Check workflow history for student 1
curl -X GET http://localhost:8000/api/v1/workflows/student/1/history \
  -H "Authorization: Bearer $TOKEN"

# Should show 4 transitions:
# 1. pending → registrar_review
# 2. registrar_review → hod_approved
# 3. hod_approved → principal_approved
```

### Workflow 2: Fee Waiver (Conditional Logic)

**Scenario A**: Student requests ₹3,000 waiver (≤₹5000 - skips HOD)

```bash
# Create fee waiver request
curl -X POST http://localhost:8000/api/v1/fees/waivers \
  -H "Authorization: Bearer $STUDENT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": 1,
    "amount": 3000,
    "reason": "Financial hardship"
  }'

# Registrar approves → Goes directly to Principal (skips HOD)
```

**Scenario B**: Student requests ₹8,000 waiver (>₹5000 - requires HOD)

```bash
# Create fee waiver request
curl -X POST http://localhost:8000/api/v1/fees/waivers \
  -H "Authorization: Bearer $STUDENT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": 2,
    "amount": 8000,
    "reason": "Medical emergency"
  }'

# Registrar reviews → HOD approves → Principal approves
```

### Workflow 3: Lesson Plan Approval

**Scenario**: Faculty creates lesson plan → HOD reviews → Principal approves

```bash
# Check lesson plan workflow
curl -X GET http://localhost:8000/api/v1/workflows/lesson-plan/1/history \
  -H "Authorization: Bearer $TOKEN"

# Should show:
# 1. draft → submitted
# 2. submitted → hod_approved
# 3. hod_approved → principal_approved
```

### Workflow 4: Department Transfer

**Scenario**: Student transfers from Science to Commerce

```bash
# Request department transfer
curl -X POST http://localhost:8000/api/v1/workflows/department-transfer \
  -H "Authorization: Bearer $STUDENT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": 1,
    "from_department_id": 1,
    "to_department_id": 2,
    "reason": "Change of interest"
  }'

# Workflow: Source HOD → Target HOD → Principal
```

---

## Step 6: Test Department Isolation (30 minutes)

### Test 1: Faculty Can Only Access Own Department

```bash
# Login as Science faculty
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"faculty1@pvgs.edu","password":"password123"}'

FACULTY_TOKEN="token_here"

# Try to access Science department (should work)
curl -X GET http://localhost:8000/api/v1/departments/1/students \
  -H "Authorization: Bearer $FACULTY_TOKEN"
# ✅ Should return students

# Try to access Commerce department (should fail)
curl -X GET http://localhost:8000/api/v1/departments/2/students \
  -H "Authorization: Bearer $FACULTY_TOKEN"
# ❌ Should return "Access denied"
```

### Test 2: Principal Can Access All Departments

```bash
# Login as Principal
PRINCIPAL_TOKEN="token_here"

# Access Science
curl -X GET http://localhost:8000/api/v1/departments/1/students \
  -H "Authorization: Bearer $PRINCIPAL_TOKEN"
# ✅ Should work

# Access Commerce
curl -X GET http://localhost:8000/api/v1/departments/2/students \
  -H "Authorization: Bearer $PRINCIPAL_TOKEN"
# ✅ Should work

# Access Arts
curl -X GET http://localhost:8000/api/v1/departments/3/students \
  -H "Authorization: Bearer $PRINCIPAL_TOKEN"
# ✅ Should work
```

### Test 3: Student Cannot Access Admin Endpoints

```bash
# Login as Student
STUDENT_TOKEN="token_here"

# Try to access student list (should fail)
curl -X GET http://localhost:8000/api/v1/departments/1/students \
  -H "Authorization: Bearer $STUDENT_TOKEN"
# ❌ Should return "Unauthorized"
```

---

## Step 7: Performance Testing (30 minutes)

### Test Response Times

```bash
# Test 1: Student list (should be < 500ms)
time curl -X GET http://localhost:8000/api/v1/departments/1/students \
  -H "Authorization: Bearer $TOKEN"

# Test 2: Attendance report (should be < 500ms)
time curl -X GET http://localhost:8000/api/v1/departments/1/attendance/report \
  -H "Authorization: Bearer $TOKEN"

# Test 3: Fee summary (should be < 500ms)
time curl -X GET http://localhost:8000/api/v1/departments/1/fees/summary \
  -H "Authorization: Bearer $TOKEN"
```

### Load Testing (Optional)

```bash
# Install Apache Bench
sudo apt-get install apache2-utils

# Test with 100 concurrent requests
ab -n 100 -c 10 -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/v1/departments/1/students
```

---

## Step 8: Verify Data Integrity (20 minutes)

### Check Database

```bash
php artisan tinker

# Check students
>>> DB::table('students')->count()
// Should be 100

# Check attendance
>>> DB::table('attendance_records')->count()
// Should be 3000 (100 students × 30 days)

# Check results
>>> DB::table('exam_results')->count()
// Should be 300 (100 students × 3 subjects)

# Check workflow history
>>> DB::table('workflow_history')->count()
// Should be 120+ (all workflow transitions)

# Check fee payments
>>> DB::table('fee_payments')->count()
// Should be 70 (70% students paid)
```

---

## Expected Results

### ✅ Success Criteria

- [ ] All 5 users can login
- [ ] Department isolation works (faculty can't access other departments)
- [ ] Principal can access all departments
- [ ] Students can't access admin endpoints
- [ ] All API endpoints return data
- [ ] Response times < 500ms
- [ ] Workflow history shows all transitions
- [ ] Attendance percentage ~85%
- [ ] 70% students have paid fees
- [ ] All data has department context

### ❌ Known Issues to Fix

If you encounter these, note them for fixing:

1. **Slow queries** - Add database indexes
2. **Missing data** - Check seeder logic
3. **Permission errors** - Verify middleware
4. **Workflow failures** - Check workflow config

---

## Troubleshooting

### Issue: "Token expired"
```bash
# Re-login to get new token
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"principal@pvgs.edu","password":"password123"}'
```

### Issue: "Department not found"
```bash
# Check departments exist
php artisan tinker
>>> DB::table('departments')->get()
```

### Issue: "No data returned"
```bash
# Re-run seeder
php artisan test:reset
```

---

## Next Steps (Day 3-5)

After successful API testing:

**Day 3**: Build React main layout + navigation  
**Day 4**: Build Principal dashboard  
**Day 5**: Build Registrar & Faculty dashboards

---

**Status**: Ready to execute  
**Estimated Time**: 4 hours  
**Prerequisites**: Laravel running, database configured
