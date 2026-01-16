#!/bin/bash

API_URL="http://localhost:8000/api"
echo "🧪 Manual API Testing"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Test 1: Login as Admin
echo "1️⃣  Testing Admin Login..."
ADMIN_RESPONSE=$(curl -s -X POST "$API_URL/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@pvgs.edu","password":"password123"}')

echo "$ADMIN_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$ADMIN_RESPONSE"
ADMIN_TOKEN=$(echo "$ADMIN_RESPONSE" | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -n "$ADMIN_TOKEN" ]; then
    echo "✅ Admin login successful"
    echo "Token: ${ADMIN_TOKEN:0:20}..."
else
    echo "❌ Admin login failed"
    exit 1
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Test 2: Get User Info
echo "2️⃣  Testing Get User Info..."
USER_RESPONSE=$(curl -s -X GET "$API_URL/user" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Accept: application/json")

echo "$USER_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$USER_RESPONSE"
echo ""

# Test 3: Get Departments
echo "3️⃣  Testing Get Departments..."
DEPT_RESPONSE=$(curl -s -X GET "$API_URL/departments" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Accept: application/json")

echo "$DEPT_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$DEPT_RESPONSE"
echo ""

# Test 4: Get Students
echo "4️⃣  Testing Get Students..."
STUDENTS_RESPONSE=$(curl -s -X GET "$API_URL/students" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Accept: application/json")

echo "$STUDENTS_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$STUDENTS_RESPONSE"
echo ""

# Test 5: Login as Student
echo "5️⃣  Testing Student Login..."
STUDENT_RESPONSE=$(curl -s -X POST "$API_URL/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"student1@pvgs.edu","password":"password123"}')

echo "$STUDENT_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$STUDENT_RESPONSE"
STUDENT_TOKEN=$(echo "$STUDENT_RESPONSE" | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -n "$STUDENT_TOKEN" ]; then
    echo "✅ Student login successful"
else
    echo "❌ Student login failed"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Manual API tests completed!"
echo ""
echo "Next steps:"
echo "  1. Start Laravel server: php artisan serve"
echo "  2. Run this script: bash test_api.sh"
echo ""
