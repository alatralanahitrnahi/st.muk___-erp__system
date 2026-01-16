# DAY 1 EXECUTION GUIDE - BACKEND VALIDATION

**Execution Time**: 4 Hours  
**Status**: Ready to Execute  
**Priority**: CRITICAL

---

## QUICK START (Copy & Paste)

```bash
# Navigate to project
cd /workspaces/st.muk___-erp__system

# Execute Day 1 in sequence
bash scripts/day1-setup.sh && \
bash scripts/day1-migrate.sh && \
bash scripts/day1-test.sh

# Review results
cat tests/results/day1-validation-*.txt
```

---

## STEP-BY-STEP EXECUTION

### Step 1: Environment Setup (30 minutes)

```bash
cd /workspaces/st.muk___-erp__system
bash scripts/day1-setup.sh
```

**What it does:**
- Installs Composer dependencies
- Creates .env file
- Generates application key
- Configures SQLite database
- Creates necessary directories

**Success indicators:**
```
✅ Setup Complete!
Next: Run 'bash scripts/day1-migrate.sh'
```

**If it fails:**
```bash
# Check logs
cat logs/composer-install.log

# Manual fix
composer install --no-interaction
cp .env.example .env
php artisan key:generate
```

---

### Step 2: Database Migration (45 minutes)

```bash
bash scripts/day1-migrate.sh
```

**What it does:**
- Runs all 70+ database migrations
- Seeds test data (users, departments, roles)
- Verifies database structure

**Success indicators:**
```
✅ Created 70+ tables
Users: 5
Departments: 3
Roles: 5
✅ Database Ready!
```

**If it fails:**
```bash
# Check migration logs
cat logs/migrate-fresh.log

# Manual fix
php artisan migrate:fresh --force
php artisan db:seed --force
```

---

### Step 3: Test Execution (2 hours)

```bash
bash scripts/day1-test.sh
```

**What it does:**
- Starts API server on port 8000
- Runs 27 real-world validation tests
- Runs PHPUnit test suite
- Generates test report

**Success indicators:**
```
✅ API server running on http://localhost:8000
✅ Real-World Tests: PASSED
✅ PHPUnit Tests: PASSED
✅ All tests passed - Proceed to Day 2
```

**If tests fail:**
```bash
# Check API logs
tail -f logs/api-server.log

# Test individual endpoint
curl http://localhost:8000/api/v1/health

# Re-run specific test
php artisan test --filter=StudentTest
```

---

## VALIDATION CHECKLIST

After Day 1 execution, verify:

### Database ✅
- [ ] 70+ tables created
- [ ] 5 test users seeded (admin, principal, registrar, faculty, student)
- [ ] 3 departments created (Science, Commerce, Arts)
- [ ] 5 roles configured (Super Admin, Principal, Registrar, Faculty, Student)

### API Server ✅
- [ ] Server starts without errors
- [ ] Health endpoint responds: `curl http://localhost:8000/api/v1/health`
- [ ] Authentication works: Login with test users
- [ ] Department isolation verified

### Tests ✅
- [ ] Real-world tests: 27/27 passing
- [ ] PHPUnit tests: All passing
- [ ] Response times < 500ms
- [ ] Zero permission leaks

---

## TEST USERS (For Manual Testing)

| Role | Email | Password | Access |
|------|-------|----------|--------|
| Super Admin | admin@pvgs.edu | password123 | All departments |
| Principal | principal@pvgs.edu | password123 | All departments |
| Registrar | registrar@pvgs.edu | password123 | Assigned department |
| Faculty | faculty1@pvgs.edu | password123 | Assigned department |
| Student | student1@pvgs.edu | password123 | Own data only |

---

## MANUAL VERIFICATION

After automated tests pass, manually verify:

### 1. Authentication
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"principal@pvgs.edu","password":"password123"}'
```

**Expected**: Returns token

### 2. Department Access
```bash
# Save token from above
TOKEN="your_token_here"

curl -X GET http://localhost:8000/api/v1/departments \
  -H "Authorization: Bearer $TOKEN"
```

**Expected**: Returns list of departments

### 3. Student List
```bash
curl -X GET http://localhost:8000/api/v1/departments/1/students \
  -H "Authorization: Bearer $TOKEN"
```

**Expected**: Returns students from department 1

---

## TROUBLESHOOTING

### Issue: Composer install fails
```bash
# Clear composer cache
composer clear-cache
composer install --no-interaction --prefer-dist
```

### Issue: Migration fails
```bash
# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Reset database
rm database/database.sqlite
touch database/database.sqlite
php artisan migrate:fresh
```

### Issue: API server won't start
```bash
# Check if port 8000 is in use
lsof -i :8000

# Kill existing process
kill -9 $(lsof -t -i:8000)

# Start server
php artisan serve --port=8000
```

### Issue: Tests fail
```bash
# Run tests with verbose output
php artisan test --verbose

# Check specific test
php artisan test --filter=AuthenticationTest

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

---

## SUCCESS CRITERIA

Day 1 is complete when:

✅ **Environment**: Laravel installed, .env configured, database created  
✅ **Migrations**: All 70+ tables created successfully  
✅ **Seeders**: Test data populated (5 users, 3 departments, 5 roles)  
✅ **API Server**: Running on port 8000, responding to requests  
✅ **Tests**: 27/27 real-world tests passing  
✅ **Performance**: API responses < 500ms  
✅ **Security**: Department isolation verified, no permission leaks

---

## NEXT STEPS

After Day 1 completion:

1. **Review test results**: `cat tests/results/day1-validation-*.txt`
2. **Document any issues**: Create GitHub issues for failures
3. **Proceed to Day 2**: Frontend implementation
4. **Daily standup**: Report completion to team

---

## ROLLBACK PROCEDURE

If Day 1 fails critically:

```bash
# Stop API server
pkill -f "php artisan serve"

# Reset database
rm database/database.sqlite

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Start fresh
bash scripts/day1-setup.sh
```

---

## TIME ESTIMATES

| Task | Estimated | Actual |
|------|-----------|--------|
| Environment Setup | 30 min | ___ |
| Database Migration | 45 min | ___ |
| Test Execution | 2 hours | ___ |
| Manual Verification | 45 min | ___ |
| **Total** | **4 hours** | ___ |

---

## CONTACT FOR ISSUES

**Technical Issues**: Backend Lead  
**Database Issues**: Database Admin  
**Test Failures**: QA Lead  
**Blockers**: Project Manager

---

**Document Version**: 1.0  
**Last Updated**: 2024-01-16  
**Status**: Ready for Execution
