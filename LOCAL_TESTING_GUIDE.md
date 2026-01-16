# 🧪 Local Testing Guide - PVGS ERP

**Status**: ✅ Server Running  
**URL**: http://localhost:8000/app

---

## 🚀 Quick Start

### 1. Server is Already Running
```
✅ PHP Server: http://localhost:8000
✅ React App: http://localhost:8000/app
✅ API: http://localhost:8000/api/*
✅ Workflow API: http://localhost:8000/workflow-api.php/*
```

### 2. Open in Browser
```
http://localhost:8000/app
```

### 3. Login with Test Account
```
Email:    admin@pvgs.edu
Password: password123
```

---

## 🧪 Test Scenarios

### Test 1: Super Admin Login
**Steps**:
1. Go to http://localhost:8000/app
2. Enter: admin@pvgs.edu / password123
3. Click Login

**Expected**:
- ✅ Redirects to /super-admin
- ✅ Shows Principal Dashboard
- ✅ Department selector visible
- ✅ Can switch departments

### Test 2: Principal Dashboard
**Steps**:
1. Login as principal@pvgs.edu / password123
2. Select "Science" department
3. View student list

**Expected**:
- ✅ Shows department statistics
- ✅ Lists students from Science dept
- ✅ Shows student names, emails, programs

### Test 3: Faculty Dashboard
**Steps**:
1. Login as faculty1@pvgs.edu / password123
2. Select today's date
3. Click "Present" for a student

**Expected**:
- ✅ Shows Faculty Dashboard
- ✅ Lists students
- ✅ Can mark attendance
- ✅ Shows success message

### Test 4: Student Dashboard
**Steps**:
1. Login as student1@pvgs.edu / password123
2. View profile

**Expected**:
- ✅ Shows Student Dashboard
- ✅ Displays student name
- ✅ Shows email and role
- ✅ Quick links visible

---

## 🔌 API Testing

### Test API Endpoints
```bash
# Test departments
curl http://localhost:8000/api/departments

# Test students
curl http://localhost:8000/api/students?department_id=1

# Test programs
curl http://localhost:8000/api/programs

# Test workflows
curl http://localhost:8000/workflow-api.php/workflows
```

### Test Login API
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@pvgs.edu","password":"password123"}'
```

### Test Workflow Creation
```bash
curl -X POST http://localhost:8000/workflow-api.php/workflows \
  -H "Content-Type: application/json" \
  -d '{
    "workflow_type":"student_admission",
    "entity_type":"student",
    "entity_id":1,
    "department_id":1
  }'
```

---

## 🎯 What to Test

### Frontend Features
- [ ] Login page loads
- [ ] Login with valid credentials works
- [ ] Login with invalid credentials fails
- [ ] Redirects to correct dashboard by role
- [ ] Department selector works
- [ ] Student list loads
- [ ] Attendance marking works
- [ ] Logout works
- [ ] Protected routes work (try accessing /principal without login)

### Backend Features
- [ ] API returns departments
- [ ] API returns students
- [ ] API filters by department_id
- [ ] Workflow creation works
- [ ] Workflow transitions work
- [ ] Audit trail is created

### Performance
- [ ] Page loads in <3 seconds
- [ ] API responds in <500ms
- [ ] No console errors
- [ ] No network errors

---

## 🐛 Troubleshooting

### React App Not Loading
```bash
# Check if files exist
ls -la public/app/

# Should see:
# - index.html
# - assets/ folder
# - .htaccess
```

### API Not Working
```bash
# Test health endpoint
curl http://localhost:8000/health.php

# Check server logs
tail -f /tmp/pvgs-server.log
```

### Database Issues
```bash
# Check if database exists
ls -la database/database.sqlite

# Check tables
php -r "
\$pdo = new PDO('sqlite:database/database.sqlite');
\$tables = \$pdo->query('SELECT name FROM sqlite_master WHERE type=\"table\"')->fetchAll();
print_r(\$tables);
"
```

### Server Not Running
```bash
# Restart server
./start-local.sh

# Or manually
php -S localhost:8000 -t public
```

---

## 📊 Expected Results

### Login Flow
```
1. User enters credentials
2. POST /api/login
3. Receives token + user data
4. Stores in localStorage
5. Redirects to /{role} dashboard
6. Dashboard loads with data
```

### Department Switch Flow
```
1. User selects department
2. Updates Zustand store
3. Re-fetches data with department_id
4. UI updates with new data
```

### Attendance Flow
```
1. Faculty selects date
2. Clicks "Present" button
3. POST /api/attendance
4. Shows success message
5. Updates UI
```

---

## 🎨 UI/UX Checks

### Visual Elements
- [ ] Login form is centered
- [ ] Buttons have hover effects
- [ ] Colors match department theme
- [ ] Text is readable
- [ ] Forms are aligned
- [ ] Tables are formatted

### Responsive Design
- [ ] Works on desktop (1920x1080)
- [ ] Works on laptop (1366x768)
- [ ] Works on tablet (768x1024)
- [ ] Works on mobile (375x667)

### Accessibility
- [ ] Can tab through forms
- [ ] Enter key submits forms
- [ ] Error messages are clear
- [ ] Loading states visible

---

## 📝 Test Accounts

### All Available Accounts
```
Super Admin:
  Email: admin@pvgs.edu
  Password: password123
  Access: All dashboards

Principal:
  Email: principal@pvgs.edu
  Password: password123
  Access: Principal dashboard

Faculty:
  Email: faculty1@pvgs.edu
  Password: password123
  Access: Faculty dashboard

Student:
  Email: student1@pvgs.edu
  Password: password123
  Access: Student dashboard

Registrar:
  Email: registrar@pvgs.edu
  Password: password123
  Access: Admin dashboard
```

---

## 🔍 Browser Console Checks

### Open Developer Tools (F12)

**Console Tab** - Should see:
```
✅ No red errors
✅ API calls successful (200 status)
✅ React app loaded
```

**Network Tab** - Should see:
```
✅ /app/ - 200 OK
✅ /app/assets/index-*.js - 200 OK
✅ /app/assets/index-*.css - 200 OK
✅ /api/login - 200 OK
✅ /api/departments - 200 OK
```

**Application Tab** - Should see:
```
✅ localStorage has 'token'
✅ localStorage has 'pvgs-auth'
```

---

## ✅ Success Criteria

### Must Pass
- ✅ Can login with all 4 roles
- ✅ Each role sees correct dashboard
- ✅ Department selector works
- ✅ Student list loads
- ✅ No console errors
- ✅ API calls succeed

### Nice to Have
- ✅ Fast page loads (<3s)
- ✅ Smooth transitions
- ✅ Good visual design
- ✅ Mobile responsive

---

## 🎉 If Everything Works

**Congratulations!** The system is working correctly.

**Next Steps**:
1. ✅ Mark testing as complete
2. ✅ Document any issues found
3. ✅ Prepare for user acceptance testing
4. ✅ Plan production deployment

---

## 📞 Quick Commands

```bash
# Start server
./start-local.sh

# Stop server
pkill -f "php -S localhost:8000"

# View logs
tail -f /tmp/pvgs-server.log

# Test API
curl http://localhost:8000/api/departments

# Rebuild frontend
cd frontend && npm run build && cd .. && cp -r frontend/dist/* public/app/
```

---

## 🚀 Ready to Test!

Open your browser and go to:
**http://localhost:8000/app**

Login with: **admin@pvgs.edu / password123**

Happy Testing! 🎊
