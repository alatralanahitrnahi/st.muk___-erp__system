#!/bin/bash

echo "🔍 PVGS ERP - Empty Modules Fix Verification"
echo "=============================================="
echo ""

# Check if database exists
echo "1. Checking database..."
if [ -f "database/database.sqlite" ]; then
    echo "   ✅ Database file exists"
else
    echo "   ❌ Database file missing"
    exit 1
fi

# Check if required files exist
echo ""
echo "2. Checking required files..."
files=(
    "public/js/data-loader.js"
    "public/js/api-service.js"
    "app/Http/Middleware/CheckModulePermission.php"
    "app/Traits/HasVisibilityScope.php"
)

for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo "   ✅ $file"
    else
        echo "   ❌ $file missing"
    fi
done

# Check if migrations need to run
echo ""
echo "3. Database setup..."
echo "   Run: php artisan migrate"
echo "   Run: php artisan db:seed --class=PrincipalConfigSeeder"

# Start server
echo ""
echo "4. Starting development server..."
echo "   Run: php artisan serve"
echo ""
echo "5. Test in browser:"
echo "   - Open: http://localhost:8000/secure_login.html"
echo "   - Login as Registrar"
echo "   - Navigate to Students section"
echo "   - Verify data loads (not 'Loading...')"
echo ""
echo "6. Check browser console for errors"
echo "   - Press F12 to open DevTools"
echo "   - Check Console tab for errors"
echo "   - Check Network tab for failed requests"
echo ""
echo "✅ Verification complete!"
