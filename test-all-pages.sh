#!/bin/bash

echo "==================================="
echo "PVGS ERP - Page Testing Report"
echo "==================================="
echo ""

# Test 1: Homepage
echo "1. Testing Homepage (/)..."
STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/)
if [ "$STATUS" = "200" ]; then
    echo "   ✅ Homepage loads successfully"
else
    echo "   ❌ Homepage failed (HTTP $STATUS)"
fi
echo ""

# Test 2: Login Page
echo "2. Testing Login Page (/app/)..."
STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/app/)
if [ "$STATUS" = "200" ]; then
    echo "   ✅ Login page loads successfully"
else
    echo "   ❌ Login page failed (HTTP $STATUS)"
fi
echo ""

# Test 3: Login API
echo "3. Testing Login API..."
RESPONSE=$(curl -s -X POST http://localhost:8000/direct-api.php/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"principal@pvgs.edu","password":"password123"}')
if echo "$RESPONSE" | grep -q '"success":true'; then
    echo "   ✅ Principal login works"
    TOKEN=$(echo "$RESPONSE" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
else
    echo "   ❌ Principal login failed"
fi
echo ""

# Test 4: Faculty Login
echo "4. Testing Faculty Login..."
RESPONSE=$(curl -s -X POST http://localhost:8000/direct-api.php/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"faculty1@pvgs.edu","password":"password123"}')
if echo "$RESPONSE" | grep -q '"success":true'; then
    echo "   ✅ Faculty login works"
else
    echo "   ❌ Faculty login failed"
fi
echo ""

# Test 5: Student Login
echo "5. Testing Student Login..."
RESPONSE=$(curl -s -X POST http://localhost:8000/direct-api.php/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"student1@pvgs.edu","password":"password123"}')
if echo "$RESPONSE" | grep -q '"success":true'; then
    echo "   ✅ Student login works"
else
    echo "   ❌ Student login failed"
fi
echo ""

# Test 6: Departments API
echo "6. Testing Departments API..."
RESPONSE=$(curl -s -X POST http://localhost:8000/direct-api.php/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"principal@pvgs.edu","password":"password123"}')
TOKEN=$(echo "$RESPONSE" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)

DEPT_RESPONSE=$(curl -s -X GET http://localhost:8000/direct-api.php/api/departments \
  -H "Authorization: Bearer $TOKEN")
if echo "$DEPT_RESPONSE" | grep -q '"success":true'; then
    DEPT_COUNT=$(echo "$DEPT_RESPONSE" | grep -o '"id"' | wc -l)
    echo "   ✅ Departments API works ($DEPT_COUNT departments)"
else
    echo "   ❌ Departments API failed"
fi
echo ""

# Test 7: Students API
echo "7. Testing Students API..."
STUDENTS_RESPONSE=$(curl -s -X GET "http://localhost:8000/direct-api.php/api/students?department_id=10" \
  -H "Authorization: Bearer $TOKEN")
if echo "$STUDENTS_RESPONSE" | grep -q '"success":true'; then
    STUDENT_COUNT=$(echo "$STUDENTS_RESPONSE" | grep -o '"id"' | wc -l)
    echo "   ✅ Students API works ($STUDENT_COUNT students)"
else
    echo "   ❌ Students API failed"
fi
echo ""

# Test 8: Attendance API
echo "8. Testing Attendance API..."
ATT_RESPONSE=$(curl -s -X GET "http://localhost:8000/direct-api.php/api/attendance?department_id=10" \
  -H "Authorization: Bearer $TOKEN")
if echo "$ATT_RESPONSE" | grep -q '"success":true'; then
    echo "   ✅ Attendance API works"
else
    echo "   ❌ Attendance API failed"
fi
echo ""

# Test 9: Fees API
echo "9. Testing Fees API..."
FEE_RESPONSE=$(curl -s -X GET "http://localhost:8000/direct-api.php/api/fees?department_id=10" \
  -H "Authorization: Bearer $TOKEN")
if echo "$FEE_RESPONSE" | grep -q '"success":true'; then
    echo "   ✅ Fees API works"
else
    echo "   ❌ Fees API failed"
fi
echo ""

# Test 10: Results API
echo "10. Testing Results API..."
RESULT_RESPONSE=$(curl -s -X GET "http://localhost:8000/direct-api.php/api/results?department_id=10" \
  -H "Authorization: Bearer $TOKEN")
if echo "$RESULT_RESPONSE" | grep -q '"success":true'; then
    echo "   ✅ Results API works"
else
    echo "   ❌ Results API failed"
fi
echo ""

# Test 11: Workflow API
echo "11. Testing Workflow API..."
WF_RESPONSE=$(curl -s -X GET "http://localhost:8000/workflow-api.php/workflows?department_id=10" \
  -H "Authorization: Bearer $TOKEN")
if echo "$WF_RESPONSE" | grep -q '"success":true'; then
    WF_COUNT=$(echo "$WF_RESPONSE" | grep -o '"id"' | wc -l)
    echo "   ✅ Workflow API works ($WF_COUNT workflows)"
else
    echo "   ❌ Workflow API failed"
fi
echo ""

# Test 12: Attendance Report API
echo "12. Testing Attendance Report API..."
ATT_REPORT=$(curl -s -X GET "http://localhost:8000/direct-api.php/api/reports/attendance?department_id=10" \
  -H "Authorization: Bearer $TOKEN")
if echo "$ATT_REPORT" | grep -q '"success":true'; then
    echo "   ✅ Attendance Report API works"
else
    echo "   ❌ Attendance Report API failed"
fi
echo ""

# Test 13: NAAC Report API
echo "13. Testing NAAC Report API..."
NAAC_REPORT=$(curl -s -X GET "http://localhost:8000/direct-api.php/api/reports/naac?department_id=10" \
  -H "Authorization: Bearer $TOKEN")
if echo "$NAAC_REPORT" | grep -q '"success":true'; then
    echo "   ✅ NAAC Report API works"
else
    echo "   ❌ NAAC Report API failed"
fi
echo ""

echo "==================================="
echo "Testing Complete!"
echo "==================================="
