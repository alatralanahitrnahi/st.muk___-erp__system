#!/bin/bash
API_URL="http://localhost:8000/direct-api.php"
PASSED=0
FAILED=0

echo "🧪 DIRECT API TEST SUITE"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Test 1: Login as Admin
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

# Test 2: Invalid credentials
echo -n "Test 2: Invalid Login... "
RESPONSE=$(curl -s -X POST "$API_URL/api/login" -H "Content-Type: application/json" -d '{"email":"admin@pvgs.edu","password":"wrong"}')
if echo "$RESPONSE" | grep -q '"success":false'; then
    echo "✅ PASS"
    ((PASSED++))
else
    echo "❌ FAIL"
    ((FAILED++))
fi

# Test 3: Get user without token
echo -n "Test 3: Unauthorized Access... "
RESPONSE=$(curl -s "$API_URL/api/user")
if echo "$RESPONSE" | grep -q '"success":false'; then
    echo "✅ PASS"
    ((PASSED++))
else
    echo "❌ FAIL"
    ((FAILED++))
fi

# Test 4: Get user with token
echo -n "Test 4: Get User Profile... "
RESPONSE=$(curl -s -H "Authorization: Bearer $ADMIN_TOKEN" "$API_URL/api/user")
if echo "$RESPONSE" | grep -q '"success":true'; then
    echo "✅ PASS"
    ((PASSED++))
else
    echo "❌ FAIL"
    ((FAILED++))
fi

# Test 5: Get departments
echo -n "Test 5: Get Departments... "
RESPONSE=$(curl -s -H "Authorization: Bearer $ADMIN_TOKEN" "$API_URL/api/departments")
if echo "$RESPONSE" | grep -q '"success":true'; then
    echo "✅ PASS"
    ((PASSED++))
else
    echo "❌ FAIL"
    ((FAILED++))
fi

# Test 6: Get students
echo -n "Test 6: Get Students... "
RESPONSE=$(curl -s -H "Authorization: Bearer $ADMIN_TOKEN" "$API_URL/api/students")
if echo "$RESPONSE" | grep -q '"success":true'; then
    echo "✅ PASS"
    ((PASSED++))
else
    echo "❌ FAIL"
    ((FAILED++))
fi

# Test 7: Login as Faculty
echo -n "Test 7: Faculty Login... "
RESPONSE=$(curl -s -X POST "$API_URL/api/login" -H "Content-Type: application/json" -d '{"email":"faculty1@pvgs.edu","password":"password123"}')
if echo "$RESPONSE" | grep -q '"success":true'; then
    echo "✅ PASS"
    FACULTY_TOKEN=$(echo "$RESPONSE" | grep -o '"token":"[^"]*' | cut -d'"' -f4)
    ((PASSED++))
else
    echo "❌ FAIL"
    ((FAILED++))
fi

# Test 8: Login as Student
echo -n "Test 8: Student Login... "
RESPONSE=$(curl -s -X POST "$API_URL/api/login" -H "Content-Type: application/json" -d '{"email":"student1@pvgs.edu","password":"password123"}')
if echo "$RESPONSE" | grep -q '"success":true'; then
    echo "✅ PASS"
    STUDENT_TOKEN=$(echo "$RESPONSE" | grep -o '"token":"[^"]*' | cut -d'"' -f4)
    ((PASSED++))
else
    echo "❌ FAIL"
    ((FAILED++))
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 RESULTS: $PASSED passed, $FAILED failed"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ $FAILED -eq 0 ]; then
    echo "✅ ALL TESTS PASSED"
    exit 0
else
    echo "❌ SOME TESTS FAILED"
    exit 1
fi
