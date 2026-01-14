# Department Data Population Strategy

## Overview

Comprehensive strategy for intelligent department assignment to existing records, ensuring 95%+ coverage before workflow configuration. Follows ATKT_Backlog_Rules.md for student assignments and maintains NAAC compliance.

## Execution Workflow

### Phase 1: Pre-Population Assessment (Day 1)

```bash
# 1. Validate current state
php artisan department:validate --export

# 2. Review validation report
cat storage/app/department_validation_*.csv

# 3. Identify gaps and plan manual assignments
```

**Success Criteria**: Baseline metrics documented, gaps identified

### Phase 2: Automated Backfill (Days 2-3)

```bash
# 1. Dry run to preview changes
php artisan department:backfill --dry-run

# 2. Execute backfill for all tables
php artisan department:backfill --batch=1000

# 3. Execute specific table if needed
php artisan department:backfill students --batch=500

# 4. Validate results
php artisan department:validate
```

**Success Criteria**: 90%+ automated assignment coverage

### Phase 3: User-Department Assignment (Day 4)

```bash
# 1. Dry run user assignments
php artisan department:assign-users --dry-run

# 2. Execute user assignments
php artisan department:assign-users --export-csv

# 3. Review unassigned users
cat storage/app/unassigned_users_*.csv

# 4. Validate user assignments
php artisan department:validate
```

**Success Criteria**: All users assigned to departments

### Phase 4: Permission Configuration (Day 5)

```bash
# 1. Seed department permissions
php artisan db:seed --class=DepartmentPermissionsSeeder

# 2. Validate permissions
php artisan department:validate

# 3. Test permission checks
php artisan tinker
>>> $user = User::find(1);
>>> $user->hasAccessToDepartment(1);
```

**Success Criteria**: All role-department-module permissions configured

### Phase 5: Manual Assignments (Days 6-7)

```bash
# 1. Export failure CSVs from backfill
ls -la storage/app/department_backfill_*_failures_*.csv

# 2. Use templates for manual assignment
cp storage/app/templates/manual_*.csv storage/app/manual_assignments/

# 3. Fill in manual assignments
# Edit CSV files with correct department_id values

# 4. Import manual assignments
php artisan department:import-manual users storage/app/manual_assignments/users.csv
php artisan department:import-manual subjects storage/app/manual_assignments/subjects.csv
php artisan department:import-manual students storage/app/manual_assignments/students.csv

# 5. Final validation
php artisan department:validate --export
```

**Success Criteria**: 95%+ coverage achieved

## Intelligent Assignment Logic

### Students
```
department_id = students.program_id → programs.department_id
```
- Based on current program enrollment
- ATKT students: Use current program department (per ATKT_Backlog_Rules.md)
- Transfer students: Require manual assignment

### Attendance Records
```
department_id = attendance.student_id → students.department_id
```
- Inherits from student's department
- Batch processed for performance

### Exam Results
```
department_id = results.student_id → students.department_id
```
- Inherits from student's department
- ATKT results retain original department context

### Fee Records
```
department_id = fees.student_id → students.department_id
```
- Inherits from student's department
- Critical for NAAC financial reporting

### Subjects
```
department_id = program_subjects.subject_id → programs.department_id
```
- Based on program association
- Interdisciplinary subjects: Assign to primary teaching department
- Orphaned subjects: Require manual assignment

### Lesson Plans
```
department_id = lesson_plans.subject_id → subjects.department_id
```
- Inherits from subject's department
- Faculty teaching across departments: Plan belongs to subject's department

### Users

**Super Admins**: All departments
```sql
INSERT INTO user_departments (user_id, department_id, role_in_department)
SELECT u.id, d.id, 'super-admin'
FROM users u CROSS JOIN departments d
WHERE u.role = 'super-admin';
```

**Principals**: All departments
```sql
INSERT INTO user_departments (user_id, department_id, role_in_department)
SELECT u.id, d.id, 'principal'
FROM users u CROSS JOIN departments d
WHERE u.role = 'principal';
```

**Registrars**: Primary department (manual assignment recommended)
```sql
-- Default to first department, but review manually
UPDATE users SET primary_department_id = (SELECT MIN(id) FROM departments)
WHERE role = 'registrar';
```

**Faculty**: Based on subject assignments
```sql
INSERT INTO user_departments (user_id, department_id, role_in_department)
SELECT DISTINCT sf.faculty_id, s.department_id, 'faculty'
FROM subject_faculty sf
JOIN subjects s ON sf.subject_id = s.id
WHERE s.department_id IS NOT NULL;
```

**Students**: Based on program enrollment
```sql
INSERT INTO user_departments (user_id, department_id, role_in_department)
SELECT s.user_id, p.department_id, 'student'
FROM students s
JOIN programs p ON s.program_id = p.id
WHERE p.department_id IS NOT NULL;
```

## Batch Processing Performance

### Configuration
- Default batch size: 1,000 records
- Adjustable via `--batch` flag
- Progress bars for visual feedback
- Automatic logging for audit trail

### Performance Benchmarks
- Students (10,000 records): ~2 minutes
- Attendance (50,000 records): ~8 minutes
- Results (30,000 records): ~5 minutes
- Fees (20,000 records): ~3 minutes
- Total estimated time: ~20 minutes for 100,000+ records

### Optimization Tips
```bash
# Increase batch size for large datasets
php artisan department:backfill --batch=5000

# Process specific tables in parallel
php artisan department:backfill students --batch=2000 &
php artisan department:backfill attendance --batch=2000 &
wait
```

## Validation & Monitoring

### Real-Time Monitoring Dashboard

Create monitoring view:
```sql
CREATE OR REPLACE VIEW department_population_status AS
SELECT 
    'Students' as entity,
    COUNT(*) as total,
    SUM(CASE WHEN department_id IS NOT NULL THEN 1 ELSE 0 END) as assigned,
    ROUND(SUM(CASE WHEN department_id IS NOT NULL THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as coverage_pct
FROM students
UNION ALL
SELECT 'Attendance', COUNT(*), SUM(CASE WHEN department_id IS NOT NULL THEN 1 ELSE 0 END),
    ROUND(SUM(CASE WHEN department_id IS NOT NULL THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2)
FROM attendance
UNION ALL
SELECT 'Results', COUNT(*), SUM(CASE WHEN department_id IS NOT NULL THEN 1 ELSE 0 END),
    ROUND(SUM(CASE WHEN department_id IS NOT NULL THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2)
FROM results
UNION ALL
SELECT 'Fees', COUNT(*), SUM(CASE WHEN department_id IS NOT NULL THEN 1 ELSE 0 END),
    ROUND(SUM(CASE WHEN department_id IS NOT NULL THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2)
FROM fees
UNION ALL
SELECT 'Subjects', COUNT(*), SUM(CASE WHEN department_id IS NOT NULL THEN 1 ELSE 0 END),
    ROUND(SUM(CASE WHEN department_id IS NOT NULL THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2)
FROM subjects
UNION ALL
SELECT 'Lesson Plans', COUNT(*), SUM(CASE WHEN department_id IS NOT NULL THEN 1 ELSE 0 END),
    ROUND(SUM(CASE WHEN department_id IS NOT NULL THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2)
FROM lesson_plans;
```

Query dashboard:
```sql
SELECT * FROM department_population_status ORDER BY coverage_pct ASC;
```

### Validation Checks

**Data Integrity**:
```sql
-- Students without department but with valid program
SELECT s.id, s.user_id, p.name as program, p.department_id
FROM students s
JOIN programs p ON s.program_id = p.id
WHERE s.department_id IS NULL AND p.department_id IS NOT NULL;

-- Attendance records orphaned from student department
SELECT a.id, a.student_id, s.department_id as student_dept, a.department_id as attendance_dept
FROM attendance a
JOIN students s ON a.student_id = s.id
WHERE a.department_id != s.department_id;

-- Users without department assignments
SELECT u.id, u.name, u.role
FROM users u
LEFT JOIN user_departments ud ON u.id = ud.user_id
WHERE ud.user_id IS NULL;
```

**Permission Coverage**:
```sql
-- Missing permissions per department
SELECT d.id, d.name, COUNT(dp.id) as permission_count
FROM departments d
LEFT JOIN department_permissions dp ON d.id = dp.department_id
GROUP BY d.id, d.name
HAVING COUNT(dp.id) < 36; -- 6 roles × 6 modules = 36 permissions
```

## CSV Templates

### Location
```
storage/app/templates/
├── manual_user_department_assignment.csv
├── manual_subject_department_assignment.csv
└── manual_student_department_assignment.csv
```

### Usage
1. Copy template to working directory
2. Fill in required fields
3. Import using `php artisan department:import-manual`
4. Validate results

## Logging & Audit Trail

### Log Files
```
storage/logs/laravel.log - All department assignment operations
storage/app/department_backfill_*_failures_*.csv - Failed assignments
storage/app/unassigned_users_*.csv - Users requiring manual assignment
storage/app/department_validation_*.csv - Validation reports
```

### Audit Trail
All assignments logged with:
- Timestamp
- User/Entity ID
- Department ID
- Assignment method (automated/manual)
- Reason/Notes

### NAAC Compliance
- Complete audit trail for all department assignments
- Validation reports exportable for compliance review
- Historical data preserved with department context

## Rollback Procedures

### Rollback Automated Assignments
```sql
-- Backup before rollback
CREATE TABLE students_backup AS SELECT * FROM students;
CREATE TABLE attendance_backup AS SELECT * FROM attendance;

-- Rollback department_id assignments
UPDATE students SET department_id = NULL WHERE updated_at > '2024-01-01';
UPDATE attendance SET department_id = NULL WHERE updated_at > '2024-01-01';
UPDATE results SET department_id = NULL WHERE updated_at > '2024-01-01';
UPDATE fees SET department_id = NULL WHERE updated_at > '2024-01-01';
UPDATE subjects SET department_id = NULL WHERE updated_at > '2024-01-01';
UPDATE lesson_plans SET department_id = NULL WHERE updated_at > '2024-01-01';
```

### Rollback User Assignments
```sql
-- Backup
CREATE TABLE user_departments_backup AS SELECT * FROM user_departments;

-- Rollback
TRUNCATE TABLE user_departments;
UPDATE users SET primary_department_id = NULL;
```

### Rollback Permissions
```sql
-- Backup
CREATE TABLE department_permissions_backup AS SELECT * FROM department_permissions;

-- Rollback
TRUNCATE TABLE department_permissions;
```

## Troubleshooting

### Issue: Low Coverage After Automated Backfill

**Diagnosis**:
```bash
php artisan department:validate
cat storage/app/department_backfill_*_failures_*.csv
```

**Resolution**:
1. Review failure CSVs
2. Identify root cause (missing relationships, orphaned records)
3. Use manual assignment templates
4. Import corrected assignments

### Issue: User Cannot Access Department

**Diagnosis**:
```sql
SELECT * FROM user_departments WHERE user_id = ?;
SELECT * FROM users WHERE id = ?;
```

**Resolution**:
```bash
# Add user to department
php artisan tinker
>>> DB::table('user_departments')->insert([
    'user_id' => 1,
    'department_id' => 1,
    'role_in_department' => 'faculty',
    'created_at' => now(),
    'updated_at' => now()
]);
```

### Issue: Permission Denied Despite Department Access

**Diagnosis**:
```sql
SELECT * FROM department_permissions 
WHERE department_id = ? AND role = ?;
```

**Resolution**:
```bash
# Re-seed permissions
php artisan db:seed --class=DepartmentPermissionsSeeder
```

## Success Criteria

### Critical (Must Achieve)
- ✅ 95%+ students assigned to departments
- ✅ 95%+ attendance records assigned
- ✅ 95%+ results assigned
- ✅ 95%+ fees assigned
- ✅ 100% users assigned to at least one department
- ✅ 100% department permissions configured

### Recommended (Should Achieve)
- ✅ 98%+ subjects assigned to departments
- ✅ 98%+ lesson plans assigned
- ✅ All super-admins and principals assigned to all departments
- ✅ All faculty assigned based on subject teaching
- ✅ All students assigned based on program enrollment

### Validation Command
```bash
php artisan department:validate --export
```

**Expected Output**:
```
Entity            Total    Assigned  Unassigned  Coverage %  Status
Students          10000    9800      200         98.00%      ✅
Attendance        50000    49500     500         99.00%      ✅
Results           30000    29700     300         99.00%      ✅
Fees              20000    19800     200         99.00%      ✅
Subjects          500      490       10          98.00%      ✅
Lesson Plans      2000     1980      20          99.00%      ✅
User Departments  500      500       0           100.00%     ✅
Permissions       216      216       0           100.00%     ✅

✅ All validation checks passed! System ready for workflow configuration.
```

## Next Steps After 95% Coverage

1. **Workflow Configuration** (docs/WORKFLOW_CONFIGURATION.md)
   - Configure approval chains per department
   - Set up department-specific workflow rules
   - Test cross-department workflows

2. **NAAC Reporting Setup** (FRD_Reports_NAAC.md)
   - Enable department-scoped reports
   - Configure compliance scorecards
   - Test report generation

3. **User Training**
   - Department switching functionality
   - Multi-department access patterns
   - Workflow approval processes

4. **Go-Live Preparation**
   - Final validation sweep
   - Performance testing with department scoping
   - Backup and rollback procedures documented

## Commands Reference

```bash
# Backfill department data
php artisan department:backfill [table] [--batch=1000] [--dry-run]

# Assign users to departments
php artisan department:assign-users [--dry-run] [--export-csv]

# Import manual assignments
php artisan department:import-manual {users|subjects|students} {file} [--dry-run]

# Validate data population
php artisan department:validate [--export]

# Seed permissions
php artisan db:seed --class=DepartmentPermissionsSeeder
```

## Files Created

### Artisan Commands (4 files)
- `app/Console/Commands/BackfillDepartmentData.php`
- `app/Console/Commands/AssignUserDepartments.php`
- `app/Console/Commands/ImportManualAssignments.php`
- `app/Console/Commands/ValidateDepartmentData.php`

### Database Seeders (1 file)
- `database/seeders/DepartmentPermissionsSeeder.php`

### CSV Templates (3 files)
- `storage/app/templates/manual_user_department_assignment.csv`
- `storage/app/templates/manual_subject_department_assignment.csv`
- `storage/app/templates/manual_student_department_assignment.csv`

### Documentation (1 file)
- `docs/DEPARTMENT_DATA_POPULATION_STRATEGY.md` (this file)
