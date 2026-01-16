# Quick Start - Manual Testing Guide

## ✅ What's Ready

- **Database**: Seeded with 51 users, 3 departments, 3 programs, 30 students
- **Test Credentials**: All users created with password `password123`
- **Seed Script**: `seed.php` - Works without Laravel (uses direct PDO)

## 🚀 Start Testing

### Option 1: Test with Laravel Server (Recommended)

```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Test API
bash test_api.sh
```

### Option 2: Test with Direct Database Queries

```bash
# Verify data
php -r "
\$pdo = new PDO('sqlite:database/database.sqlite');
\$users = \$pdo->query('SELECT email, user_type, role FROM users LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);
print_r(\$users);
"
```

### Option 3: Test with curl (Manual)

```bash
# 1. Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@pvgs.edu","password":"password123"}'

# 2. Get user info (replace TOKEN)
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"

# 3. Get departments
curl -X GET http://localhost:8000/api/departments \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"

# 4. Get students
curl -X GET http://localhost:8000/api/students \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 📊 Test Accounts

| Email | Password | Type | Role | Use Case |
|-------|----------|------|------|----------|
| admin@pvgs.edu | password123 | admin | super-admin | Full system access |
| principal@pvgs.edu | password123 | admin | principal | College-wide access |
| hod.science@pvgs.edu | password123 | staff | registrar | Science dept HOD |
| hod.commerce@pvgs.edu | password123 | staff | registrar | Commerce dept HOD |
| hod.arts@pvgs.edu | password123 | staff | registrar | Arts dept HOD |
| registrar@pvgs.edu | password123 | staff | registrar | Registrar office |
| faculty1@pvgs.edu | password123 | faculty | faculty | Science faculty |
| faculty6@pvgs.edu | password123 | faculty | faculty | Commerce faculty |
| faculty11@pvgs.edu | password123 | faculty | faculty | Arts faculty |
| student1@pvgs.edu | password123 | student | student | Science student |
| student11@pvgs.edu | password123 | student | student | Commerce student |
| student21@pvgs.edu | password123 | student | student | Arts student |

## 🔧 Reseed Database

If you need fresh data:

```bash
php seed.php
```

This will:
- Clear all existing users, departments, programs, students
- Create fresh test data
- Takes ~1 second
- No Laravel bootstrap required

## 📝 What to Test

### 1. Authentication
- [ ] Login with admin
- [ ] Login with principal
- [ ] Login with HOD
- [ ] Login with faculty
- [ ] Login with student
- [ ] Verify token generation
- [ ] Test invalid credentials

### 2. Department Access
- [ ] Admin can see all departments
- [ ] HOD can see their department
- [ ] Faculty can see their department
- [ ] Student can see their department

### 3. Student Management
- [ ] List all students (admin)
- [ ] List department students (HOD)
- [ ] View student profile
- [ ] Update student info

### 4. Programs & Departments
- [ ] List all departments
- [ ] List programs by department
- [ ] View program details

## ⚠️ Known Issues

1. **Laravel Artisan Commands**: Still failing due to missing cache config
   - **Workaround**: Use `seed.php` instead of `php artisan db:seed`

2. **API Versioning**: Routes at `/api/*` instead of `/api/v1/*`
   - **Impact**: Future compatibility issues
   - **Fix**: Planned for Week 2

3. **Department Context**: API endpoints don't filter by department
   - **Impact**: HODs see all students, not just their department
   - **Fix**: Planned for Week 2

4. **Response Format**: Inconsistent across controllers
   - **Impact**: Frontend needs to handle multiple formats
   - **Fix**: Planned for Week 2

## 📚 Documentation

- `TESTING_SUMMARY.md` - Detailed issues and solutions
- `possible problems.md` - External code review findings
- `test_api.sh` - Automated API testing script
- `seed.php` - Database seeding script

## 🎯 Next Steps

1. **Today**: Test all API endpoints manually
2. **Tomorrow**: Start React dashboard development
3. **Week 2**: Fix API versioning and department context
4. **Week 3**: Implement caching and performance optimizations

---

**Status**: ✅ Ready for manual testing
**Last Updated**: Day 2 - Backend Testing Phase
