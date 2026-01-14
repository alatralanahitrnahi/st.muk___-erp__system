# Real-World Validation - Quick Execution Checklist

## Pre-Flight Check (5 minutes)

```bash
# 1. Verify Laravel is running
curl http://localhost:8000/api/v1/health || echo "❌ API not running"

# 2. Check database connection
php artisan tinker --execute="DB::connection()->getPdo(); echo '✅ Database connected';"

# 3. Verify super admin exists
php artisan tinker --execute="User::where('email','admin@pvgs.edu')->exists() ? print('✅ Admin exists') : print('❌ Create admin');"

# 4. Check departments seeded
php artisan tinker --execute="DB::table('departments')->count() >= 3 ? print('✅ Departments ready') : print('❌ Seed departments');"
```

## Execute Validation (30 minutes)

```bash
# Run full validation suite
bash tests/real-world-validation.sh

# Or run with custom API base
API_BASE=http://localhost:8000/api/v1 bash tests/real-world-validation.sh
```

## Post-Execution Review (10 minutes)

```bash
# 1. Check results file
cat tests/results/real-world-validation-*.txt | tail -20

# 2. Review API logs for errors
tail -50 storage/logs/laravel.log | grep ERROR

# 3. Verify test users created
php artisan tinker --execute="User::whereIn('email',['principal@test.edu','faculty@test.edu'])->count();"
```

## Quick Status Check

### ✅ All Tests Pass (27/27)
→ **Action**: Proceed with UI improvements  
→ **Next**: Document successful workflows  
→ **Deploy**: Ready for staging environment

### ⚠️ Some Tests Fail (20-26/27)
→ **Action**: Review failed tests  
→ **Next**: Fix non-critical issues  
→ **Deploy**: Can proceed with caution

### ❌ Many Tests Fail (<20/27)
→ **Action**: Stop and investigate  
→ **Next**: Fix critical blockers  
→ **Deploy**: Not ready for staging

## Critical Success Factors

- [ ] Principal can configure permissions
- [ ] Admission → Student ID works
- [ ] Faculty can mark attendance
- [ ] Students can view their data
- [ ] No cross-department data leaks
- [ ] All responses < 500ms
- [ ] NAAC audit trails complete

## Common Issues & Fixes

### Issue: "Super admin login failed"
```bash
php artisan tinker
>>> User::create(['name'=>'Admin','email'=>'admin@pvgs.edu','password'=>bcrypt('admin123'),'user_type'=>'super_admin']);
```

### Issue: "Department not found"
```bash
php artisan db:seed --class=DepartmentSeeder
```

### Issue: "Token expired"
```bash
# Increase token lifetime in config/sanctum.php
'expiration' => 1440, // 24 hours
```

### Issue: "Permission denied"
```bash
# Check user department assignments
php artisan tinker
>>> User::with('departments')->where('email','faculty@test.edu')->first();
```

## Manual Verification (Optional)

After automated tests pass, verify in browser:

1. **Principal Dashboard**: http://localhost:8000/secure-principal-dashboard.html
   - Login: principal@test.edu / password123
   - ✓ Department selector works
   - ✓ Can configure modules

2. **Faculty Dashboard**: http://localhost:8000/secure-faculty-dashboard.html
   - Login: faculty@test.edu / password123
   - ✓ Can mark attendance
   - ✓ Can submit lesson plans

3. **Student Dashboard**: http://localhost:8000/secure-student-dashboard.html
   - Login: student@test.edu / password123
   - ✓ Can view attendance
   - ✓ Cannot access admin functions

## Results Location

- **Test Output**: `tests/results/real-world-validation-YYYYMMDD-HHMMSS.txt`
- **API Logs**: `storage/logs/laravel.log`
- **Report Template**: `tests/REAL_WORLD_VALIDATION_REPORT.md`

## Time Estimates

| Phase | Duration | Description |
|-------|----------|-------------|
| Pre-flight | 5 min | Environment checks |
| Execution | 30 min | Run all tests |
| Review | 10 min | Analyze results |
| **Total** | **45 min** | Complete validation |

## Success Metrics

| Metric | Target | Critical |
|--------|--------|----------|
| Pass Rate | 100% | > 90% |
| Response Time | < 500ms | < 1000ms |
| Security | 0 leaks | 0 leaks |
| NAAC Compliance | 100% | 100% |

---

**Quick Start**: `bash tests/real-world-validation.sh`  
**Full Guide**: `tests/REAL_WORLD_VALIDATION_GUIDE.md`  
**Report Template**: `tests/REAL_WORLD_VALIDATION_REPORT.md`
