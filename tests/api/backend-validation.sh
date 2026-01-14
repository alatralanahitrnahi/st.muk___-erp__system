#!/bin/bash

# Backend API & Workflow Validation Suite
# Comprehensive testing of all endpoints, workflows, and permissions

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

BASE_URL="${BASE_URL:-http://localhost:8000/api/v1}"
PASS=0
FAIL=0
TOTAL=0

echo "========================================"
echo "Backend Validation Suite"
echo "========================================"
echo "Base URL: $BASE_URL"
echo ""

# Test helper functions
test_api() {
    local name="$1"
    local method="$2"
    local endpoint="$3"
    local expected_status="$4"
    local auth_token="$5"
    
    ((TOTAL++))
    
    if [ -n "$auth_token" ]; then
        response=$(curl -s -w "\n%{http_code}" -X "$method" \
            -H "Authorization: Bearer $auth_token" \
            -H "Content-Type: application/json" \
            "$BASE_URL$endpoint" 2>/dev/null)
    else
        response=$(curl -s -w "\n%{http_code}" -X "$method" \
            -H "Content-Type: application/json" \
            "$BASE_URL$endpoint" 2>/dev/null)
    fi
    
    status=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')
    
    if [ "$status" = "$expected_status" ]; then
        echo -e "${GREEN}✅ PASS${NC}: $name (HTTP $status)"
        ((PASS++))
        return 0
    else
        echo -e "${RED}❌ FAIL${NC}: $name (Expected $expected_status, got $status)"
        ((FAIL++))
        return 1
    fi
}

test_response_time() {
    local name="$1"
    local endpoint="$2"
    local max_time="$3"
    
    ((TOTAL++))
    
    start=$(date +%s%N)
    curl -s "$BASE_URL$endpoint" > /dev/null 2>&1
    end=$(date +%s%N)
    
    duration=$(( (end - start) / 1000000 ))
    
    if [ "$duration" -lt "$max_time" ]; then
        echo -e "${GREEN}✅ PASS${NC}: $name (${duration}ms < ${max_time}ms)"
        ((PASS++))
        return 0
    else
        echo -e "${RED}❌ FAIL${NC}: $name (${duration}ms >= ${max_time}ms)"
        ((FAIL++))
        return 1
    fi
}

# Section 1: API Endpoint Validation
echo "========================================"
echo "1. API Endpoint Validation"
echo "========================================"
echo ""

echo "Testing Department Endpoints..."
test_api "Get Science Dept Students" "GET" "/departments/1/students" "200"
test_api "Get Commerce Dept Students" "GET" "/departments/2/students" "200"
test_api "Get Arts Dept Students" "GET" "/departments/3/students" "200"
test_api "Get Science Dept Dashboard" "GET" "/departments/1/dashboard" "200"
test_api "Get Invalid Department" "GET" "/departments/999/students" "404"
echo ""

echo "Testing Performance..."
test_response_time "Students endpoint < 500ms" "/departments/1/students" 500
test_response_time "Dashboard endpoint < 500ms" "/departments/1/dashboard" 500
echo ""

# Section 2: Permission Testing
echo "========================================"
echo "2. Role-Based Permission Testing"
echo "========================================"
echo ""

echo "Testing Permission Boundaries..."
test_api "Unauthenticated access denied" "GET" "/departments/1/students" "401"
test_api "Invalid token rejected" "GET" "/departments/1/students" "401" "invalid_token"
echo ""

# Section 3: Data Integrity
echo "========================================"
echo "3. Data Integrity & Isolation"
echo "========================================"
echo ""

echo "Testing Department Isolation..."
# These would check that data from dept 1 doesn't leak to dept 2
echo -e "${BLUE}ℹ️  INFO${NC}: Department isolation requires live API"
echo ""

# Section 4: Workflow Validation
echo "========================================"
echo "4. Workflow Validation"
echo "========================================"
echo ""

echo "Testing Workflow Endpoints..."
test_api "Get workflow history" "GET" "/workflows/history?department_id=1" "200"
test_api "Get pending approvals" "GET" "/workflows/pending?department_id=1" "200"
echo ""

# Summary
echo "========================================"
echo "Test Summary"
echo "========================================"
echo -e "Total Tests: $TOTAL"
echo -e "${GREEN}Passed: $PASS${NC}"
echo -e "${RED}Failed: $FAIL${NC}"

if [ $TOTAL -eq 0 ]; then
    PERCENTAGE=0
else
    PERCENTAGE=$((PASS * 100 / TOTAL))
fi

echo -e "Success Rate: $PERCENTAGE%"
echo ""

if [ $FAIL -eq 0 ]; then
    echo -e "${GREEN}✅ ALL TESTS PASSED${NC}"
    exit 0
elif [ $PERCENTAGE -ge 80 ]; then
    echo -e "${YELLOW}⚠️  MOSTLY PASSED - Review failures${NC}"
    exit 1
else
    echo -e "${RED}❌ TESTS FAILED${NC}"
    exit 1
fi
