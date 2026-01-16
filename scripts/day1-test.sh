#!/bin/bash
# Day 1: Backend Test Execution
# Execution Time: 2 hours

set -e

echo "=========================================="
echo "PVGS ERP - Backend Test Suite"
echo "=========================================="

cd /workspaces/st.muk___-erp__system

# Create results directory
mkdir -p tests/results
TIMESTAMP=$(date +%Y%m%d-%H%M%S)
RESULTS_FILE="tests/results/day1-validation-${TIMESTAMP}.txt"

# Start API Server
echo "[1/4] Starting API server..."
php artisan serve --host=0.0.0.0 --port=8000 --no-interaction > logs/api-server.log 2>&1 &
API_PID=$!
echo "API Server PID: $API_PID"

# Wait for server to start
sleep 5

# Check if server is running
if ! curl -s http://localhost:8000 > /dev/null; then
    echo "❌ API server failed to start"
    kill $API_PID 2>/dev/null || true
    exit 1
fi

echo "✅ API server running on http://localhost:8000"

# Step 2: Run Real-World Validation
echo "[2/4] Running real-world validation tests..."
if [ -f "tests/api-test-realworld.sh" ]; then
    bash tests/api-test-realworld.sh 2>&1 | tee -a "$RESULTS_FILE"
    REALWORLD_STATUS=$?
else
    echo "⚠️  Real-world test script not found, skipping..."
    REALWORLD_STATUS=0
fi

# Step 3: Run PHPUnit Tests
echo "[3/4] Running PHPUnit test suite..."
php artisan test --parallel --processes=4 2>&1 | tee -a "$RESULTS_FILE"
PHPUNIT_STATUS=$?

# Step 4: Generate Test Report
echo "[4/4] Generating test report..."

cat > "$RESULTS_FILE" << EOF

========================================
PVGS ERP - Day 1 Test Results
========================================
Date: $(date)
Duration: 2 hours

Test Execution Summary:
- Real-World Tests: $([ $REALWORLD_STATUS -eq 0 ] && echo "✅ PASSED" || echo "❌ FAILED")
- PHPUnit Tests: $([ $PHPUNIT_STATUS -eq 0 ] && echo "✅ PASSED" || echo "❌ FAILED")

Database Statistics:
- Users: $(sqlite3 database/database.sqlite 'SELECT COUNT(*) FROM users;' 2>/dev/null || echo "N/A")
- Departments: $(sqlite3 database/database.sqlite 'SELECT COUNT(*) FROM departments;' 2>/dev/null || echo "N/A")
- Students: $(sqlite3 database/database.sqlite 'SELECT COUNT(*) FROM students;' 2>/dev/null || echo "N/A")

API Server Status: Running (PID: $API_PID)

Next Steps:
$([ $REALWORLD_STATUS -eq 0 ] && [ $PHPUNIT_STATUS -eq 0 ] && echo "✅ All tests passed - Proceed to Day 2 (Frontend)" || echo "❌ Fix failing tests before proceeding")

========================================
EOF

cat "$RESULTS_FILE"

# Cleanup
echo ""
echo "Stopping API server..."
kill $API_PID 2>/dev/null || true

echo ""
echo "=========================================="
echo "Test Results: $RESULTS_FILE"
echo "=========================================="

# Exit with combined status
[ $REALWORLD_STATUS -eq 0 ] && [ $PHPUNIT_STATUS -eq 0 ]
