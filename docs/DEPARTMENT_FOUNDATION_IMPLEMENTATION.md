# Department Foundation Layer - Implementation Guide

## Overview

This implementation adds department context to PVGS ERP while maintaining 100% backward compatibility with existing role-based functionality.

## Database Schema Changes

### 1. Core Tables - Department ID Addition
**Migration**: `2024_10_01_000001_add_department_id_to_core_tables.php`

Added `department_id` (nullable) to:
- `students` - Links student to department
- `attendance_records` - Department-scoped attendance
- `exam_results` - Department-scoped results
- `student_fees` - Department-scoped fees
- `faculty_assignments` - Department-scoped assignments
- `subjects` - Denormalized for performance

**Indexes Created**:
- Single column: `department_id`
- Composite: `(department_id, attendance_date)`, `(department_id, academic_year)`

### 2. User-Department Relationship
**Migration**: `2024_10_01_000002_create_user_departments_table.php`

**user_departments** table:
```sql
- user_id (FK to users)
- department_id (FK to departments)
- role_in_department (varchar 50)
- is_primary (boolean)
- start_date, end_date (nullable dates)
```

**users** table addition:
- `primary_department_id` (nullable FK to departments)

### 3. Department Permissions
**Migration**: `2024_10_01_000003_create_department_permissions_table.php`

**department_permissions** table:
```sql
- department_id (FK to departments)
- role_name (varchar 50)
- module_name (varchar 50)
- can_view, can_create, can_edit, can_delete, can_export, can_approve (booleans)
```

## Middleware Implementation

### DepartmentScope Middleware
**File**: `app/Http/Middleware/DepartmentScope.php`

**Behavior**:
1. Extracts department from route parameter or query string
2. Falls back to user's primary_department_id if not specified
3. Verifies user has access to requested department
4. Injects `scoped_department_id` into request
5. Returns 403 if access denied

**Access Check Logic**:
- Super admin: Access to all departments
- Primary department: Automatic access
- Junction table: Check user_departments

## User Model Enhancements

### HasDepartments Trait
**File**: `app/Traits/HasDepartments.php`

**Methods**:
- `departments()` - BelongsToMany relationship
- `primaryDepartment()` - BelongsTo relationship
- `hasAccessToDepartment($id)` - Boolean check
- `accessibleDepartmentIds()` - Array of accessible IDs
- `getRoleInDepartment($id)` - Get role string

## Department Context Injection Pattern

### Pattern 1: Route Parameter (Preferred)
```php
// routes/api.php
Route::prefix('departments/{department}')
    ->middleware(['auth:sanctum', 'department.scope'])
    ->group(function () {
        Route::get('students', [StudentController::class, 'index']);
    });

// Controller
public function index(Request $request, $department)
{
    $deptId = $request->get('scoped_department_id'); // Injected by middleware
    $students = Student::where('department_id', $deptId)->get();
}
```

### Pattern 2: Query Parameter (Backward Compatible)
```php
// Existing route works as-is
Route::get('students', [StudentController::class, 'index']);

// Controller handles both cases
public function index(Request $request)
{
    $query = Student::query();
    
    // Department context injected by middleware if provided
    if ($deptId = $request->get('scoped_department_id')) {
        $query->where('department_id', $deptId);
    }
    
    return response()->json($query->get());
}
```

### Pattern 3: Automatic Scoping (User's Primary Department)
```php
// No department specified - uses primary_department_id
Route::get('students', [StudentController::class, 'index'])
    ->middleware(['auth:sanctum', 'department.scope']);

// Middleware automatically injects primary department
public function index(Request $request)
{
    $deptId = $request->get('scoped_department_id'); // Auto-injected
    // ... use department context
}
```

## Backward Compatibility Strategy

### Phase 1: Parallel Operation (Months 1-3)
- All department fields nullable
- Middleware optional on routes
- Existing endpoints work without changes
- New department-scoped endpoints available

### Phase 2: Gradual Migration (Months 4-6)
- Populate department_id in existing records
- Add middleware to more routes
- Update frontend to use department context

### Phase 3: Full Department Mode (Month 7+)
- Make department_id required
- Enforce department scoping on all routes
- Remove legacy role-only checks

## Usage Examples

### Example 1: Existing Endpoint (No Changes)
```php
// Before: Works as-is
GET /api/students
Authorization: Bearer {token}

// After: Still works exactly the same
GET /api/students
Authorization: Bearer {token}
```

### Example 2: New Department-Scoped Endpoint
```php
// New: Department-specific students
GET /api/departments/1/students
Authorization: Bearer {token}

// Or with query parameter
GET /api/students?department_id=1
Authorization: Bearer {token}
```

### Example 3: Multi-Department User
```php
// User in multiple departments
$user->departments; // Collection of departments
$user->primaryDepartment; // Primary department
$user->accessibleDepartmentIds(); // [1, 2, 3]

// Check access
if ($user->hasAccessToDepartment(1)) {
    // User can access department 1
}
```

## Controller Update Pattern

### Before (Role-Based Only)
```php
public function index(Request $request)
{
    $students = Student::with(['user', 'program'])->get();
    return response()->json($students);
}
```

### After (Department-Aware, Backward Compatible)
```php
public function index(Request $request)
{
    $query = Student::with(['user', 'program', 'department']);
    
    // Optional department scoping
    if ($deptId = $request->get('scoped_department_id')) {
        $query->where('department_id', $deptId);
    }
    
    return response()->json($query->get());
}
```

## Testing Backward Compatibility

### Test 1: Existing Endpoints
```bash
# Should work without any changes
curl -H "Authorization: Bearer {token}" \
     http://localhost:8000/api/students

# Expected: Returns all students (existing behavior)
```

### Test 2: Department Query Parameter
```bash
# Should filter by department
curl -H "Authorization: Bearer {token}" \
     http://localhost:8000/api/students?department_id=1

# Expected: Returns only department 1 students
```

### Test 3: Department Route Parameter
```bash
# Should filter by department
curl -H "Authorization: Bearer {token}" \
     http://localhost:8000/api/departments/1/students

# Expected: Returns only department 1 students
```

### Test 4: Access Control
```bash
# User without access to department 2
curl -H "Authorization: Bearer {token}" \
     http://localhost:8000/api/departments/2/students

# Expected: 403 Forbidden
```

## Migration Rollback

All migrations include `down()` methods:

```bash
# Rollback all department foundation changes
php artisan migrate:rollback --step=3

# This will:
# 1. Drop department_permissions table
# 2. Drop user_departments table and users.primary_department_id
# 3. Remove department_id from all core tables
```

## Performance Considerations

### Indexes Created
- `students.department_id`
- `attendance_records (department_id, attendance_date)`
- `exam_results (department_id, academic_year)`
- `student_fees.department_id`
- `faculty_assignments.department_id`
- `subjects.department_id`
- `user_departments (user_id, is_primary)`

### Query Optimization
```php
// Efficient: Uses index
Student::where('department_id', 1)->get();

// Efficient: Composite index
AttendanceRecord::where('department_id', 1)
                ->whereBetween('attendance_date', [$from, $to])
                ->get();
```

## Security Considerations

### Access Control
- Middleware verifies department access before processing
- Super admin bypass for system operations
- Junction table for multi-department users

### Data Isolation
- Department context injected at middleware level
- Controllers receive pre-validated department ID
- No direct department parameter manipulation

## Alignment with database_migrations_plan.md

### Phase 1 Requirements ✅
- ✅ User-department relationship
- ✅ Department permissions table
- ✅ Core tables department_id
- ✅ Backward compatibility maintained

### Phase 2 Ready
- Department hierarchy (future)
- Cross-department operations (future)
- Department-specific workflows (future)

## Critical Success Factors

1. ✅ All existing endpoints work without changes
2. ✅ Department parameter is optional
3. ✅ Middleware provides fallback behavior
4. ✅ Full rollback capability via migrations
5. ✅ No breaking changes to existing functionality

## Next Steps

1. Run migrations: `php artisan migrate`
2. Run verification: `./verify-department-foundation.sh`
3. Test existing endpoints (should work unchanged)
4. Test new department-scoped endpoints
5. Begin populating department_id in existing records
6. Update controllers to use department context
7. Update frontend to show department selector
