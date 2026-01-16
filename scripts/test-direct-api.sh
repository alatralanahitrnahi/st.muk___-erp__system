#!/bin/bash

# PVGS ERP Direct API Test Suite
# Tests all critical endpoints with authentication, RBAC, and department isolation

API_URL="${API_URL:-http://localhost:8000/direct-api.php}"
RESULTS_FILE="tests/results/direct-api-test-$(date +%Y%m%d-%H%M%S).txt"

mkdir -p tests/results

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Counters
TOTAL=0
PASSED=0
FAILED=0

log() {
    echo -e "${BLUE}[INFO]${NC} $1" | tee -a "$RESULTS_FILE"
}

test_start() {
    echo -e "${YELLOW}[TEST $((TOTAL + 1))]${NC} $1" | tee -a "$RESULTS_FILE"
    TOTAL=$((TOTAL + 1))
}

test_pass() {
    echo -e "${GREEN}[PASS]${NC} $1" | tee -a "$RESULTS_FILE"
    PASSED=$((PASSED + 1))
}

test_fail() {
    echo -e "${RED}[FAIL]${NC} $1" | tee -a "$RESULTS_FILE"
    FAILED=$((FAILED + 1))
}

echo "========================================" | tee "$RESULTS_FILE"
echo "PVGS ERP Direct API Test Suite" | tee -a "$RESULTS_FILE"
echo "========================================" | tee -a "$RESULTS_FILE"
echo "API URL: $API_URL" | tee -a "$RESULTS_FILE"
echo "Started: $(date)" | tee -a "$RESULTS_FILE"
echo "" | tee -a "$RESULTS_FILE"

# Test 1: Health Check
test_start "Health Check"
response=$(curl -s "$API_URL/api/health")
if echo "$response" | grep -q '"success":true'; then
    test_pass "API is healthy"
else
    test_fail "API health check failed"
    echo "Response: $response" | tee -a "$RESULTS_FILE"
fi

# Test 2: Login as Principal
test_start "Principal Login"
response=$(curl -s -X POST "$API_URL/api/login" \
    -H "Content-Type: application/json" \
    -d '{"email":"principal@pvgs.edu","password":"password123"}')

PRINCIPAL_TOKEN=$(echo "$response" | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -n "$PRINCIPAL_TOKEN" ] && [ "$PRINCIPAL_TOKEN" != "null" ]; then
    test_pass "Principal logged in successfully"
    echo "Token: ${PRINCIPAL_TOKEN:0:20}..." | tee -a "$RESULTS_FILE"
else
    test_fail "Principal login failed"
    echo "Response: $response" | tee -a "$RESULTS_FILE"
fi

# Test 3: Get Departments (Principal - should see all)
test_start "Get Departments (Principal)"
response=$(curl -s -H "Authorization: Bearer $PRINCIPAL_TOKEN" \
    "$API_URL/api/departments")

dept_count=$(echo "$response" | grep -o '"id"' | wc -l)
if [ "$dept_count" -ge 3 ]; then
    test_pass "Principal can access all departments (found $dept_count)"
else
    test_fail "Principal department access failed"
    echo "Response: $response" | tee -a "$RESULTS_FILE"
fi

# Test 4: Get Students (Department 1)
test_start "Get Students (Department 1)"
response=$(curl -s -H "Authorization: Bearer $PRINCIPAL_TOKEN" \
    "$API_URL/api/students?department_id=1")

if echo "$response" | grep -q '"success":true'; then
    student_count=$(echo "$response" | grep -o '"id"' | wc -l)
    test_pass "Retrieved students from Department 1 (count: $student_count)"
else
    test_fail "Failed to retrieve students"
    echo "Response: $response" | tee -a "$RESULTS_FILE"
fi

# Test 5: Login as Faculty
test_start "Faculty Login"
response=$(curl -s -X POST "$API_URL/api/login" \
    -H "Content-Type: application/json" \
    -d '{"email":"faculty1@pvgs.edu","password":"password123"}')

FACULTY_TOKEN=$(echo "$response" | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -n "$FACULTY_TOKEN" ] && [ "$FACULTY_TOKEN" != "null" ]; then
    test_pass "Faculty logged in successfully"
else
    test_fail "Faculty login failed"
    echo "Response: $response" | tee -a "$RESULTS_FILE"
fi

# Test 6: Faculty Department Restriction
test_start "Faculty Department Restriction"
response=$(curl -s -H "Authorization: Bearer $FACULTY_TOKEN" \
    "$API_URL/api/students?department_id=2")

if echo "$response" | grep -q '"success":false'; then
    test_pass "Faculty correctly restricted from other departments"
else
    test_fail "Faculty department isolation failed"
    echo "Response: $response" | tee -a "$RESULTS_FILE"
fi

# Test 7: Login as Student
test_start "Student Login"
response=$(curl -s -X POST "$API_URL/api/login" \
    -H "Content-Type: application/json" \
    -d '{"email":"student1@pvgs.edu","password":"password123"}')

STUDENT_TOKEN=$(echo "$response" | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -n "$STUDENT_TOKEN" ] && [ "$STUDENT_TOKEN" != "null" ]; then
    test_pass "Student logged in successfully"
else
    test_fail "Student login failed"
    echo "Response: $response" | tee -a "$RESULTS_FILE"
fi

# Test 8: Student Access Restriction
test_start "Student Access Restriction"
response=$(curl -s -H "Authorization: Bearer $STUDENT_TOKEN" \
    "$API_URL/api/students?department_id=1")

if echo "$response" | grep -q '"success":false'; then
    test_pass "Student correctly restricted from admin endpoints"
else
    test_fail "Student access control failed"
    echo "Response: $response" | tee -a "$RESULTS_FILE"
fi

# Test 9: Invalid Token
test_start "Invalid Token Rejection"
response=$(curl -s -H "Authorization: Bearer invalid_token_12345" \
    "$API_URL/api/departments")

if echo "$response" | grep -q '"success":false'; then
    test_pass "Invalid token correctly rejected"
else
    test_fail "Invalid token was accepted"
    echo "Response: $response" | tee -a "$RESULTS_FILE"
fi

# Test 10: Missing Department ID
test_start "Missing Department ID Validation"
response=$(curl -s -H "Authorization: Bearer $PRINCIPAL_TOKEN" \
    "$API_URL/api/students")

if echo "$response" | grep -q 'department_id required'; then
    test_pass "Missing department_id correctly validated"
else
    test_fail "Missing parameter validation failed"
    echo "Response: $response" | tee -a "$RESULTS_FILE"
fi

# Test 11: Attendance Report
test_start "Attendance Report"
response=$(curl -s -H "Authorization: Bearer $PRINCIPAL_TOKEN" \
    "$API_URL/api/reports/attendance?department_id=1")

if echo "$response" | grep -q '"success":true'; then
    test_pass "Attendance report generated successfully"
else
    test_fail "Attendance report generation failed"
    echo "Response: $response" | tee -a "$RESULTS_FILE"
fi

# Test 12: NAAC Report
test_start "NAAC Compliance Report"
response=$(curl -s -H "Authorization: Bearer $PRINCIPAL_TOKEN" \
    "$API_URL/api/reports/naac?department_id=1")

if echo "$response" | grep -q '"success":true'; then
    test_pass "NAAC report generated successfully"
else
    test_fail "NAAC report generation failed"
    echo "Response: $response" | tee -a "$RESULTS_FILE"
fi

# Test 13: Performance Benchmark
test_start "Performance Benchmark (<200ms)"
start_time=$(date +%s%N)
response=$(curl -s -H "Authorization: Bearer $PRINCIPAL_TOKEN" \
    "$API_URL/api/students?department_id=1")
end_time=$(date +%s%N)
duration=$(( (end_time - start_time) / 1000000 ))

if [ "$duration" -lt 200 ]; then
    test_pass "Response time: ${duration}ms (target: <200ms)"
else
    test_fail "Response time: ${duration}ms (exceeds 200ms target)"
fi

# Test 14: Rate Limiting (simulate)
test_start "Rate Limiting Check"
count=0
for i in {1..65}; do
    response=$(curl -s -H "Authorization: Bearer $PRINCIPAL_TOKEN" \
        "$API_URL/api/students?department_id=1")
    if echo "$response" | grep -q 'Rate limit exceeded'; then
        count=$((count + 1))
    fi
done

if [ "$count" -gt 0 ]; then
    test_pass "Rate limiting enforced after 60 requests"
else
    test_fail "Rate limiting not working (may need more requests)"
fi

# Summary
echo "" | tee -a "$RESULTS_FILE"
echo "========================================" | tee -a "$RESULTS_FILE"
echo "TEST SUMMARY" | tee -a "$RESULTS_FILE"
echo "========================================" | tee -a "$RESULTS_FILE"
echo "Total Tests: $TOTAL" | tee -a "$RESULTS_FILE"
echo "Passed: $PASSED" | tee -a "$RESULTS_FILE"
echo "Failed: $FAILED" | tee -a "$RESULTS_FILE"
echo "Success Rate: $(( PASSED * 100 / TOTAL ))%" | tee -a "$RESULTS_FILE"
echo "========================================" | tee -a "$RESULTS_FILE"
echo "Completed: $(date)" | tee -a "$RESULTS_FILE"
echo "" | tee -a "$RESULTS_FILE"

if [ "$FAILED" -eq 0 ]; then
    echo -e "${GREEN}✅ ALL TESTS PASSED${NC}" | tee -a "$RESULTS_FILE"
    exit 0
else
    echo -e "${RED}❌ SOME TESTS FAILED${NC}" | tee -a "$RESULTS_FILE"
    exit 1
fi
