#!/bin/bash

# API Test Script - Real World Scenarios
# Tests all workflows end-to-end with real data

API_BASE="${API_BASE:-http://localhost:8000/api/v1}"
RESULTS_FILE="tests/results/api-test-$(date +%Y%m%d-%H%M%S).txt"

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

# Tokens
ADMIN_TOKEN=""
PRINCIPAL_TOKEN=""
REGISTRAR_TOKEN=""
FACULTY_TOKEN=""
STUDENT_TOKEN=""

log() {
    echo -e "${BLUE}[INFO]${NC} $1" | tee -a "$RESULTS_FILE"
}

test_start() {
    echo -e "${YELLOW}[TEST]${NC} $1" | tee -a "$RESULTS_FILE"
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

# Test 1: Authentication
test_authentication() {
    echo ""
    echo "========================================" | tee -a "$RESULTS_FILE"
    echo "Test 1: Authentication" | tee -a "$RESULTS_FILE"
    echo "========================================" | tee -a "$RESULTS_FILE"
    
    # Login as admin
    test_start "Admin login"
    RESPONSE=$(curl -s -X POST "$API_BASE/auth/login" \
        -H "Content-Type: application/json" \
        -d '{"email":"admin@pvgs.edu","password":"password123"}')
    
    ADMIN_TOKEN=$(echo "$RESPONSE" | grep -o '"token":"[^"]*' | cut -d'"' -f4)
    
    if [ -n "$ADMIN_TOKEN" ]; then
        test_pass "Admin logged in successfully"
    else
        test_fail "Admin login failed"
        echo "$RESPONSE" | tee -a "$RESULTS_FILE"
    fi
    
    # Login as principal
    test_start "Principal login"
    RESPONSE=$(curl -s -X POST "$API_BASE/auth/login" \
        -H "Content-Type: application/json" \
        -d '{"email":"principal@pvgs.edu","password":"password123"}')
    
    PRINCIPAL_TOKEN=$(echo "$RESPONSE" | grep -o '"token":"[^"]*' | cut -d'"' -f4)
    [ -n "$PRINCIPAL_TOKEN" ] && test_pass "Principal logged in" || test_fail "Principal login failed"
    
    # Login as registrar
    test_start "Registrar login"
    RESPONSE=$(curl -s -X POST "$API_BASE/auth/login" \
        -H "Content-Type: application/json" \
        -d '{"email":"registrar@pvgs.edu","password":"password123"}')
    
    REGISTRAR_TOKEN=$(echo "$RESPONSE" | grep -o '"token":"[^"]*' | cut -d'"' -f4)
    [ -n "$REGISTRAR_TOKEN" ] && test_pass "Registrar logged in" || test_fail "Registrar login failed"
    
    # Login as faculty
    test_start "Faculty login"
    RESPONSE=$(curl -s -X POST "$API_BASE/auth/login" \
        -H "Content-Type: application/json" \
        -d '{"email":"faculty1@pvgs.edu","password":"password123"}')
    
    FACULTY_TOKEN=$(echo "$RESPONSE" | grep -o '"token":"[^"]*' | cut -d'"' -f4)
    [ -n "$FACULTY_TOKEN" ] && test_pass "Faculty logged in" || test_fail "Faculty login failed"
    
    # Login as student
    test_start "Student login"
    RESPONSE=$(curl -s -X POST "$API_BASE/auth/login" \
        -H "Content-Type: application/json" \
        -d '{"email":"student1@pvgs.edu","password":"password123"}')
    
    STUDENT_TOKEN=$(echo "$RESPONSE" | grep -o '"token":"[^"]*' | cut -d'"' -f4)
    [ -n "$STUDENT_TOKEN" ] && test_pass "Student logged in" || test_fail "Student login failed"
}

# Test 2: Department Access
test_department_access() {
    echo ""
    echo "========================================" | tee -a "$RESULTS_FILE"
    echo "Test 2: Department Access Control" | tee -a "$RESULTS_FILE"
    echo "========================================" | tee -a "$RESULTS_FILE"
    
    # Principal can access all departments
    test_start "Principal access to Science department"
    RESPONSE=$(curl -s -X GET "$API_BASE/departments/1/students" \
        -H "Authorization: Bearer $PRINCIPAL_TOKEN")
    
    if echo "$RESPONSE" | grep -q "success.*true"; then
        test_pass "Principal can access Science department"
    else
        test_fail "Principal cannot access Science department"
    fi
    
    # Faculty can only access their department
    test_start "Faculty access to own department"
    RESPONSE=$(curl -s -X GET "$API_BASE/departments/1/students" \
        -H "Authorization: Bearer $FACULTY_TOKEN")
    
    if echo "$RESPONSE" | grep -q "success.*true"; then
        test_pass "Faculty can access own department"
    else
        test_fail "Faculty cannot access own department"
    fi
    
    # Student cannot access admin endpoints
    test_start "Student blocked from admin endpoints"
    RESPONSE=$(curl -s -X GET "$API_BASE/departments/1/students" \
        -H "Authorization: Bearer $STUDENT_TOKEN")
    
    if echo "$RESPONSE" | grep -q "denied\|forbidden\|unauthorized"; then
        test_pass "Student correctly blocked from admin endpoints"
    else
        test_fail "SECURITY ISSUE: Student can access admin endpoints"
    fi
}

# Test 3: Student Management
test_student_management() {
    echo ""
    echo "========================================" | tee -a "$RESULTS_FILE"
    echo "Test 3: Student Management" | tee -a "$RESULTS_FILE"
    echo "========================================" | tee -a "$RESULTS_FILE"
    
    # Get students list
    test_start "Get students list"
    RESPONSE=$(curl -s -X GET "$API_BASE/departments/1/students" \
        -H "Authorization: Bearer $REGISTRAR_TOKEN")
    
    if echo "$RESPONSE" | grep -q "success.*true"; then
        COUNT=$(echo "$RESPONSE" | grep -o '"total":[0-9]*' | cut -d':' -f2)
        test_pass "Retrieved $COUNT students"
    else
        test_fail "Failed to retrieve students"
    fi
    
    # Get single student
    test_start "Get student details"
    RESPONSE=$(curl -s -X GET "$API_BASE/departments/1/students/1" \
        -H "Authorization: Bearer $REGISTRAR_TOKEN")
    
    if echo "$RESPONSE" | grep -q "success.*true"; then
        test_pass "Retrieved student details"
    else
        test_fail "Failed to retrieve student details"
    fi
}

# Test 4: Attendance
test_attendance() {
    echo ""
    echo "========================================" | tee -a "$RESULTS_FILE"
    echo "Test 4: Attendance Management" | tee -a "$RESULTS_FILE"
    echo "========================================" | tee -a "$RESULTS_FILE"
    
    # Get attendance
    test_start "Get attendance records"
    RESPONSE=$(curl -s -X GET "$API_BASE/departments/1/attendance?date=$(date +%Y-%m-%d)" \
        -H "Authorization: Bearer $FACULTY_TOKEN")
    
    if echo "$RESPONSE" | grep -q "success.*true"; then
        test_pass "Retrieved attendance records"
    else
        test_fail "Failed to retrieve attendance"
    fi
    
    # Get attendance report
    test_start "Generate attendance report"
    RESPONSE=$(curl -s -X GET "$API_BASE/departments/1/attendance/report" \
        -H "Authorization: Bearer $FACULTY_TOKEN")
    
    if echo "$RESPONSE" | grep -q "attendance_percentage"; then
        PERCENTAGE=$(echo "$RESPONSE" | grep -o '"attendance_percentage":[0-9.]*' | cut -d':' -f2)
        test_pass "Attendance report generated: $PERCENTAGE%"
    else
        test_fail "Failed to generate attendance report"
    fi
}

# Test 5: Results
test_results() {
    echo ""
    echo "========================================" | tee -a "$RESULTS_FILE"
    echo "Test 5: Results Management" | tee -a "$RESULTS_FILE"
    echo "========================================" | tee -a "$RESULTS_FILE"
    
    # Get results
    test_start "Get results"
    RESPONSE=$(curl -s -X GET "$API_BASE/departments/1/results" \
        -H "Authorization: Bearer $FACULTY_TOKEN")
    
    if echo "$RESPONSE" | grep -q "success.*true"; then
        test_pass "Retrieved results"
    else
        test_fail "Failed to retrieve results"
    fi
}

# Test 6: Fees
test_fees() {
    echo ""
    echo "========================================" | tee -a "$RESULTS_FILE"
    echo "Test 6: Fee Management" | tee -a "$RESULTS_FILE"
    echo "========================================" | tee -a "$RESULTS_FILE"
    
    # Get fees
    test_start "Get fee records"
    RESPONSE=$(curl -s -X GET "$API_BASE/departments/1/fees" \
        -H "Authorization: Bearer $REGISTRAR_TOKEN")
    
    if echo "$RESPONSE" | grep -q "success.*true"; then
        test_pass "Retrieved fee records"
    else
        test_fail "Failed to retrieve fees"
    fi
    
    # Get fee summary
    test_start "Generate fee summary"
    RESPONSE=$(curl -s -X GET "$API_BASE/departments/1/fees/summary" \
        -H "Authorization: Bearer $REGISTRAR_TOKEN")
    
    if echo "$RESPONSE" | grep -q "total_fees"; then
        TOTAL_FEES=$(echo "$RESPONSE" | grep -o '"total_fees":[0-9]*' | cut -d':' -f2)
        test_pass "Fee summary generated: ₹$TOTAL_FEES"
    else
        test_fail "Failed to generate fee summary"
    fi
}

# Generate report
generate_report() {
    echo ""
    echo "========================================" | tee -a "$RESULTS_FILE"
    echo "TEST SUMMARY" | tee -a "$RESULTS_FILE"
    echo "========================================" | tee -a "$RESULTS_FILE"
    echo "Total Tests: $TOTAL" | tee -a "$RESULTS_FILE"
    echo "Passed: $PASSED" | tee -a "$RESULTS_FILE"
    echo "Failed: $FAILED" | tee -a "$RESULTS_FILE"
    echo "Success Rate: $(( PASSED * 100 / TOTAL ))%" | tee -a "$RESULTS_FILE"
    echo "========================================" | tee -a "$RESULTS_FILE"
    echo ""
    echo "Report saved to: $RESULTS_FILE"
}

# Main execution
main() {
    echo "========================================" | tee "$RESULTS_FILE"
    echo "API TEST - REAL WORLD SCENARIOS" | tee -a "$RESULTS_FILE"
    echo "========================================" | tee -a "$RESULTS_FILE"
    echo "API Base: $API_BASE" | tee -a "$RESULTS_FILE"
    echo "Start Time: $(date)" | tee -a "$RESULTS_FILE"
    echo ""
    
    test_authentication
    test_department_access
    test_student_management
    test_attendance
    test_results
    test_fees
    generate_report
    
    if [ $FAILED -eq 0 ]; then
        echo -e "${GREEN}✅ All tests passed!${NC}"
        exit 0
    else
        echo -e "${RED}❌ Some tests failed. Review results above.${NC}"
        exit 1
    fi
}

main "$@"
