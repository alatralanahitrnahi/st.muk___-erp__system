# Frontend Cleanup Execution Plan

**Status**: Ready for Implementation  
**Estimated Time**: 4-6 hours  
**Risk Level**: Medium (with mitigations)

---

## Phase 1: Critical Path (P0) - 2 hours

### 1.1 Login Consolidation (30 min)

**Action**: Single login entry point

**Steps**:
1. Keep `secure_login.html` as primary
2. Create redirect pages for old URLs
3. Update all logout links

**Files to Modify**:
- `secure_admin.html` - Already correct ✅
- `secure_faculty.html` - Already correct ✅
- `secure_principal.html` - Already correct ✅
- `secure_super_admin.html` - Already correct ✅
- `secure_student.html` - Fix redirect (login.html → secure_login.html)

**Files to Create**:
- `login.html` - Redirect to secure_login.html
- `index.html` - Redirect to secure_login.html

**Risk**: LOW - Simple redirects  
**Rollback**: Keep old files, remove redirects

---

### 1.2 Delete Duplicate Pages (15 min)

**Action**: Remove 8 duplicate/legacy pages

**Delete List**:
```bash
# Legacy dashboards (pre-department architecture)
rm public/admin.html
rm public/principal.html  
rm public/faculty.html
rm public/student.html

# Old versions
rm public/faculty_old.html

# Duplicates
rm public/secure_principal_optimized.html

# Test pages
rm public/test.html
```

**Backup First**:
```bash
mkdir -p backup/legacy-pages
cp public/{admin,principal,faculty,student,faculty_old}.html backup/legacy-pages/
cp public/secure_principal_optimized.html backup/legacy-pages/
cp public/test.html backup/legacy-pages/
```

**Risk**: MEDIUM - Users may have bookmarks  
**Mitigation**: Create 410 Gone responses  
**Rollback**: Restore from backup

---

### 1.3 Integrate Department Selector (60 min)

**Action**: Add department selector to all 5 dashboards

**Template to Add** (top of each dashboard):
```html
<!-- Department Selector -->
<div id="department-selector-container"></div>
<script>
    // Load department selector component
    fetch('/components/department-selector.html')
        .then(r => r.text())
        .then(html => {
            document.getElementById('department-selector-container').innerHTML = html;
            initializeDepartmentSelector();
        });
    
    // Initialize with user's primary department
    function initializeDepartmentSelector() {
        const userData = JSON.parse(localStorage.getItem('user') || '{}');
        const primaryDept = userData.primary_department_id || 1;
        
        // Set active department
        if (window.setActiveDepartment) {
            window.setActiveDepartment(primaryDept);
        }
        
        // Listen for department changes
        document.addEventListener('departmentChanged', (e) => {
            const newDeptId = e.detail.departmentId;
            reloadDashboardData(newDeptId);
        });
    }
</script>
```

**Files to Modify**:
- `secure_super_admin.html`
- `secure_principal.html`
- `secure_admin.html`
- `secure_faculty.html`
- `secure_student.html`

**Risk**: MEDIUM - May break existing functionality  
**Mitigation**: Test each page after integration  
**Rollback**: Remove added code blocks

---

### 1.4 Session Validation (30 min)

**Action**: Add session check to all dashboards

**Template to Add** (after `<body>` tag):
```html
<script>
    // Session validation
    (function() {
        const user = JSON.parse(localStorage.getItem('user') || 'null');
        const sessionExpiry = localStorage.getItem('session_expiry');
        
        // Check if logged in
        if (!user) {
            window.location.href = '/secure_login.html';
            return;
        }
        
        // Check if session expired
        if (sessionExpiry && Date.now() > parseInt(sessionExpiry)) {
            localStorage.clear();
            alert('Session expired. Please login again.');
            window.location.href = '/secure_login.html';
            return;
        }
        
        // Check role access
        const requiredRole = '[ROLE]'; // Replace per page
        if (user.role !== requiredRole && user.role !== 'super-admin') {
            window.location.href = '/unauthorized.html';
            return;
        }
        
        // Extend session
        const fifteenMinutes = 15 * 60 * 1000;
        localStorage.setItem('session_expiry', Date.now() + fifteenMinutes);
    })();
</script>
```

**Files to Modify**:
- `secure_super_admin.html` - requiredRole: 'super-admin'
- `secure_principal.html` - requiredRole: 'principal'
- `secure_admin.html` - requiredRole: 'registrar'
- `secure_faculty.html` - requiredRole: 'faculty'
- `secure_student.html` - requiredRole: 'student'

**Risk**: LOW - Improves security  
**Rollback**: Remove validation blocks

---

## Phase 2: High Priority (P1) - 2 hours

### 2.1 API v1 Migration (90 min)

**Action**: Replace mock data with v1 department API calls

**Pattern to Follow**:
```javascript
// OLD (Mock Data)
const mockStudents = [
    { id: 1, name: 'John Doe', program: 'B.Sc CS' }
];
displayStudents(mockStudents);

// NEW (v1 API)
async function loadStudents() {
    const departmentId = getActiveDepartment();
    try {
        const response = await fetch(`/api/v1/departments/${departmentId}/students`);
        if (!response.ok) throw new Error('API Error');
        const data = await response.json();
        displayStudents(data.data);
    } catch (error) {
        console.error('Failed to load students:', error);
        // Fallback to cached data or show error
        showError('Unable to load students. Please try again.');
    }
}
```

**Modules to Migrate** (per dashboard):

**secure_admin.html**:
- Students list → `/api/v1/departments/{id}/students`
- Fees summary → `/api/v1/departments/{id}/fees/summary`
- Reports → `/api/v1/departments/{id}/reports`

**secure_faculty.html**:
- Attendance → `/api/v1/departments/{id}/attendance`
- Lesson plans → `/api/v1/departments/{id}/lesson-plans`
- Results → `/api/v1/departments/{id}/results`

**secure_principal.html**:
- Dashboard stats → `/api/v1/departments/{id}/dashboard`
- Approvals → `/api/v1/departments/{id}/workflows/pending`
- Reports → `/api/v1/departments/{id}/reports`

**secure_student.html**:
- Profile → `/api/v1/students/{id}`
- Attendance → `/api/v1/students/{id}/attendance`
- Results → `/api/v1/students/{id}/results`

**Risk**: HIGH - May break if API not ready  
**Mitigation**: Keep mock data as fallback  
**Rollback**: Revert to mock data

---

### 2.2 Navigation Consistency (30 min)

**Action**: Standardize navigation across all pages

**Create Shared Navigation Component**:
```javascript
// js/navigation.js
function renderNavigation(userRole) {
    const navItems = {
        'super-admin': [
            { label: 'Dashboard', url: '/secure_super_admin.html' },
            { label: 'Users', url: '#users' },
            { label: 'Settings', url: '#settings' }
        ],
        'principal': [
            { label: 'Dashboard', url: '/secure_principal.html' },
            { label: 'Approvals', url: '#approvals' },
            { label: 'Reports', url: '#reports' }
        ],
        'registrar': [
            { label: 'Dashboard', url: '/secure_admin.html' },
            { label: 'Students', url: '#students' },
            { label: 'Fees', url: '#fees' }
        ],
        'faculty': [
            { label: 'Dashboard', url: '/secure_faculty.html' },
            { label: 'Attendance', url: '#attendance' },
            { label: 'Lesson Plans', url: '#lessons' }
        ],
        'student': [
            { label: 'Dashboard', url: '/secure_student.html' },
            { label: 'Attendance', url: '#attendance' },
            { label: 'Results', url: '#results' }
        ]
    };
    
    return navItems[userRole] || [];
}
```

**Files to Modify**: All 5 dashboards  
**Risk**: LOW - Visual changes only  
**Rollback**: Revert navigation code

---

## Phase 3: Medium Priority (P2) - 2 hours

### 3.1 Department Theming (45 min)

**Action**: Apply department colors consistently

**CSS to Add**:
```css
/* Department Theming */
:root {
    --dept-primary: #3b82f6; /* Default blue */
    --dept-secondary: #60a5fa;
    --dept-accent: #2563eb;
}

[data-department="1"] { /* Science */
    --dept-primary: #3b82f6;
    --dept-secondary: #60a5fa;
    --dept-accent: #2563eb;
}

[data-department="2"] { /* Commerce */
    --dept-primary: #10b981;
    --dept-secondary: #34d399;
    --dept-accent: #059669;
}

[data-department="3"] { /* Arts */
    --dept-primary: #8b5cf6;
    --dept-secondary: #a78bfa;
    --dept-accent: #7c3aed;
}

.btn-primary {
    background-color: var(--dept-primary);
}

.text-primary {
    color: var(--dept-primary);
}
```

**JavaScript to Add**:
```javascript
function applyDepartmentTheme(departmentId) {
    document.body.setAttribute('data-department', departmentId);
}

document.addEventListener('departmentChanged', (e) => {
    applyDepartmentTheme(e.detail.departmentId);
});
```

**Risk**: LOW - Visual only  
**Rollback**: Remove CSS variables

---

### 3.2 Error Handling (45 min)

**Action**: Add consistent error handling

**Create Error Handler**:
```javascript
// js/error-handler.js
function showError(message, type = 'error') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.remove(), 5000);
}

function handleAPIError(error, context) {
    console.error(`API Error in ${context}:`, error);
    
    if (error.status === 401) {
        showError('Session expired. Please login again.');
        setTimeout(() => window.location.href = '/secure_login.html', 2000);
    } else if (error.status === 403) {
        showError('You do not have permission to access this resource.');
    } else if (error.status === 404) {
        showError('Resource not found.');
    } else {
        showError('An error occurred. Please try again.');
    }
}
```

**Risk**: LOW - Improves UX  
**Rollback**: Remove error handler

---

### 3.3 WCAG Compliance (30 min)

**Action**: Add accessibility attributes

**Checklist per Page**:
- [ ] Add `lang="en"` to `<html>`
- [ ] Add `alt` text to all images
- [ ] Add `aria-label` to icon buttons
- [ ] Add `role` attributes to custom components
- [ ] Ensure color contrast ratio ≥ 4.5:1
- [ ] Add keyboard navigation support
- [ ] Add focus indicators

**Risk**: LOW - Compliance improvement  
**Rollback**: Remove ARIA attributes

---

## Phase 4: Verification (30 min)

### 4.1 Automated Tests

**Create Verification Script**:
```bash
#!/bin/bash
# verify-frontend-cleanup.sh

echo "Frontend Cleanup Verification"
echo "=============================="

# Check deleted files
echo "Checking deleted files..."
for file in admin.html principal.html faculty.html student.html faculty_old.html secure_principal_optimized.html test.html; do
    if [ -f "public/$file" ]; then
        echo "❌ $file still exists"
    else
        echo "✅ $file deleted"
    fi
done

# Check login redirects
echo -e "\nChecking login redirects..."
grep -r "login.html" public/secure_*.html | grep -v "secure_login.html" && echo "❌ Found incorrect login redirects" || echo "✅ All login redirects correct"

# Check department selector integration
echo -e "\nChecking department selector..."
for file in secure_super_admin.html secure_principal.html secure_admin.html secure_faculty.html secure_student.html; do
    if grep -q "department-selector" "public/$file"; then
        echo "✅ $file has department selector"
    else
        echo "❌ $file missing department selector"
    fi
done

# Check session validation
echo -e "\nChecking session validation..."
for file in secure_super_admin.html secure_principal.html secure_admin.html secure_faculty.html secure_student.html; do
    if grep -q "session_expiry" "public/$file"; then
        echo "✅ $file has session validation"
    else
        echo "❌ $file missing session validation"
    fi
done

# Check API v1 usage
echo -e "\nChecking API v1 endpoints..."
grep -r "api/v1" public/secure_*.html | wc -l | xargs echo "API v1 calls found:"

echo -e "\n=============================="
echo "Verification complete"
```

**Risk**: NONE - Read-only checks

---

## Implementation Order

### Day 1 - Critical Path
1. ✅ Backup all files
2. ✅ Delete duplicate pages
3. ✅ Create login redirects
4. ✅ Fix logout links
5. ✅ Integrate department selector
6. ✅ Add session validation
7. ✅ Test basic navigation

### Day 2 - High Priority
1. ✅ Migrate to API v1 endpoints
2. ✅ Add error handling
3. ✅ Standardize navigation
4. ✅ Test all dashboards

### Day 3 - Polish
1. ✅ Apply department theming
2. ✅ WCAG compliance
3. ✅ Performance optimization
4. ✅ Final testing

---

## Rollback Plan

### If Critical Issues Found

**Step 1**: Stop deployment
```bash
git stash
```

**Step 2**: Restore backup
```bash
cp -r backup/legacy-pages/* public/
```

**Step 3**: Revert changes
```bash
git reset --hard HEAD
```

**Step 4**: Investigate issue

**Step 5**: Fix and retry

---

## Success Criteria

### Must Pass Before Deployment

- [ ] Only `secure_login.html` accessible for login
- [ ] All logout links go to `secure_login.html`
- [ ] Department selector visible on all 5 dashboards
- [ ] Department switching works (<300ms)
- [ ] Session validation on all pages
- [ ] No 404 errors on navigation
- [ ] At least 50% of modules using API v1
- [ ] No console errors on page load
- [ ] Mobile responsive (viewport test)
- [ ] WCAG 2.1 AA compliance (basic)

### Performance Targets

- [ ] Page load time <2s
- [ ] Department switch <300ms
- [ ] API response <500ms
- [ ] No memory leaks (10min test)

---

## Risk Matrix

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Broken bookmarks | High | Low | Add redirects |
| API not ready | Medium | High | Keep mock fallback |
| Session issues | Low | Medium | Test thoroughly |
| Performance degradation | Low | Medium | Monitor metrics |
| WCAG failures | Medium | Low | Incremental fixes |

---

## Timeline

**Total Estimated Time**: 4-6 hours  
**Recommended Schedule**: 2-3 days with testing  
**Team Size**: 1-2 developers  
**QA Time**: 2 hours

---

## Next Steps

1. Review and approve this plan
2. Create feature branch: `feature/frontend-cleanup`
3. Execute Phase 1 (Critical Path)
4. Test and verify
5. Execute Phase 2 (High Priority)
6. Test and verify
7. Execute Phase 3 (Polish)
8. Final verification
9. Merge to main
10. Deploy to production

---

**Plan Status**: ✅ Ready for Execution  
**Approval Required**: Yes  
**Estimated Completion**: 2-3 days
