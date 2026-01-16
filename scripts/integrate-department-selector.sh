#!/bin/bash

# Automated Department Selector & Session Integration Script
# Integrates department selector and session validation into all 5 dashboards

echo "🔧 Integrating Department Selector & Session Validation"
echo "========================================================"
echo ""

# Define dashboards and their required roles
declare -A DASHBOARDS=(
    ["secure_super_admin.html"]="super-admin"
    ["secure_principal.html"]="principal"
    ["secure_admin.html"]="registrar"
    ["secure_faculty.html"]="faculty"
    ["secure_student.html"]="student"
)

# Backup current files
echo "📦 Creating backup..."
mkdir -p backup/pre-integration-$(date +%Y%m%d-%H%M%S)
for dashboard in "${!DASHBOARDS[@]}"; do
    cp "public/$dashboard" "backup/pre-integration-$(date +%Y%m%d-%H%M%S)/"
done
echo "✅ Backup created"
echo ""

# Integration code snippets
read -r -d '' SESSION_VALIDATION << 'EOF'
    <!-- Session Validation & Department Integration -->
    <script src="/js/session-department.js"></script>
    <script>
        // Validate session on page load
        if (!validateSession('ROLE_PLACEHOLDER')) {
            // Redirect handled by validateSession
        }
        
        // Initialize department selector
        document.addEventListener('DOMContentLoaded', function() {
            initDepartmentSelector();
        });
    </script>
EOF

read -r -d '' DEPT_SELECTOR_CONTAINER << 'EOF'
        <!-- Department Selector -->
        <div id="department-selector-container" style="margin-bottom: 1rem;"></div>
EOF

# Process each dashboard
for dashboard in "${!DASHBOARDS[@]}"; do
    ROLE="${DASHBOARDS[$dashboard]}"
    echo "Processing $dashboard (role: $ROLE)..."
    
    FILE="public/$dashboard"
    
    # Check if already integrated
    if grep -q "session-department.js" "$FILE"; then
        echo "  ⚠️  Already integrated, skipping..."
        continue
    fi
    
    # Create temp file
    TEMP_FILE=$(mktemp)
    
    # Add session validation after <body> tag
    sed "/<body>/a\\
$(echo "$SESSION_VALIDATION" | sed "s/ROLE_PLACEHOLDER/$ROLE/g")
" "$FILE" > "$TEMP_FILE"
    
    # Add department selector container after header
    sed -i '/<div class="container">/i\
'"$DEPT_SELECTOR_CONTAINER"'
' "$TEMP_FILE"
    
    # Replace original file
    mv "$TEMP_FILE" "$FILE"
    
    echo "  ✅ Integrated successfully"
done

echo ""
echo "========================================================"
echo "✅ Integration Complete!"
echo ""
echo "Next steps:"
echo "1. Test each dashboard in browser"
echo "2. Run verification: bash scripts/verify-frontend-cleanup.sh"
echo "3. Check department selector appears and works"
echo ""
