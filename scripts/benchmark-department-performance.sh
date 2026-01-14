#!/bin/bash

# Performance Benchmarking Script for Department-Aware System
# Tests system under 5,000 concurrent user load during NAAC audit periods

echo "==================================="
echo "Department Performance Benchmark"
echo "Target: Sub-second response times"
echo "Load: 5,000 concurrent users"
echo "==================================="
echo ""

# Configuration
BASE_URL="${BASE_URL:-http://localhost/api}"
DEPARTMENT_ID="${DEPARTMENT_ID:-1}"
AUTH_TOKEN="${AUTH_TOKEN:-}"
CONCURRENT_USERS=5000
DURATION=300

echo "1. Testing Department Dashboard"
echo "   Target: < 1000ms"
ab -n 10000 -c $CONCURRENT_USERS -t $DURATION \
   -H "Authorization: Bearer $AUTH_TOKEN" \
   "$BASE_URL/v1/departments/$DEPARTMENT_ID/dashboard" \
   > dashboard_benchmark.txt 2>&1

DASHBOARD_AVG=$(grep "Time per request" dashboard_benchmark.txt | head -1 | awk '{print $4}')
echo "   Result: ${DASHBOARD_AVG}ms"
echo ""

echo "2. Testing Department Students"
echo "   Target: < 500ms"
ab -n 10000 -c $CONCURRENT_USERS -t $DURATION \
   -H "Authorization: Bearer $AUTH_TOKEN" \
   "$BASE_URL/v1/departments/$DEPARTMENT_ID/students" \
   > students_benchmark.txt 2>&1

STUDENTS_AVG=$(grep "Time per request" students_benchmark.txt | head -1 | awk '{print $4}')
echo "   Result: ${STUDENTS_AVG}ms"
echo ""

echo "3. Testing Permission Cache"
echo "   Target: < 10ms"
ab -n 50000 -c $CONCURRENT_USERS -t 60 \
   -H "Authorization: Bearer $AUTH_TOKEN" \
   "$BASE_URL/users/1/department-permissions?department_id=$DEPARTMENT_ID" \
   > permissions_benchmark.txt 2>&1

PERMISSIONS_AVG=$(grep "Time per request" permissions_benchmark.txt | head -1 | awk '{print $4}')
echo "   Result: ${PERMISSIONS_AVG}ms"
echo ""

echo "==================================="
echo "Summary"
echo "==================================="
echo "Dashboard:    ${DASHBOARD_AVG}ms (target: <1000ms)"
echo "Students:     ${STUDENTS_AVG}ms (target: <500ms)"
echo "Permissions:  ${PERMISSIONS_AVG}ms (target: <10ms)"
