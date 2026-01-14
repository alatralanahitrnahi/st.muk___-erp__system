# Navigation Naming Standards - PVGS ERP System

## Role Terminology (MANDATORY)

| ✅ Correct | ❌ Incorrect | Context |
|-----------|-------------|---------|
| Super Admin | System Admin, Root, Superuser | User role |
| Principal | Head, Director | User role |
| Registrar | Admin, Administrator | User role |
| Faculty | Teacher, Staff, Instructor | User role |
| Student | Learner, Pupil | User role |

## Module Naming Convention

### Format: `kebab-case`

**Pattern:** All lowercase, words separated by hyphens

✅ **Correct Examples:**
- `academic-structure`
- `lesson-plans`
- `student-section`
- `teaching-schedule`
- `front-office`

❌ **Incorrect Examples:**
- `academicStructure` (camelCase)
- `lesson_plans` (snake_case)
- `StudentSection` (PascalCase)
- `FRONT-OFFICE` (UPPER-KEBAB-CASE)

### Module ID Rules

1. **Must match HTML section ID exactly**
   ```html
   <div id="lesson-plans" class="section">
   ```

2. **Must match navigation config key**
   ```javascript
   'lesson-plans': { label: 'Lesson Plans', icon: '📝' }
   ```

3. **Must be used in onclick handler**
   ```html
   <div class="nav-item" onclick="showSection('lesson-plans')">
   ```

## Section Label Standards

### Display Names (Title Case)

✅ **Correct:**
- Dashboard
- Student Records
- Fee Management
- Lesson Plans
- NAAC Reports

❌ **Incorrect:**
- DASHBOARD (all caps)
- student records (lowercase)
- Fee management (inconsistent case)
- Lesson-Plans (hyphenated display)

## Icon Standards

### Emoji Icons (Preferred)

| Module Category | Icon | Examples |
|----------------|------|----------|
| Core | 📊 | Dashboard, Overview |
| System | ⚙️ 🔐 💾 | Settings, Security, Database |
| Academic | 📚 🎓 📝 | Programs, Students, Lessons |
| Financial | 💰 💳 | Fees, Payments |
| Reports | 📋 📊 | NAAC Reports, Analytics |
| Teaching | 👨🏫 📚 🕐 | Classes, Timetable |
| Personal | 👤 🏖️ | Profile, Leave |
| Services | 📖 ⚽ | Library, Sports |

## Section Title Standards

### Format: `{Icon} {Title}`

✅ **Correct:**
```html
<h2>📊 Dashboard</h2>
<h2>🎓 Student Records</h2>
<h2>💰 Fee Management</h2>
```

❌ **Incorrect:**
```html
<h2>Dashboard 📊</h2>  <!-- Icon at end -->
<h2>STUDENT RECORDS</h2>  <!-- All caps, no icon -->
<h2>fee-management</h2>  <!-- Kebab case in display -->
```

## Navigation Section Grouping

### Section Title Format: Title Case

✅ **Correct:**
- System Management
- Student Operations
- Teaching
- Personal

❌ **Incorrect:**
- SYSTEM MANAGEMENT (all caps)
- student operations (lowercase)
- Teaching_Section (underscore)

## File Naming Standards

### Dashboard Files

**Pattern:** `secure_{role}.html`

✅ **Correct:**
- `secure_super_admin.html`
- `secure_principal.html`
- `secure_registrar.html` (not admin)
- `secure_faculty.html`
- `secure_student.html`

❌ **Incorrect:**
- `admin.html` (not secure)
- `secure-admin.html` (hyphen instead of underscore)
- `secureAdmin.html` (camelCase)

## JavaScript Variable Naming

### Navigation Config

```javascript
// ✅ Correct
const NavigationConfig = { ... }
const userRole = 'registrar';
const moduleKey = 'lesson-plans';

// ❌ Incorrect
const navConfig = { ... }
const user_role = 'admin';
const ModuleKey = 'lessonPlans';
```

## API Endpoint Standards

### Pattern: `/api/{resource}/{action}`

✅ **Correct:**
- `/api/students`
- `/api/lesson-plans`
- `/api/attendance/mark`

❌ **Incorrect:**
- `/api/student` (singular)
- `/api/lessonPlans` (camelCase)
- `/api/Attendance/Mark` (PascalCase)

## CSS Class Standards

### Navigation Classes

```css
/* ✅ Correct */
.nav-section
.nav-section-title
.nav-item
.nav-item.active

/* ❌ Incorrect */
.navSection
.nav_item
.NavigationItem
```

## Validation Checklist

Before committing navigation changes:

- [ ] All section IDs use kebab-case
- [ ] Role names use correct terminology (Registrar not Admin)
- [ ] Display labels use Title Case
- [ ] Icons are consistent with category
- [ ] Navigation config matches HTML sections
- [ ] File names follow secure_{role}.html pattern
- [ ] JavaScript variables use camelCase
- [ ] CSS classes use kebab-case
- [ ] API endpoints use kebab-case

## Quick Reference

```javascript
// Complete example of correct naming
const NavigationConfig = {
    modules: {
        'lesson-plans': {  // kebab-case ID
            label: 'Lesson Plans',  // Title Case display
            icon: '📝',  // Emoji icon
            category: 'teaching'  // lowercase category
        }
    },
    roles: {
        'registrar': {  // NOT 'admin'
            sections: [
                {
                    title: 'Student Operations',  // Title Case
                    items: ['admissions', 'students', 'documents']
                }
            ]
        }
    }
};
```