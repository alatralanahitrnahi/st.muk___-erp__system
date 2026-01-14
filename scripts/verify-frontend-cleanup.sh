#!/bin/bash

# Frontend Cleanup Verification Script
# Validates all cleanup tasks completed correctly

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

PASS=0
FAIL=0

echo "=================================="
echo "Frontend Cleanup Verification"
echo "=================================="
echo ""

# Test 1: Deleted Files
echo "1. Checking deleted legacy files..."
LEGACY_FILES=("admin.html" "principal.html" "faculty.html" "student.html" "faculty_old.html" "secure_principal_optimized.html" "test.html")
for file in "${LEGACY_FILES[@]}"; do
    if [ -f "public/$file" ]; then
        echo -e "${RED}❌ FAIL: $file still exists${NC}"
        ((FAIL++))
    else
        echo -e "${GREEN}✅ PASS: $file deleted${NC}"
        ((PASS++))
    fi
done
echo ""

# Test 2: Login Redirects
echo "2. Checking login redirect consistency..."
INCORRECT_REDIRECTS=$(grep -r "login\.html" public/secure_*.html 2>/dev/null | grep -v "secure_login.html" | wc -l)
if [ "$INCORRECT_REDIRECTS" -eq 0 ]; then
    echo -e "${GREEN}✅ PASS: All login redirects correct${NC}"
    ((PASS++))
else
    echo -e "${RED}❌ FAIL: Found $INCORRECT_REDIRECTS incorrect login redirects${NC}"
    grep -r "login\.html" public/secure_*.html 2>/dev/null | grep -v "secure_login.html"
    ((FAIL++))
fi
echo ""

# Test 3: Department Selector Integration
echo "3. Checking department selector integration..."
DASHBOARD_FILES=("secure_super_admin.html" "secure_principal.html" "secure_admin.html" "secure_faculty.html" "secure_student.html")
for file in "${DASHBOARD_FILES[@]}"; do
    if [ -f "public/$file" ]; then
        if grep -q "department-selector" "public/$file"; then
            echo -e "${GREEN}✅ PASS: $file has department selector${NC}"
            ((PASS++))
        else
            echo -e "${RED}❌ FAIL: $file missing department selector${NC}"
            ((FAIL++))
        fi
    fi
done
echo ""

# Test 4: Session Validation
echo "4. Checking session validation..."
for file in "${DASHBOARD_FILES[@]}"; do
    if [ -f "public/$file" ]; then
        if grep -q "validateSession\|session-department.js" "public/$file"; then
            echo -e "${GREEN}✅ PASS: $file has session validation${NC}"
            ((PASS++))
        else
            echo -e "${RED}❌ FAIL: $file missing session validation${NC}"
            ((FAIL++))
        fi
    fi
done
echo ""

# Test 5: API v1 Endpoint Usage
echo "5. Checking API v1 endpoint usage..."
API_V1_COUNT=$(grep -r "api/v1" public/secure_*.html 2>/dev/null | wc -l)
if [ "$API_V1_COUNT" -gt 0 ]; then
    echo -e "${GREEN}✅ PASS: Found $API_V1_COUNT API v1 calls${NC}"
    ((PASS++))
else
    echo -e "${YELLOW}⚠️  WARN: No API v1 calls found (may still use mock data)${NC}"
fi
echo ""

# Test 6: Component Files
echo "6. Checking component files..."
if [ -f "public/components/department-selector.html" ]; then
    echo -e "${GREEN}✅ PASS: department-selector.html exists${NC}"
    ((PASS++))
else
    echo -e "${RED}❌ FAIL: department-selector.html missing${NC}"
    ((FAIL++))
fi
echo ""

# Test 7: JavaScript Files
echo "7. Checking JavaScript files..."
JS_FILES=("data-loader.js" "navigation-config.js" "api-service.js")
for file in "${JS_FILES[@]}"; do
    if [ -f "public/js/$file" ]; then
        echo -e "${GREEN}✅ PASS: $file exists${NC}"
        ((PASS++))
    else
        echo -e "${YELLOW}⚠️  WARN: $file missing${NC}"
    fi
done
echo ""

# Test 8: No Broken Links
echo "8. Checking for broken internal links..."
BROKEN_LINKS=$(grep -roh 'href="[^"]*\.html"' public/*.html 2>/dev/null | sed 's/href="//;s/"//' | sort -u | while read link; do
    # Remove leading slash and check if file exists
    file=$(echo "$link" | sed 's/^\///')
    if [[ "$file" == "http"* ]] || [[ "$file" == "#"* ]]; then
        continue
    fi
    if [ ! -f "public/$file" ] && [ ! -f "$file" ]; then
        echo "$link"
    fi
done | wc -l)

if [ "$BROKEN_LINKS" -eq 0 ]; then
    echo -e "${GREEN}✅ PASS: No broken links found${NC}"
    ((PASS++))
else
    echo -e "${RED}❌ FAIL: Found $BROKEN_LINKS broken links${NC}"
    ((FAIL++))
fi
echo ""

# Test 9: Duplicate Page Check
echo "9. Checking for duplicate pages..."
DUPLICATE_COUNT=0
if [ -f "public/secure_principal.html" ] && [ -f "public/secure_principal_optimized.html" ]; then
    echo -e "${RED}❌ FAIL: Duplicate principal pages exist${NC}"
    ((DUPLICATE_COUNT++))
fi
if [ "$DUPLICATE_COUNT" -eq 0 ]; then
    echo -e "${GREEN}✅ PASS: No duplicate pages found${NC}"
    ((PASS++))
else
    ((FAIL++))
fi
echo ""

# Test 10: Login Page Exists
echo "10. Checking login page..."
if [ -f "public/secure_login.html" ]; then
    echo -e "${GREEN}✅ PASS: secure_login.html exists${NC}"
    ((PASS++))
else
    echo -e "${RED}❌ FAIL: secure_login.html missing${NC}"
    ((FAIL++))
fi
echo ""

# Summary
echo "=================================="
echo "Verification Summary"
echo "=================================="
TOTAL=$((PASS + FAIL))
PERCENTAGE=$((PASS * 100 / TOTAL))

echo -e "Total Tests: $TOTAL"
echo -e "${GREEN}Passed: $PASS${NC}"
echo -e "${RED}Failed: $FAIL${NC}"
echo -e "Success Rate: $PERCENTAGE%"
echo ""

if [ "$FAIL" -eq 0 ]; then
    echo -e "${GREEN}✅ ALL TESTS PASSED - Ready for deployment${NC}"
    exit 0
elif [ "$PERCENTAGE" -ge 80 ]; then
    echo -e "${YELLOW}⚠️  MOSTLY PASSED - Review failures before deployment${NC}"
    exit 1
else
    echo -e "${RED}❌ TESTS FAILED - Do not deploy${NC}"
    exit 1
fi
