#!/bin/bash

# Module Functionality Verification Script

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

PASS=0
FAIL=0

echo "===================================="
echo "Module Functionality Verification"
echo "===================================="
echo ""

DASHBOARDS=("secure_super_admin.html" "secure_principal.html" "secure_admin.html" "secure_faculty.html" "secure_student.html")

# Test 1: Module Loader Integration
echo "1. Checking module-loader.js integration..."
for dashboard in "${DASHBOARDS[@]}"; do
    if grep -q "module-loader.js" "public/$dashboard"; then
        echo -e "${GREEN}✅ PASS: $dashboard has module-loader.js${NC}"
        ((PASS++))
    else
        echo -e "${RED}❌ FAIL: $dashboard missing module-loader.js${NC}"
        ((FAIL++))
    fi
done
echo ""

# Test 2: Main Content Container
echo "2. Checking main content container..."
for dashboard in "${DASHBOARDS[@]}"; do
    if grep -q 'id="main-content"' "public/$dashboard"; then
        echo -e "${GREEN}✅ PASS: $dashboard has main-content container${NC}"
        ((PASS++))
    else
        echo -e "${RED}❌ FAIL: $dashboard missing main-content container${NC}"
        ((FAIL++))
    fi
done
echo ""

# Test 3: showSection Function
echo "3. Checking showSection function..."
for dashboard in "${DASHBOARDS[@]}"; do
    if grep -q "showSection\|ModuleLoader" "public/$dashboard"; then
        echo -e "${GREEN}✅ PASS: $dashboard has module loading function${NC}"
        ((PASS++))
    else
        echo -e "${RED}❌ FAIL: $dashboard missing module loading function${NC}"
        ((FAIL++))
    fi
done
echo ""

# Test 4: Navigation Items
echo "4. Checking navigation items..."
for dashboard in "${DASHBOARDS[@]}"; do
    NAV_COUNT=$(grep -c "onclick=\"showSection\|data-module=" "public/$dashboard" 2>/dev/null || echo "0")
    if [ "$NAV_COUNT" -gt 0 ]; then
        echo -e "${GREEN}✅ PASS: $dashboard has $NAV_COUNT navigation items${NC}"
        ((PASS++))
    else
        echo -e "${YELLOW}⚠️  WARN: $dashboard has no navigation items${NC}"
    fi
done
echo ""

# Test 5: Module Loader JS File
echo "5. Checking module-loader.js file..."
if [ -f "public/js/module-loader.js" ]; then
    echo -e "${GREEN}✅ PASS: module-loader.js exists${NC}"
    ((PASS++))
    
    # Check for key functions
    if grep -q "loadModule" "public/js/module-loader.js"; then
        echo -e "${GREEN}✅ PASS: loadModule function exists${NC}"
        ((PASS++))
    else
        echo -e "${RED}❌ FAIL: loadModule function missing${NC}"
        ((FAIL++))
    fi
    
    if grep -q "departmentChanged" "public/js/module-loader.js"; then
        echo -e "${GREEN}✅ PASS: Department change listener exists${NC}"
        ((PASS++))
    else
        echo -e "${RED}❌ FAIL: Department change listener missing${NC}"
        ((FAIL++))
    fi
else
    echo -e "${RED}❌ FAIL: module-loader.js file missing${NC}"
    ((FAIL++))
fi
echo ""

# Test 6: API Integration
echo "6. Checking API integration..."
if grep -q "apiCall" "public/js/module-loader.js"; then
    echo -e "${GREEN}✅ PASS: API integration present${NC}"
    ((PASS++))
else
    echo -e "${RED}❌ FAIL: API integration missing${NC}"
    ((FAIL++))
fi
echo ""

# Summary
echo "===================================="
echo "Verification Summary"
echo "===================================="
TOTAL=$((PASS + FAIL))
if [ "$TOTAL" -eq 0 ]; then
    PERCENTAGE=0
else
    PERCENTAGE=$((PASS * 100 / TOTAL))
fi

echo -e "Total Tests: $TOTAL"
echo -e "${GREEN}Passed: $PASS${NC}"
echo -e "${RED}Failed: $FAIL${NC}"
echo -e "Success Rate: $PERCENTAGE%"
echo ""

if [ "$FAIL" -eq 0 ]; then
    echo -e "${GREEN}✅ ALL MODULE TESTS PASSED - Modules ready${NC}"
    exit 0
elif [ "$PERCENTAGE" -ge 80 ]; then
    echo -e "${YELLOW}⚠️  MOSTLY PASSED - Review failures${NC}"
    exit 1
else
    echo -e "${RED}❌ TESTS FAILED - Modules not functional${NC}"
    exit 1
fi
