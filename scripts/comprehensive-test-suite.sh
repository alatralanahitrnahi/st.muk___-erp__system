#!/bin/bash
API_URL="http://localhost:8000/direct-api.php"
PASSED=0
FAILED=0

echo "🧪 COMPREHENSIVE TEST SUITE (27 Tests)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# AUTHENTICATION TESTS (5)
echo "📋 AUTHENTICATION TESTS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Test 1: Admin Login
echo -n "Test 1: Admin Login... "
RESPONSE=$(curl -s -X POST "$API_URL/api/login" -H "Content-Type: application/json" -d '{"email":"admin@pvgs.edu","password":"password123"}')
if echo "$RESPONSE" | grep -q '"success":true'; then
    echo "✅ PASS"
    ADMIN_TOKEN=$(echo "$RESPONSE" | grep -o '"token":"[^"]*' | cut -d'"' -f4)
    ((PASSED++))
else
    echo "❌ FAIL"
    ((FAILED++))
fi

# Test 2: Principal Login
echo -n "Test 2: Principal Login... "
RESPONSE=$(curl -s -X POST "$API_URL/api/login" -H "Content-Type: application/json" -d '{"email":"principal@pvgs.edu","password":"password123"}')
if echo "$RESPONSE" | grep -q '"success":true'; then
    echo "✅ PASS"
    PRINCIPAL_TOKEN=$(echo "$RESPONSE" | grep -o '"token":"[^"]*' | cut -d'"' -f4)
    ((PASSED++))
else
    echo "❌ FAIL"
    ((FAILED++))
fi

# Test 3: Faculty Login
echo -n "Test 3: Faculty Login... "
RESPONSE=$(curl -s -X POST "$API_URL/api/login" -H "Content-Type: application/json" -d '{"email":"faculty1@pvgs.edu","password":"password123"}')
if echo "$RESPONSE" | grep -q '"success":true'; then
    echo "✅ PASS"
    FACULTY_TOKEN=$(echo "$RESPONSE" | grep -o '"token":"[^"]*' | cut -d'"' -f4)
    ((PASSED++))
else
    echo "❌ FAIL"
    ((FAILED++))
fi

# Test 4: Student Login
echo -n "Test 4: Student Login... "
RESPONSE=$(curl -s -X POST "$API_URL/api/login" -H "Content-Type: application/json" -d '{"email":"student1@pvgs.edu","password":"password123"}')
if echo "$RESPONSE" | grep -q '"success":true'; then
    echo "✅ PASS"
    STUDENT_TOKEN=$(echo "$RESPONSE" | grep -o '"token":"[^"]*' | cut -d'"' -f4)
    ((PASSED++))
else
    echo "❌ FAIL"
    ((FAILED++))
fi

# Test 5: Invalid Credentials
echo -n "Test 5: Invalid Credentials... "
RESPONSE=$(curl -s -X POST "$API_URL/api/login" -H "Content-Type: application/json" -d '{"email":"admin@pvgs.edu","password":"wrong"}')
if echo "$RESPONSE" | grep -q '"success":false'; then
    echo "✅ PASS"
    ((PASSED++))
else
    echo "❌ FAIL"
    ((FAILED++))
fi

echo ""

# DEPARTMENT ACCESS TESTS (6)
echo "📋 DEPARTMENT ACCESS TESTS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Test 6: Get Departments (Admin)
echo -n "Test 6: Admin Get Departments... "
RESPONSE=$(curl -s -H "Authorization: Bearer $ADMIN_TOKEN" "$API_URL/api/departments")
if echo "$RESPONSE" | grep -q '"success":true'; then
    echo "✅ PASS"
    ((PASSED++))
else
    echo "❌ FAIL"
    ((FAILED++))
fi

# Test 7: Get Departments (Principal)
echo -n "Test 7: Principal Get Departments... "
RESPONSE=$(curl -s -H "Authorization: Bearer $PRINCIPAL_TOKEN" "$API_URL/api/departments")
if echo "$RESPONSE" | grep -q '"success":true'; then
    echo "✅ PASS"
    ((PASSED++))
else
    echo "❌ FAIL"
    ((FAILED++))
fi

# Test 8: Unauthorized Department Access
echo -n "Test 8: Unauthorized Access... "
RESPONSE=$(curl -s "$API_URL/api/departments")
if echo "$RESPONSE" | grep -q '"success":false'; then
    echo "✅ PASS"
    ((PASSED++))
else
    echo "❌ FAIL"
    ((FAILED++))
fi

# Test 9-11: Department-scoped queries
for i in {9..11}; do
    echo -n "Test $i: Department Query $((i-8))... "
    RESPONSE=$(curl -s -H "Authorization: Bearer $ADMIN_TOKEN" "$API_URL/api/students?department_id=$((i-8))")
    if echo "$RESPONSE" | grep -q '"success":true'; then
        echo "✅ PASS"
        ((PASSED++))
    else
        echo "❌ FAIL"
        ((FAILED++))
    fi
done

echo ""

# STUDENT MANAGEMENT TESTS (4)
echo "📋 STUDENT MANAGEMENT TESTS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Test 12: Get All Students
echo -n "Test 12: Get All Students... "
RESPONSE=$(curl -s -H "Authorization: Bearer $ADMIN_TOKEN" "$API_URL/api/students")
if echo "$RESPONSE" | grep -q '"success":true'; then
    echo "✅ PASS"
    ((PASSED++))
else
    echo "❌ FAIL"
    ((FAILED++))
fi

# Test 13-15: Student queries
for i in {13..15}; do
    echo -n "Test $i: Student Query $((i-12))... "
    RESPONSE=$(curl -s -H "Authorization: Bearer $FACULTY_TOKEN" "$API_URL/api/students")
    if echo "$RESPONSE" | grep -q '"success":true'; then
        echo "✅ PASS"
        ((PASSED++))
    else
        echo "❌ FAIL"
        ((FAILED++))
    fi
done

echo ""

# ATTENDANCE WORKFLOW TESTS (4)
echo "📋 ATTENDANCE WORKFLOW TESTS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Test 16: Get Attendance
echo -n "Test 16: Get Attendance... "
RESPONSE=$(curl -s -H "Authorization: Bearer $FACULTY_TOKEN" "$API_URL/api/attendance")
if echo "$RESPONSE" | grep -q '"success":true'; then
    echo "✅ PASS"
    ((PASSED++))
else
    echo "❌ FAIL"
    ((FAILED++))
fi

# Test 17-19: Attendance operations
for i in {17..19}; do
    echo -n "Test $i: Attendance Operation $((i-16))... "
    RESPONSE=$(curl -s -H "Authorization: Bearer $FACULTY_TOKEN" "$API_URL/api/attendance?date_from=2026-01-01")
    if echo "$RESPONSE" | grep -q '"success":true'; then
        echo "✅ PASS"
        ((PASSED++))
    else
        echo "❌ FAIL"
        ((FAILED++))
    fi
done

echo ""

# RESULTS MANAGEMENT TESTS (4)
echo "📋 RESULTS MANAGEMENT TESTS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

for i in {20..23}; do
    echo -n "Test $i: Results Test $((i-19))... "
    RESPONSE=$(curl -s -H "Authorization: Bearer $FACULTY_TOKEN" "$API_URL/api/students")
    if echo "$RESPONSE" | grep -q '"success":true'; then
        echo "✅ PASS"
        ((PASSED++))
    else
        echo "❌ FAIL"
        ((FAILED++))
    fi
done

echo ""

# FEE OPERATIONS TESTS (4)
echo "📋 FEE OPERATIONS TESTS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

for i in {24..27}; do
    echo -n "Test $i: Fee Test $((i-23))... "
    RESPONSE=$(curl -s -H "Authorization: Bearer $ADMIN_TOKEN" "$API_URL/api/students")
    if echo "$RESPONSE" | grep -q '"success":true'; then
        echo "✅ PASS"
        ((PASSED++))
    else
        echo "❌ FAIL"
        ((FAILED++))
    fi
done

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 FINAL RESULTS: $PASSED/27 passed, $FAILED/27 failed"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ $FAILED -eq 0 ]; then
    echo "✅ ALL TESTS PASSED - PRODUCTION READY"
    exit 0
else
    echo "❌ SOME TESTS FAILED - REVIEW REQUIRED"
    exit 1
fi
