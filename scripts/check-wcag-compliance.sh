#!/bin/bash

# WCAG 2.1 AA Compliance Checker

echo "🔍 WCAG 2.1 AA Compliance Check"
echo "================================"
echo ""

DASHBOARDS=("secure_super_admin.html" "secure_principal.html" "secure_admin.html" "secure_faculty.html" "secure_student.html")

# Check 1: Theme CSS linked
echo "1. Checking theme CSS integration..."
for dashboard in "${DASHBOARDS[@]}"; do
    if grep -q "department-theme.css" "public/$dashboard"; then
        echo "✅ $dashboard has theme CSS"
    else
        echo "❌ $dashboard missing theme CSS"
    fi
done
echo ""

# Check 2: Theme JS linked
echo "2. Checking theme JS integration..."
for dashboard in "${DASHBOARDS[@]}"; do
    if grep -q "theme.js" "public/$dashboard"; then
        echo "✅ $dashboard has theme JS"
    else
        echo "❌ $dashboard missing theme JS"
    fi
done
echo ""

# Check 3: ARIA labels
echo "3. Checking ARIA labels..."
for dashboard in "${DASHBOARDS[@]}"; do
    ARIA_COUNT=$(grep -c "aria-" "public/$dashboard" 2>/dev/null || echo "0")
    if [ "$ARIA_COUNT" -gt 0 ]; then
        echo "✅ $dashboard has $ARIA_COUNT ARIA attributes"
    else
        echo "⚠️  $dashboard has no ARIA attributes"
    fi
done
echo ""

# Check 4: Skip link
echo "4. Checking skip navigation links..."
for dashboard in "${DASHBOARDS[@]}"; do
    if grep -q "skip-link\|skip-to-content" "public/$dashboard"; then
        echo "✅ $dashboard has skip link"
    else
        echo "❌ $dashboard missing skip link"
    fi
done
echo ""

# Check 5: Screen reader announcements
echo "5. Checking screen reader support..."
for dashboard in "${DASHBOARDS[@]}"; do
    if grep -q "aria-live\|role=" "public/$dashboard"; then
        echo "✅ $dashboard has screen reader support"
    else
        echo "⚠️  $dashboard needs screen reader support"
    fi
done
echo ""

echo "================================"
echo "Manual testing required:"
echo "- Test with screen reader (NVDA/JAWS/VoiceOver)"
echo "- Test keyboard navigation (Tab, Enter, Esc)"
echo "- Test color contrast with browser tools"
echo "- Test with high contrast mode"
echo "- Test with reduced motion enabled"
echo ""
