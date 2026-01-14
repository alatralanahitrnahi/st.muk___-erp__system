#!/bin/bash

echo "🔍 Department Foundation - Comprehensive Verification"
echo "===================================================="
echo ""

PASS=0
FAIL=0

test_check() {
    if [ $1 -eq 0 ]; then
        echo "   ✅ $2"
        ((PASS++))
    else
        echo "   ❌ $2"
        ((FAIL++))
    fi
}

echo "1. Checking migration files..."
test -f "database/migrations/2024_10_department_foundation/2024_10_01_000001_add_department_id_to_core_tables.php"
test_check $? "Core tables migration exists"

test -f "database/migrations/2024_10_department_foundation/2024_10_01_000002_create_user_departments_table.php"
test_check $? "User departments migration exists"

test -f "database/migrations/2024_10_department_foundation/2024_10_01_000003_create_department_permissions_table.php"
test_check $? "Department permissions migration exists"

test -f "database/migrations/2024_10_department_foundation/2024_10_01_000004_create_workflow_history_table.php"
test_check $? "Workflow history migration exists"

echo ""
echo "2. Checking middleware..."
test -f "app/Http/Middleware/DepartmentScope.php"
test_check $? "DepartmentScope middleware exists"

grep -q "department.scope" app/Http/Kernel.php
test_check $? "DepartmentScope registered in Kernel"

echo ""
echo "3. Checking traits..."
test -f "app/Traits/HasDepartments.php"
test_check $? "HasDepartments trait exists"

test -f "app/Traits/HasWorkflow.php"
test_check $? "HasWorkflow trait exists"

echo ""
echo "4. Checking User model..."
grep -q "HasDepartments" app/Models/User.php
test_check $? "User model uses HasDepartments trait"

grep -q "primary_department_id" app/Models/User.php
test_check $? "User model has primary_department_id"

echo ""
echo "5. Checking Department model..."
grep -q "users()" app/Models/Department.php
test_check $? "Department model has users relationship"

grep -q "students()" app/Models/Department.php
test_check $? "Department model has students relationship"

echo ""
echo "6. Checking services..."
test -f "app/Services/WorkflowService.php"
test_check $? "WorkflowService exists"

test -f "config/workflows.php"
test_check $? "Workflow configuration exists"

echo ""
echo "7. Checking frontend components..."
test -f "public/js/department-navigation.js"
test_check $? "Department navigation exists"

test -f "public/components/department-selector.html"
test_check $? "Department selector component exists"

grep -q "departmentId" public/js/data-loader.js
test_check $? "Data loader has department parameter"

echo ""
echo "===================================================="
echo "Test Results: $PASS passed, $FAIL failed"
echo "===================================================="
echo ""

if [ $FAIL -eq 0 ]; then
    echo "✅ All verification checks passed!"
    echo ""
    echo "Next steps:"
    echo "1. Run: ./run-department-migrations.sh"
    echo "2. Test existing endpoints (should work unchanged)"
    echo "3. Test new department-scoped endpoints"
    exit 0
else
    echo "❌ Some verification checks failed"
    echo "Please review the failures above"
    exit 1
fi
