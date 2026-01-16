# PVGS ERP - User Workflows

## Real-World User Scenarios

---

## 1. Principal's Daily Workflow

### Morning Routine (9:00 AM)
**User**: Dr. Principal (principal@pvgs.edu)

```bash
# 1. Login
POST /api/login
{
  "email": "principal@pvgs.edu",
  "password": "password123"
}
# Receives JWT token valid for 15 minutes

# 2. View Dashboard Summary
GET /api/departments
# Returns: Science, Commerce, Arts departments

# 3. Check Today's Statistics
GET /api/students?department_id=10  # Science
GET /api/students?department_id=11  # Commerce
GET /api/students?department_id=12  # Arts
# Total: 30 students across 3 departments

# 4. Review Attendance
GET /api/attendance?date_from=2026-01-16&date_to=2026-01-16
# Today's attendance across all departments
```

**Time**: 5 minutes  
**Actions**: 6 API calls  
**Result**: Complete overview of college operations

---

## 2. Faculty Attendance Marking

### Class Attendance (10:00 AM)
**User**: Faculty Science 1 (faculty1@pvgs.edu)

```bash
# 1. Login
POST /api/login
{
  "email": "faculty1@pvgs.edu",
  "password": "password123"
}

# 2. Get Today's Class Students
GET /api/students?department_id=10
# Returns: 10 Science students

# 3. Mark Attendance (Bulk)
POST /api/attendance
{
  "student_id": 1,
  "subject_id": 1,
  "date": "2026-01-16",
  "status": "present"
}
# Repeat for each student

# 4. Verify Attendance Saved
GET /api/attendance?date_from=2026-01-16
# Confirms all records saved
```

**Time**: 3 minutes per class  
**Actions**: 12+ API calls  
**Result**: Attendance recorded for 10 students

---

## 3. Student Checking Results

### View Academic Performance (2:00 PM)
**User**: Student 1 (student1@pvgs.edu)

```bash
# 1. Login
POST /api/login
{
  "email": "student1@pvgs.edu",
  "password": "password123"
}

# 2. View Profile
GET /api/user
# Returns: Name, email, program, department

# 3. Check Attendance
GET /api/attendance?student_id=1
# Returns: Attendance history

# 4. View Results
GET /api/students/1
# Returns: Complete profile with results
```

**Time**: 2 minutes  
**Actions**: 4 API calls  
**Result**: Complete academic overview

---

## 4. HOD Department Management

### Weekly Review (Friday 4:00 PM)
**User**: HOD Science (hod.science@pvgs.edu)

```bash
# 1. Login
POST /api/login
{
  "email": "hod.science@pvgs.edu",
  "password": "password123"
}

# 2. Get Department Students
GET /api/students?department_id=10
# Returns: 10 Science students

# 3. Weekly Attendance Report
GET /api/attendance?department_id=10&date_from=2026-01-13&date_to=2026-01-17
# 5-day attendance summary

# 4. Review Performance
GET /api/students?department_id=10
# Check student progress
```

**Time**: 10 minutes  
**Actions**: 4 API calls  
**Result**: Complete department overview

---

## 5. Registrar Student Admission

### New Student Enrollment (11:00 AM)
**User**: Registrar (registrar@pvgs.edu)

```bash
# 1. Login
POST /api/login
{
  "email": "registrar@pvgs.edu",
  "password": "password123"
}

# 2. Create Student Account
POST /api/students
{
  "name": "New Student",
  "email": "new@pvgs.edu",
  "program_id": 4,
  "department_id": 10,
  "category_id": 1
}

# 3. Verify Creation
GET /api/students?department_id=10
# Confirms new student in list

# 4. Assign Fees
POST /api/fees
{
  "student_id": 31,
  "fee_structure_id": 1
}
```

**Time**: 5 minutes  
**Actions**: 4 API calls  
**Result**: New student enrolled and fees assigned

---

## Common Workflows

### Password Reset
```bash
# Admin resets user password
sqlite3 database/database.sqlite "UPDATE users SET password = '\$2y\$10\$...' WHERE email = 'user@pvgs.edu';"
```

### Department Switch
```bash
# User switches department context
GET /api/departments
# Select new department_id
GET /api/students?department_id=11  # Commerce
```

### Bulk Operations
```bash
# Mark attendance for entire class
for student_id in {1..10}; do
  curl -X POST /api/attendance \
    -H "Authorization: Bearer $TOKEN" \
    -d "{\"student_id\":$student_id,\"status\":\"present\"}"
done
```

---

## Error Handling

### Invalid Token
```json
{
  "success": false,
  "message": "Invalid or expired token",
  "meta": {"timestamp": "2026-01-16T08:00:00Z"}
}
```
**Action**: Re-login to get new token

### Rate Limit Exceeded
```json
{
  "success": false,
  "message": "Rate limit exceeded",
  "meta": {"timestamp": "2026-01-16T08:00:00Z"}
}
```
**Action**: Wait 60 seconds before retry

### Unauthorized Access
```json
{
  "success": false,
  "message": "Access denied",
  "meta": {"timestamp": "2026-01-16T08:00:00Z"}
}
```
**Action**: Check user permissions

---

## Performance Expectations

| Operation | Expected Time | Max Time |
|-----------|--------------|----------|
| Login | < 100ms | 200ms |
| Get Students | < 150ms | 300ms |
| Mark Attendance | < 100ms | 200ms |
| Get Departments | < 50ms | 100ms |
| Health Check | < 10ms | 50ms |

---

## Daily Usage Statistics

**Peak Hours**: 10:00 AM - 12:00 PM (Attendance marking)  
**Average Requests**: ~500/hour  
**Concurrent Users**: 15-20  
**Total Daily Requests**: ~3,000

---

**Last Updated**: 2026-01-16  
**Version**: 1.0.0
