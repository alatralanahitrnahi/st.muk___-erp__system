# Department Foundation Migration - Execution Protocol

## Pre-Migration Checklist

### 1. Environment Verification
```bash
# Check PHP version (requires 8.1+)
php -v

# Check Laravel version (requires 10+)
php artisan --version

# Check database connection
php artisan db:show

# Check disk space (need at least 500MB)
df -h
```

### 2. Backup Verification
```bash
# Ensure backup directory exists
mkdir -p backups/pre-department-migration

# Create manual backup
cp database/database.sqlite backups/pre-department-migration/manual_backup_$(date +%Y%m%d).sqlite

# Verify backup
ls -lh backups/pre-department-migration/
```

### 3. Code Verification
```bash
# Run verification script
./verify-department-foundation-new.sh

# Expected output: All checks pass (0 failures)
```

## Migration Execution

### Step 1: Run Safe Migration Script
```bash
./run-department-migrations.sh
```

**What it does:**
- Creates automatic backup
- Runs 4 migrations in order
- Logs all operations
- Verifies schema changes
- Runs verification tests

**Expected duration:** 2-5 minutes

**Success indicators:**
- ✅ All 4 migrations complete
- ✅ No error messages
- ✅ Verification tests pass
- ✅ Backup created

### Step 2: Verify Database Schema
```bash
# Check new tables exist
php artisan db:table user_departments
php artisan db:table department_permissions
php artisan db:table workflow_history

# Check new columns exist
php artisan db:table students  # Should show department_id
php artisan db:table users     # Should show primary_department_id
```

### Step 3: Backfill Existing Data
```bash
# Run backfill script
sqlite3 database/database.sqlite < backfill-department-data.sql

# Verify backfill
sqlite3 database/database.sqlite "SELECT COUNT(*) FROM students WHERE department_id IS NOT NULL;"
```

### Step 4: Test Backward Compatibility
```bash
# Start server
php artisan serve

# Test existing endpoints (should work unchanged)
curl -H "Authorization: Bearer {token}" http://localhost:8000/api/students

# Expected: Returns students without errors
```

### Step 5: Test New Department Endpoints
```bash
# Test department-scoped endpoint
curl -H "Authorization: Bearer {token}" http://localhost:8000/api/students?department_id=1

# Expected: Returns only department 1 students
```

## Performance Benchmarks

### Query Performance Tests
```sql
-- Test 1: Department-scoped student query (target: < 50ms)
EXPLAIN QUERY PLAN SELECT * FROM students WHERE department_id = 1;

-- Test 2: Workflow history retrieval (target: < 100ms)
EXPLAIN QUERY PLAN SELECT * FROM workflow_history WHERE department_id = 1 ORDER BY performed_at DESC LIMIT 50;

-- Test 3: User departments lookup (target: < 10ms)
EXPLAIN QUERY PLAN SELECT * FROM user_departments WHERE user_id = 1;
```

### Index Verification
```sql
-- Verify indexes created
SELECT name, sql FROM sqlite_master WHERE type='index' AND name LIKE '%department%';

-- Expected indexes:
-- students_department_id_index
-- attendance_records_department_id_attendance_date_index
-- exam_results_department_id_academic_year_index
-- user_departments_user_id_is_primary_index
```

## Rollback Procedures

### Scenario 1: Migration Fails During Execution
```bash
# Automatic rollback triggered by run-department-migrations.sh
# Check log file for details
cat logs/department-migration-*.log

# Restore from backup if needed
cp backups/pre-department-migration/database_*.sql database/database.sqlite
```

### Scenario 2: Post-Migration Issues Detected
```bash
# Run complete rollback
./rollback-department-changes.sh

# Verify rollback
php artisan db:show

# Restore from backup
cp backups/pre-department-migration/database_*.sql database/database.sqlite
```

### Scenario 3: Partial Rollback (Keep Some Changes)
```bash
# Rollback specific migrations
php artisan migrate:rollback --step=1  # Rollback last migration only
php artisan migrate:rollback --step=2  # Rollback last 2 migrations
```

## Data Integrity Verification

### Test 1: Foreign Key Constraints
```sql
-- Verify all foreign keys valid
SELECT COUNT(*) FROM students WHERE department_id IS NOT NULL AND department_id NOT IN (SELECT id FROM departments);
-- Expected: 0

SELECT COUNT(*) FROM user_departments WHERE user_id NOT IN (SELECT id FROM users);
-- Expected: 0
```

### Test 2: Data Consistency
```sql
-- Verify students have department through program
SELECT COUNT(*) FROM students s
JOIN programs p ON s.program_id = p.id
WHERE s.department_id != p.department_id;
-- Expected: 0

-- Verify attendance records match student department
SELECT COUNT(*) FROM attendance_records a
JOIN students s ON a.student_id = s.id
WHERE a.department_id != s.department_id;
-- Expected: 0
```

### Test 3: Null Value Check
```sql
-- Check for unexpected nulls (after backfill)
SELECT 
    'students' as table_name,
    COUNT(*) as total,
    SUM(CASE WHEN department_id IS NULL THEN 1 ELSE 0 END) as null_count
FROM students
UNION ALL
SELECT 'attendance_records', COUNT(*), SUM(CASE WHEN department_id IS NULL THEN 1 ELSE 0 END)
FROM attendance_records;
-- Expected: null_count should be 0 or very low
```

## Automated Test Cases

### Test Suite 1: API Backward Compatibility
```bash
# Test existing endpoints work without changes
php artisan test --filter=DepartmentBackwardCompatibilityTest
```

### Test Suite 2: Department Functionality
```bash
# Test new department features
php artisan test --filter=DepartmentFunctionalityTest
```

### Test Suite 3: Performance Benchmarks
```bash
# Test query performance meets targets
php artisan test --filter=DepartmentPerformanceTest
```

## Monitoring & Logging

### Log Files to Monitor
```bash
# Migration execution log
tail -f logs/department-migration-*.log

# Laravel application log
tail -f storage/logs/laravel.log

# Database query log (if enabled)
tail -f storage/logs/query.log
```

### Key Metrics to Track
- Migration execution time: < 5 minutes
- Database size increase: < 10%
- Query performance: < 50ms for department-scoped queries
- API response time: < 500ms
- Error rate: 0%

## Troubleshooting

### Issue: Migration Fails with Foreign Key Error
**Solution:**
```bash
# Check if departments table has data
sqlite3 database/database.sqlite "SELECT COUNT(*) FROM departments;"

# If empty, seed departments first
php artisan db:seed --class=DepartmentSeeder
```

### Issue: Backfill Script Fails
**Solution:**
```bash
# Check for orphaned records
sqlite3 database/database.sqlite "SELECT COUNT(*) FROM students WHERE program_id NOT IN (SELECT id FROM programs);"

# Clean up orphaned records
sqlite3 database/database.sqlite "DELETE FROM students WHERE program_id NOT IN (SELECT id FROM programs);"
```

### Issue: Performance Degradation
**Solution:**
```bash
# Rebuild indexes
php artisan db:table students --rebuild-indexes

# Analyze query plans
sqlite3 database/database.sqlite "ANALYZE;"
```

## Success Criteria

### ✅ Migration Complete When:
1. All 4 migrations executed successfully
2. Verification script passes (0 failures)
3. Backfill script completes without errors
4. Existing API endpoints work unchanged
5. New department endpoints return correct data
6. Query performance meets benchmarks (< 50ms)
7. No errors in application logs
8. Backup created and verified

### ✅ Ready for Phase 2 When:
1. All success criteria met
2. Data integrity verified
3. Performance benchmarks passed
4. User acceptance testing complete
5. Documentation updated
6. Team trained on new features

## Emergency Contacts

- **Database Issues**: Run `./rollback-department-changes.sh`
- **Performance Issues**: Check indexes with `EXPLAIN QUERY PLAN`
- **Data Issues**: Restore from backup in `backups/pre-department-migration/`
- **Application Issues**: Check `storage/logs/laravel.log`

## Post-Migration Tasks

1. Update API documentation with new endpoints
2. Train users on department selector
3. Configure department permissions via Principal UI
4. Monitor performance for 48 hours
5. Schedule Phase 2 implementation
