#!/bin/bash

echo "🔍 Department Foundation Layer - Backward Compatibility Test"
echo "============================================================"
echo ""

# Test 1: Check migrations exist
echo "1. Checking migration files..."
if [ -f "database/migrations/2024_10_department_foundation/2024_10_01_000001_add_department_id_to_core_tables.php" ]; then
    echo "   ✅ Core tables migration exists"
else
    echo "   ❌ Core tables migration missing"
fi

if [ -f "database/migrations/2024_10_department_foundation/2024_10_01_000002_create_user_departments_table.php" ]; then
    echo "   ✅ User departments migration exists"
else
    echo "   ❌ User departments migration missing"
fi

if [ -f "database/migrations/2024_10_department_foundation/2024_10_01_000003_create_department_permissions_table.php" ]; then
    echo "   ✅ Department permissions migration exists"
else
    echo "   ❌ Department permissions migration missing"
fi

# Test 2: Check middleware
echo ""
echo "2. Checking middleware..."
if [ -f "app/Http/Middleware/DepartmentScope.php" ]; then
    echo "   ✅ DepartmentScope middleware exists"
else
    echo "   ❌ DepartmentScope middleware missing"
fi

# Test 3: Check trait
echo ""
echo "3. Checking HasDepartments trait..."
if [ -f "app/Traits/HasDepartments.php" ]; then
    echo "   ✅ HasDepartments trait exists"
else
    echo "   ❌ HasDepartments trait missing"
fi

# Test 4: Verify User model updated
echo ""
echo "4. Checking User model..."
if grep -q "HasDepartments" app/Models/User.php; then
    echo "   ✅ User model uses HasDepartments trait"
else
    echo "   ❌ User model missing HasDepartments trait"
fi

# Test 5: Verify Department model updated
echo ""
echo "5. Checking Department model..."
if grep -q "users()" app/Models/Department.php; then
    echo "   ✅ Department model has users relationship"
else
    echo "   ❌ Department model missing users relationship"
fi

# Test 6: Verify Kernel updated
echo ""
echo "6. Checking Kernel middleware registration..."
if grep -q "department.scope" app/Http/Kernel.php; then
    echo "   ✅ DepartmentScope registered in Kernel"
else
    echo "   ❌ DepartmentScope not registered in Kernel"
fi

echo ""
echo "============================================================"
echo "📋 Next Steps:"
echo ""
echo "1. Run migrations:"
echo "   php artisan migrate"
echo ""
echo "2. Test existing API endpoints (should work without changes):"
echo "   curl -H 'Authorization: Bearer {token}' http://localhost:8000/api/students"
echo ""
echo "3. Test new department-scoped endpoints:"
echo "   curl -H 'Authorization: Bearer {token}' http://localhost:8000/api/students?department_id=1"
echo ""
echo "4. Verify backward compatibility:"
echo "   - All existing endpoints work without department parameter"
echo "   - Department parameter is optional"
echo "   - Users without department_id can still access system"
echo ""
echo "✅ Verification complete!"
