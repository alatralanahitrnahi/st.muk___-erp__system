# PVGS ERP System - Comprehensive Architecture Assessment

## 1. System Architecture Assessment

### Current Module Organization

#### File Structure Analysis
```
app/
├── Http/Controllers/Api/
│   ├── Academic/          # Empty subdirectory
│   ├── Attendance/        # Empty subdirectory
│   ├── Examination/       # Empty subdirectory
│   ├── Financial/         # Empty subdirectory
│   └── *.php             # 19 controllers at root level
├── Models/
│   ├── Academic/          # Empty subdirectory
│   ├── Attendance/        # Empty subdirectory
│   ├── Examination/       # Empty subdirectory
│   ├── Financial/         # Empty subdirectory
│   ├── User/              # Empty subdirectory
│   └── *.php             # 24 models at root level
├── Services/
│   └── *.php             # 10 services at root level
└── Repositories/          # Empty directory
```

**CRITICAL ISSUE**: Subdirectories exist but are empty. All files at root level.

#### Naming Conventions
- ✅ Controllers: `{Entity}Controller.php` (consistent)
- ✅ Models: `{Entity}.php` (consistent)
- ✅ Services: `{Entity}Service.php` (consistent)
- ❌ No namespace organization despite subdirectories

### Business Logic Distribution

#### Anti-Pattern #1: Fat Controllers
**Location**: `app/Http/Controllers/Api/StudentController.php`
```php
public function index(Request $request)
{
    $query = Student::with(['user', 'program', 'category']);
    
    if ($request->user()->user_type !== 'super-admin') {
        $query->visibleTo($request->user());
    }
    
    $students = $query->get();
    return response()->json($students);
}
```
**Issue**: Business logic (visibility filtering) in controller instead of service layer.

#### Anti-Pattern #2: Service Layer Inconsistency
**Services with logic**: `FeeService.php`, `AttendanceService.php`
**Controllers without services**: `StudentController`, `ExamController`, `ReportController`

**Example - FeeService.php**:
```php
public function calculateFees(Student $student)
{
    $feeStructure = FeeStructure::where('program_id', $student->program_id)
        ->where('category_id', $student->category_id)
        ->first();

    if ($feeStructure) {
        $totalFee = $feeStructure->total_fee;
        if ($student->scholarship_applied) {
            $totalFee -= $feeStructure->scholarship_amount ?? 0; // ISSUE: Field doesn't exist
        }
        return $totalFee;
    }
    return 0;
}
```
**Issue**: References non-existent `scholarship_amount` field.

#### Anti-Pattern #3: Model Bloat
**Location**: `app/Models/User.php`
```php
public function permissions()
{
    return $this->roles()->with('permissions')->get()->pluck('permissions')->flatten();
}

public function hasPermission($permission)
{
    return $this->roles()->whereHas('permissions', function ($query) use ($permission) {
        $query->where('name', $permission);
    })->exists();
}
```
**Issue**: Permission logic in User model instead of dedicated service.

### Dependency Injection Patterns

#### Current State: Minimal DI Usage
**Controllers**: No constructor injection
```php
class StudentController extends Controller
{
    // No dependencies injected
    public function index(Request $request) { }
}
```

**Services**: Manual instantiation
```php
// In controllers:
$feeService = new FeeService();
$feeService->calculateFees($student);
```

**MISSING**: Service container bindings in `app/Providers/AppServiceProvider.php`

### API Versioning Strategy

#### Current State: NO VERSIONING
**Routes**: `routes/api.php`
```php
Route::post('/register', [AuthController::class, 'register']);
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('students', StudentController::class);
});
```

**Issues**:
- No `/v1/` prefix
- No version-specific controllers
- Breaking changes would affect all clients
- No deprecation strategy

### Department-Based Implementation Blockers

#### Blocker #1: No Department Context in User Model
**Current**: `users` table has no `department_id` column
```php
// User.php - Missing department relationship
protected $fillable = ['name', 'email', 'phone', 'password', 'user_type', 'is_active'];
```

#### Blocker #2: Role-Based Instead of Department-Based Permissions
**Current**: Permissions tied to roles, not departments
```php
// RoleMiddleware.php
public function handle(Request $request, Closure $next, string $role): Response
{
    if (!$request->user()->hasRole($role)) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    return $next($request);
}
```

#### Blocker #3: No Department Scoping in Queries
**Current**: Controllers don't filter by department
```php
// StudentController.php
$students = Student::with(['user', 'program', 'category'])->get();
// Returns ALL students, not department-specific
```

#### Blocker #4: Department Model Lacks Relationships
**Current**: `Department.php`
```php
class Department extends Model
{
    protected $fillable = ['name', 'code', 'description', 'is_active'];
    
    public function programs() {
        return $this->hasMany(Program::class);
    }
    // MISSING: users(), faculty(), students(), subjects()
}
```


## 2. Role & Permission System Deep Dive

### Permission Definitions Location

#### Database Tables
```
roles                    # id, name, guard_name
permissions              # id, name, guard_name
role_permissions         # role_id, permission_id
user_roles              # user_id, role_id
module_permissions      # module_name, role_name, can_view, can_create, etc.
visibility_rules        # module_name, role_name, rule_type, rule_config
approval_chains         # workflow_name, module_name, step_order, approver_role
```

**CRITICAL GAP**: No `department_permissions` table

#### Config Files
**Location**: None found
**Expected**: `config/permissions.php` - MISSING

### Permission Checking Mechanisms

#### Method #1: Middleware (Role-Based)
**File**: `app/Http/Middleware/RoleMiddleware.php`
```php
public function handle(Request $request, Closure $next, string $role): Response
{
    if (!$request->user()->hasRole($role)) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    return $next($request);
}
```
**Usage**: `Route::middleware('role:principal')`
**Issue**: Hardcoded role names, no department context

#### Method #2: Module Permission Middleware
**File**: `app/Http/Middleware/CheckModulePermission.php`
```php
public function handle(Request $request, Closure $next, $module, $action = 'view')
{
    $user = $request->user();
    $role = $user->user_type;
    
    $permission = DB::table('module_permissions')
        ->where('module_name', $module)
        ->where('role_name', $role)
        ->first();
    
    $canPerform = match($action) {
        'view' => $permission->can_view,
        'create' => $permission->can_create,
        // ...
    };
    
    if (!$canPerform) {
        return response()->json(['error' => "No permission"], 403);
    }
    return $next($request);
}
```
**Issue**: No department filtering

#### Method #3: Model-Level (hasPermission)
**File**: `app/Models/User.php`
```php
public function hasPermission($permission)
{
    return $this->roles()->whereHas('permissions', function ($query) use ($permission) {
        $query->where('name', $permission);
    })->exists();
}
```
**Issue**: N+1 query problem, no caching

#### Method #4: Visibility Scope Trait
**File**: `app/Traits/HasVisibilityScope.php`
```php
public function scopeVisibleTo(Builder $query, $user)
{
    if ($user->user_type === 'super-admin') {
        return $query;
    }
    
    $rules = DB::table('visibility_rules')
        ->where('module_name', $modelName . 's')
        ->where('role_name', $role)
        ->get();
    
    foreach ($rules as $rule) {
        $this->applyScope($query, $config['scope'], $user);
    }
}
```
**Issue**: Role-based, not department-based

### Role Inheritance Structure

#### Current: Flat Structure (No Inheritance)
```
super-admin  (all permissions)
principal    (executive permissions)
registrar    (administrative permissions)
faculty      (teaching permissions)
student      (self-service permissions)
```

**MISSING**: Department hierarchy
```
Expected:
├── super-admin
├── principal
│   ├── department-head (Science)
│   ├── department-head (Commerce)
│   └── department-head (Arts)
├── registrar
│   ├── department-registrar (Science)
│   └── department-registrar (Commerce)
└── faculty
    ├── faculty (Science - Physics)
    └── faculty (Commerce - Accounting)
```

### Permission Caching Strategy

#### Current State: NO CACHING
**Evidence**: No cache calls in permission checks
```php
// User.php - No caching
public function hasPermission($permission)
{
    return $this->roles()->whereHas('permissions', function ($query) use ($permission) {
        $query->where('name', $permission);
    })->exists(); // Direct DB query every time
}
```

**Performance Impact**: Every permission check = 2-3 DB queries

### Hardcoded vs Dynamic Permissions

#### Hardcoded Locations

**1. Route Middleware**
```php
// routes/api.php
Route::prefix('config')->middleware('role:principal')->group(function() {
    // Hardcoded 'principal' role
});
```

**2. Controller Logic**
```php
// StudentController.php
if ($request->user()->user_type !== 'super-admin') {
    // Hardcoded 'super-admin' check
}
```

**3. Frontend Navigation**
```javascript
// navigation-config.js
roles: {
    'super-admin': { sections: [...] },
    'principal': { sections: [...] },
    'registrar': { sections: [...] },
    // Hardcoded role names
}
```

#### Dynamic Locations

**1. Module Permissions Table**
```sql
SELECT * FROM module_permissions 
WHERE module_name = 'students' AND role_name = 'registrar';
```

**2. Visibility Rules Table**
```sql
SELECT * FROM visibility_rules 
WHERE module_name = 'students' AND role_name = 'faculty';
```

### FRD_Roles_Permissions.md Implementation Gaps

#### Documented Requirements vs Implementation

**Requirement**: "Department heads can manage faculty within their department"
**Implementation**: ❌ NOT IMPLEMENTED
- No department_head role
- No department-scoped faculty management

**Requirement**: "Faculty can view students in assigned subjects"
**Implementation**: ✅ PARTIALLY IMPLEMENTED
```php
// HasVisibilityScope.php
case 'assigned_subjects':
    $query->whereHas('subject', function($q) use ($user) {
        $q->where('faculty_id', $user->id);
    });
```

**Requirement**: "Registrar can manage all students"
**Implementation**: ✅ IMPLEMENTED
```php
// module_permissions table
registrar | students | can_view=1, can_create=1, can_edit=1
```

**Requirement**: "Principal can configure permissions dynamically"
**Implementation**: ✅ IMPLEMENTED
- PrincipalConfigController exists
- module_permissions table is editable

### Department-Level Permission Gaps

#### Missing Tables
```sql
-- NEEDED:
CREATE TABLE department_permissions (
    id BIGINT PRIMARY KEY,
    department_id BIGINT,
    role_name VARCHAR(50),
    module_name VARCHAR(50),
    can_view BOOLEAN,
    can_create BOOLEAN,
    can_edit BOOLEAN,
    can_delete BOOLEAN
);

CREATE TABLE user_departments (
    user_id BIGINT,
    department_id BIGINT,
    role_in_department VARCHAR(50),
    is_primary BOOLEAN
);
```

#### Missing Middleware
```php
// NEEDED: app/Http/Middleware/DepartmentScopeMiddleware.php
public function handle(Request $request, Closure $next)
{
    $user = $request->user();
    $departmentId = $request->route('department') ?? $user->primary_department_id;
    
    if (!$user->hasAccessToDepartment($departmentId)) {
        return response()->json(['error' => 'No department access'], 403);
    }
    
    $request->merge(['scoped_department_id' => $departmentId]);
    return $next($request);
}
```

#### Missing Model Methods
```php
// NEEDED: User.php
public function departments()
{
    return $this->belongsToMany(Department::class, 'user_departments')
                ->withPivot('role_in_department', 'is_primary');
}

public function hasAccessToDepartment($departmentId)
{
    return $this->departments()->where('department_id', $departmentId)->exists();
}

public function primaryDepartment()
{
    return $this->departments()->wherePivot('is_primary', true)->first();
}
```


## 3. Database Schema Analysis

### User Management Tables

#### users
```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    user_type ENUM('super-admin', 'principal', 'registrar', 'faculty', 'student'),
    is_active BOOLEAN,
    -- MISSING: department_id, primary_department_id
);
```

#### roles
```sql
CREATE TABLE roles (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    guard_name VARCHAR(255)
    -- MISSING: department_id, is_department_specific
);
```

#### user_roles
```sql
CREATE TABLE user_roles (
    user_id BIGINT,
    role_id BIGINT
    -- MISSING: department_id, scope
);
```

### Department-Related Tables

#### departments
```sql
CREATE TABLE departments (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    code VARCHAR(50),      -- Added in model but not migration
    description TEXT,      -- Added in model but not migration
    is_active BOOLEAN      -- Added in model but not migration
    -- MISSING: head_user_id, parent_department_id
);
```
**Migration File**: `2024_03_academic/2024_03_01_000001_create_departments_table.php`
**Issue**: Migration only has `id, name, timestamps` but model expects more fields

#### programs
```sql
CREATE TABLE programs (
    id BIGINT PRIMARY KEY,
    department_id BIGINT,
    name VARCHAR(255),
    code VARCHAR(50),
    duration_years INT,
    is_active BOOLEAN,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);
```

#### subjects
```sql
CREATE TABLE subjects (
    id BIGINT PRIMARY KEY,
    program_id BIGINT,
    name VARCHAR(255),
    code VARCHAR(50),
    semester INT,
    credits INT,
    is_active BOOLEAN,
    FOREIGN KEY (program_id) REFERENCES programs(id)
    -- MISSING: department_id (should be denormalized for queries)
);
```

### Workflow-Related Tables

#### students
```sql
CREATE TABLE students (
    id BIGINT PRIMARY KEY,
    user_id BIGINT,
    program_id BIGINT,
    category_id BIGINT,
    admission_number VARCHAR(50),
    status ENUM('active', 'inactive', 'graduated', 'withdrawn'),
    application_status ENUM('pending', 'under_review', 'approved', 'rejected'),
    -- MISSING: department_id (denormalized for performance)
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (program_id) REFERENCES programs(id)
);
```

#### attendance_records
```sql
CREATE TABLE attendance_records (
    id BIGINT PRIMARY KEY,
    student_id BIGINT,
    subject_id BIGINT,
    subject_component_id BIGINT NULLABLE,
    component_type ENUM('theory', 'lab', 'practical', 'tutorial'),
    attendance_date DATE,
    status ENUM('present', 'absent', 'late'),
    marked_by BIGINT,
    -- MISSING: department_id
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (marked_by) REFERENCES users(id)
);
```

#### exam_results
```sql
CREATE TABLE exam_results (
    id BIGINT PRIMARY KEY,
    student_id BIGINT,
    subject_id BIGINT,
    program_id BIGINT,  -- REDUNDANT: Can get from subject
    exam_type ENUM('internal', 'external', 'practical'),
    marks_obtained DECIMAL(5,2),
    max_marks DECIMAL(5,2),
    grade VARCHAR(5),
    result ENUM('pass', 'fail'),
    -- MISSING: department_id
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id)
);
```

### Foreign Key Relationships Impact

#### Current Relationships
```
departments
    └── programs
            ├── subjects
            └── students
                    ├── attendance_records
                    └── exam_results
```

#### Department Restructuring Impact

**Scenario**: Moving a program from Science to Commerce department

**Affected Tables**:
1. `programs.department_id` - Direct update
2. `students` - No direct link (ISSUE)
3. `subjects` - No direct link (ISSUE)
4. `attendance_records` - No direct link (ISSUE)
5. `exam_results` - No direct link (ISSUE)

**Required Cascade Updates**:
```sql
-- Current: Manual updates needed
UPDATE students SET ??? WHERE program_id = ?;  -- No department_id column!

-- Needed: Denormalized department_id
UPDATE students SET department_id = ? WHERE program_id = ?;
UPDATE attendance_records SET department_id = ? WHERE student_id IN (...);
UPDATE exam_results SET department_id = ? WHERE student_id IN (...);
```

### Role-Specific Columns That Should Be Department-Specific

#### faculty_assignments
```sql
CREATE TABLE faculty_assignments (
    id BIGINT PRIMARY KEY,
    faculty_id BIGINT,
    subject_id BIGINT,
    academic_year_id BIGINT,
    semester_id BIGINT,
    -- MISSING: department_id
    -- ISSUE: Faculty can be assigned across departments without tracking
);
```

#### lesson_plans
```sql
CREATE TABLE lesson_plans (
    id BIGINT PRIMARY KEY,
    faculty_id BIGINT,
    subject_id BIGINT,
    program_id BIGINT,
    semester_id BIGINT,
    hod_approved_by BIGINT,      -- ISSUE: Which HOD? Need department context
    principal_approved_by BIGINT,
    -- MISSING: department_id
);
```

#### student_fees
```sql
CREATE TABLE student_fees (
    id BIGINT PRIMARY KEY,
    student_id BIGINT,
    fee_structure_id BIGINT,
    total_amount DECIMAL(10,2),
    paid_amount DECIMAL(10,2),
    payment_status ENUM('pending', 'partial', 'paid', 'overdue'),
    -- MISSING: department_id (for department-wise fee reports)
);
```

### Missing Indexes for Department-Based Queries

#### Current Indexes
```sql
-- From migrations: Very few indexes defined
CREATE INDEX idx_students_program ON students(program_id);
CREATE INDEX idx_attendance_student ON attendance_records(student_id);
```

#### Required Indexes for Department Queries
```sql
-- NEEDED:
CREATE INDEX idx_users_department ON users(department_id);
CREATE INDEX idx_students_department ON students(department_id);
CREATE INDEX idx_subjects_department ON subjects(department_id);
CREATE INDEX idx_attendance_department ON attendance_records(department_id);
CREATE INDEX idx_results_department ON exam_results(department_id);
CREATE INDEX idx_faculty_assignments_dept ON faculty_assignments(department_id);

-- Composite indexes for common queries
CREATE INDEX idx_students_dept_program ON students(department_id, program_id);
CREATE INDEX idx_attendance_dept_date ON attendance_records(department_id, attendance_date);
CREATE INDEX idx_results_dept_year ON exam_results(department_id, academic_year);
```

### ERD_Database_Schema.md vs Implementation Gaps

#### Documented in ERD but Missing in Code

**1. Department Hierarchy**
- ERD shows: `departments.parent_department_id`
- Implementation: ❌ NOT IMPLEMENTED

**2. User-Department Relationship**
- ERD shows: `user_departments` junction table
- Implementation: ❌ NOT IMPLEMENTED

**3. Department-Specific Roles**
- ERD shows: `roles.department_id`
- Implementation: ❌ NOT IMPLEMENTED

**4. Department Budget Tracking**
- ERD shows: `department_budgets` table
- Implementation: ❌ NOT IMPLEMENTED

#### Implemented but Not in ERD

**1. module_permissions Table**
- Implementation: ✅ EXISTS
- ERD: ❌ NOT DOCUMENTED

**2. visibility_rules Table**
- Implementation: ✅ EXISTS
- ERD: ❌ NOT DOCUMENTED

**3. approval_chains Table**
- Implementation: ✅ EXISTS
- ERD: ❌ NOT DOCUMENTED

### database_migrations_plan.md Implementation Gaps

#### Planned Migrations Not Executed

**Phase 1 (Foundation)**:
- ✅ users, roles, permissions tables
- ❌ user_departments table
- ❌ department_roles table

**Phase 2 (Academic)**:
- ✅ departments, programs, subjects
- ❌ department_hierarchy table
- ❌ cross_department_enrollments table

**Phase 3 (Student Management)**:
- ✅ students, admissions
- ❌ student_department_transfers table
- ❌ department_quotas table

**Phase 4 (Attendance)**:
- ✅ attendance_records
- ❌ department_attendance_policies table

**Phase 5 (Financial)**:
- ✅ student_fees, fee_payments
- ❌ department_fee_structures table
- ❌ department_financial_reports table

### Critical Schema Issues for Department Implementation

#### Issue #1: No Department Context in Core Tables
```sql
-- REQUIRED MIGRATIONS:
ALTER TABLE users ADD COLUMN primary_department_id BIGINT;
ALTER TABLE students ADD COLUMN department_id BIGINT;
ALTER TABLE subjects ADD COLUMN department_id BIGINT;
ALTER TABLE attendance_records ADD COLUMN department_id BIGINT;
ALTER TABLE exam_results ADD COLUMN department_id BIGINT;
ALTER TABLE faculty_assignments ADD COLUMN department_id BIGINT;
```

#### Issue #2: No Department-User Relationship
```sql
-- REQUIRED TABLE:
CREATE TABLE user_departments (
    id BIGINT PRIMARY KEY,
    user_id BIGINT,
    department_id BIGINT,
    role_in_department VARCHAR(50),
    is_primary BOOLEAN DEFAULT FALSE,
    start_date DATE,
    end_date DATE NULLABLE,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (department_id) REFERENCES departments(id),
    UNIQUE(user_id, department_id, role_in_department)
);
```

#### Issue #3: No Department Permission System
```sql
-- REQUIRED TABLE:
CREATE TABLE department_permissions (
    id BIGINT PRIMARY KEY,
    department_id BIGINT,
    role_name VARCHAR(50),
    module_name VARCHAR(50),
    can_view BOOLEAN DEFAULT FALSE,
    can_create BOOLEAN DEFAULT FALSE,
    can_edit BOOLEAN DEFAULT FALSE,
    can_delete BOOLEAN DEFAULT FALSE,
    can_export BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    UNIQUE(department_id, role_name, module_name)
);
```


## 4. API Layer Assessment

### Endpoints Needing Department Context

#### Current: No Department Parameters
```php
// routes/api.php
Route::get('students', [StudentController::class, 'index']);
// Returns ALL students, no department filtering
```

#### Required: Department-Scoped Endpoints
```php
// NEEDED:
Route::get('departments/{department}/students', [StudentController::class, 'index']);
Route::get('departments/{department}/attendance', [AttendanceController::class, 'index']);
Route::get('departments/{department}/results', [ExamController::class, 'index']);
Route::get('departments/{department}/reports', [ReportController::class, 'index']);
```

### Role-Specific vs Department-Scoped Data

#### Current: Role-Based Responses
**StudentController.php**:
```php
public function index(Request $request)
{
    $query = Student::with(['user', 'program', 'category']);
    
    // Role-based filtering
    if ($request->user()->user_type !== 'super-admin') {
        $query->visibleTo($request->user());
    }
    
    return response()->json($query->get());
}
```
**Issue**: Returns data based on role, not department

#### Required: Department-Scoped Responses
```php
// NEEDED:
public function index(Request $request, $departmentId = null)
{
    $user = $request->user();
    $query = Student::with(['user', 'program', 'category']);
    
    // Department-based filtering
    if ($departmentId) {
        if (!$user->hasAccessToDepartment($departmentId)) {
            return response()->json(['error' => 'No access'], 403);
        }
        $query->where('department_id', $departmentId);
    } else {
        // Return only departments user has access to
        $query->whereIn('department_id', $user->accessibleDepartmentIds());
    }
    
    return response()->json($query->get());
}
```

### API Versioning Inconsistencies

#### Current State: No Versioning
```php
// routes/api.php - All routes at root level
Route::post('/login', [AuthController::class, 'login']);
Route::apiResource('students', StudentController::class);
```

#### Issues:
1. No `/v1/` prefix
2. No version-specific controllers
3. No deprecation headers
4. Breaking changes affect all clients

#### Required Structure:
```php
// routes/api/v1.php
Route::prefix('v1')->group(function () {
    Route::post('/login', [V1\AuthController::class, 'login']);
    Route::apiResource('students', V1\StudentController::class);
});

// routes/api/v2.php (future)
Route::prefix('v2')->group(function () {
    Route::post('/login', [V2\AuthController::class, 'login']);
    Route::apiResource('departments/{department}/students', [V2\StudentController::class, 'index']);
});
```

### Endpoints Missing Error Handling

#### Example #1: StudentController
```php
public function show($id)
{
    $student = Student::with(['user', 'program', 'category'])->findOrFail($id);
    return response()->json($student);
}
```
**Issues**:
- No try-catch for database errors
- No validation of $id parameter
- No department access check
- Generic 404 error message

#### Example #2: AttendanceController
```php
public function markAttendance(Request $request)
{
    // Validation exists
    DB::beginTransaction();
    try {
        foreach ($request->students as $studentData) {
            AttendanceRecord::updateOrCreate([...], [...]);
        }
        DB::commit();
        return response()->json(['message' => 'Success'], 201);
    } catch (\Exception $e) {
        DB::rollback();
        return response()->json(['error' => 'Failed'], 500);  // Generic error
    }
}
```
**Issues**:
- Generic error message (doesn't specify what failed)
- No logging of exception
- No validation of student access
- No department context

#### Example #3: FeeController
```php
public function recordPayment(Request $request)
{
    // No validation
    // No error handling
    // Direct model manipulation
}
```
**Issues**:
- Missing validation
- No try-catch
- No transaction
- No audit logging

### api_contracts.md Implementation Gaps

#### Documented Endpoints Not Implemented

**1. Department Management**
```json
// Documented in api_contracts.md:
GET /api/v1/departments/{id}/faculty
GET /api/v1/departments/{id}/students
GET /api/v1/departments/{id}/subjects
GET /api/v1/departments/{id}/statistics

// Implementation: ❌ NOT IMPLEMENTED
```

**2. Department-Scoped Reports**
```json
// Documented:
GET /api/v1/departments/{id}/reports/attendance
GET /api/v1/departments/{id}/reports/results
GET /api/v1/departments/{id}/reports/fees

// Implementation: ❌ NOT IMPLEMENTED
```

**3. Cross-Department Operations**
```json
// Documented:
POST /api/v1/students/{id}/transfer-department
GET /api/v1/departments/compare-performance

// Implementation: ❌ NOT IMPLEMENTED
```

#### Implemented Endpoints Not Documented

**1. Principal Configuration**
```php
// Implemented:
Route::prefix('principal/config')->group(function() {
    Route::get('permissions/{module}', ...);
    Route::post('permissions', ...);
});

// Documentation: ❌ NOT IN api_contracts.md
```

**2. Lesson Planning**
```php
// Implemented:
Route::apiResource('lesson-plans', LessonPlanController::class);
Route::post('lesson-plans/{id}/submit', ...);

// Documentation: ❌ NOT IN api_contracts.md
```

### Controller Methods Requiring Department-Aware Modification

#### StudentController.php
```php
// CURRENT:
public function index(Request $request)
{
    $students = Student::with(['user', 'program', 'category'])->get();
    return response()->json($students);
}

// REQUIRED:
public function index(Request $request, $departmentId = null)
{
    $query = Student::with(['user', 'program', 'category', 'department']);
    
    if ($departmentId) {
        $this->authorize('viewDepartment', [Student::class, $departmentId]);
        $query->where('department_id', $departmentId);
    } else {
        $query->whereIn('department_id', $request->user()->accessibleDepartmentIds());
    }
    
    return response()->json([
        'data' => $query->paginate(50),
        'meta' => [
            'department_id' => $departmentId,
            'total_departments' => $request->user()->departments()->count()
        ]
    ]);
}
```

#### AttendanceController.php
```php
// CURRENT:
public function getAttendanceReport(Request $request)
{
    $query = AttendanceRecord::with(['student.user', 'subject'])
        ->whereBetween('attendance_date', [$request->date_from, $request->date_to]);
    
    if ($request->program_id) {
        $query->whereHas('student', function($q) use ($request) {
            $q->where('program_id', $request->program_id);
        });
    }
    
    return response()->json($query->get());
}

// REQUIRED:
public function getAttendanceReport(Request $request, $departmentId = null)
{
    $this->validate($request, [
        'date_from' => 'required|date',
        'date_to' => 'required|date|after_or_equal:date_from',
        'program_id' => 'nullable|exists:programs,id'
    ]);
    
    $query = AttendanceRecord::with(['student.user', 'subject', 'department'])
        ->whereBetween('attendance_date', [$request->date_from, $request->date_to]);
    
    if ($departmentId) {
        $this->authorize('viewDepartmentAttendance', $departmentId);
        $query->where('department_id', $departmentId);
    } else {
        $query->whereIn('department_id', $request->user()->accessibleDepartmentIds());
    }
    
    if ($request->program_id) {
        $query->whereHas('student', function($q) use ($request) {
            $q->where('program_id', $request->program_id);
        });
    }
    
    return response()->json([
        'data' => $query->get(),
        'summary' => [
            'total_records' => $query->count(),
            'department_id' => $departmentId,
            'date_range' => [$request->date_from, $request->date_to]
        ]
    ]);
}
```

#### ReportController.php
```php
// CURRENT:
public function studentReport(Request $request)
{
    $students = Student::with(['user', 'program', 'category'])->get();
    return response()->json($students);
}

// REQUIRED:
public function studentReport(Request $request, $departmentId = null)
{
    $query = Student::with(['user', 'program', 'category', 'department']);
    
    if ($departmentId) {
        $this->authorize('viewDepartmentReports', $departmentId);
        $query->where('department_id', $departmentId);
    } else {
        $query->whereIn('department_id', $request->user()->accessibleDepartmentIds());
    }
    
    $students = $query->get();
    
    return response()->json([
        'data' => $students,
        'statistics' => [
            'total_students' => $students->count(),
            'by_program' => $students->groupBy('program_id')->map->count(),
            'by_status' => $students->groupBy('status')->map->count(),
            'department_id' => $departmentId
        ]
    ]);
}
```

### Route Definitions Requiring Modification

#### Current Routes (Flat Structure)
```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('students', StudentController::class);
    Route::get('attendance/report', [AttendanceController::class, 'getAttendanceReport']);
    Route::get('reports/students', [ReportController::class, 'studentReport']);
});
```

#### Required Routes (Department-Scoped)
```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    
    // Global endpoints (cross-department)
    Route::apiResource('students', StudentController::class);
    
    // Department-scoped endpoints
    Route::prefix('departments/{department}')->middleware('department.access')->group(function () {
        Route::get('students', [StudentController::class, 'index']);
        Route::get('attendance/report', [AttendanceController::class, 'getAttendanceReport']);
        Route::get('results/report', [ExamController::class, 'getResultsReport']);
        Route::get('reports/students', [ReportController::class, 'studentReport']);
        Route::get('reports/attendance', [ReportController::class, 'attendanceReport']);
        Route::get('reports/fees', [ReportController::class, 'feeReport']);
    });
    
    // Department management (admin only)
    Route::middleware('role:super-admin,principal')->group(function () {
        Route::apiResource('departments', DepartmentController::class);
        Route::post('departments/{department}/assign-head', [DepartmentController::class, 'assignHead']);
        Route::get('departments/{department}/statistics', [DepartmentController::class, 'statistics']);
    });
});
```

### Missing API Response Standards

#### Current: Inconsistent Response Formats
```php
// StudentController
return response()->json($students);  // Direct data

// FeeController
return response()->json(['message' => 'Success', 'data' => $fees]);  // Wrapped

// AttendanceController
return response()->json(['records' => $attendance, 'summary' => $summary]);  // Custom
```

#### Required: Standardized Response Format
```php
// app/Http/Responses/ApiResponse.php
class ApiResponse
{
    public static function success($data, $message = null, $meta = [])
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $meta
        ]);
    }
    
    public static function error($message, $code = 400, $errors = [])
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }
}

// Usage:
return ApiResponse::success($students, 'Students retrieved', [
    'department_id' => $departmentId,
    'total' => $students->count()
]);
```


## 5. Frontend Component Analysis

### Hardcoded Navigation Elements

#### Location: `public/js/navigation-config.js`
```javascript
const NavigationConfig = {
    roles: {
        'super-admin': { sections: [...] },  // Hardcoded
        'principal': { sections: [...] },     // Hardcoded
        'registrar': { sections: [...] },     // Hardcoded
        'faculty': { sections: [...] },       // Hardcoded
        'student': { sections: [...] }        // Hardcoded
    }
};
```

**Issue**: No department-based navigation
**Required**: Dynamic navigation based on user's department(s)

```javascript
// NEEDED:
const NavigationConfig = {
    async generateNavigation(user) {
        const departments = await ApiService.getUserDepartments(user.id);
        const navigation = [];
        
        departments.forEach(dept => {
            navigation.push({
                department: dept.name,
                sections: this.getSectionsForDepartment(dept.id, user.role)
            });
        });
        
        return navigation;
    }
};
```

### UI Components Assuming Role-Based Context

#### Example #1: Student List Component
**Location**: `public/secure_admin.html`
```html
<div id="students" class="section">
    <h2>🎓 Student Records</h2>
    <div id="studentsList">Loading students...</div>
</div>
```
```javascript
async function loadStudents() {
    const students = await DataLoader.loadStudents();
    // Loads ALL students, no department filter
}
```

**Required**: Department-aware component
```html
<div id="students" class="section">
    <div class="department-selector">
        <select id="departmentFilter" onchange="loadStudents()">
            <option value="">All Departments</option>
            <option value="1">Science</option>
            <option value="2">Commerce</option>
        </select>
    </div>
    <div id="studentsList">Loading students...</div>
</div>
```
```javascript
async function loadStudents() {
    const deptId = document.getElementById('departmentFilter').value;
    const students = await DataLoader.loadStudents(deptId);
}
```

#### Example #2: Dashboard Stats
**Location**: `public/secure_admin.html`
```html
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number">1,250</div>
        <div class="stat-label">Total Students</div>
    </div>
</div>
```

**Issue**: Shows global stats, not department-specific

**Required**:
```html
<div class="department-stats">
    <h3>Department: <span id="currentDepartment">Science</span></h3>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number" id="deptStudents">450</div>
            <div class="stat-label">Department Students</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="deptFaculty">15</div>
            <div class="stat-label">Department Faculty</div>
        </div>
    </div>
</div>
```

### JavaScript Files with Business Logic

#### Location: `public/js/data-loader.js`
```javascript
const DataLoader = {
    async loadStudents() {
        try {
            const response = await ApiService.students.getAll();
            return response.data || response;
        } catch (error) {
            throw new Error('Unable to load student data');
        }
    }
};
```

**Issue**: Business logic (error handling, data transformation) in frontend

**Should Be**: Backend service handles logic, frontend just displays
```javascript
// Frontend should only handle UI logic
const DataLoader = {
    async loadStudents(departmentId = null) {
        const endpoint = departmentId 
            ? `/api/departments/${departmentId}/students`
            : '/api/students';
        return await ApiService.fetch(endpoint);
    }
};
```

#### Location: `public/js/api-service.js`
```javascript
getCurrentAcademicYear() {
    const now = new Date();
    const year = now.getFullYear();
    const month = now.getMonth();
    return month >= 6 ? `${year}-${year + 1}` : `${year - 1}-${year}`;
}
```

**Issue**: Business logic (academic year calculation) in frontend

**Should Be**: Backend API provides current academic year
```javascript
// Backend: AcademicYearService.php
public function getCurrentAcademicYear() {
    return AcademicYear::where('is_current', true)->first();
}

// Frontend: Just fetch it
async getCurrentAcademicYear() {
    return await ApiService.fetch('/api/academic-years/current');
}
```

### CSS Classes Tied to Specific Roles

#### Location: `public/secure_admin.html`, `public/secure_faculty.html`
```html
<style>
    .registrar-dashboard { /* Registrar-specific styles */ }
    .faculty-dashboard { /* Faculty-specific styles */ }
    .student-dashboard { /* Student-specific styles */ }
</style>
```

**Issue**: Role-specific CSS classes, not reusable

**Required**: Generic, reusable classes
```html
<style>
    .dashboard-container { /* Generic dashboard */ }
    .dashboard-stats { /* Generic stats */ }
    .dashboard-actions { /* Generic actions */ }
    
    /* Department-specific overrides */
    [data-department="science"] .dashboard-container {
        --primary-color: #3b82f6;
    }
    [data-department="commerce"] .dashboard-container {
        --primary-color: #10b981;
    }
</style>
```

### text_wireframes.md Implementation Gaps

#### Documented Wireframes Not Implemented

**1. Department Selector Component**
```
Documented in text_wireframes.md:
┌─ Department Selector ────────────────┐
│ [Science ▼] [Commerce] [Arts]        │
│ Current: Science Department          │
└──────────────────────────────────────┘

Implementation: ❌ NOT IMPLEMENTED
```

**2. Cross-Department View**
```
Documented:
┌─ Multi-Department Dashboard ─────────┐
│ Science: 450 students | 15 faculty   │
│ Commerce: 380 students | 12 faculty  │
│ Arts: 290 students | 10 faculty      │
└──────────────────────────────────────┘

Implementation: ❌ NOT IMPLEMENTED
```

**3. Department Transfer Workflow**
```
Documented:
Student Transfer Request Form
├── Current Department: [Science]
├── Target Department: [Commerce ▼]
├── Reason: [Text area]
└── [Submit for Approval]

Implementation: ❌ NOT IMPLEMENTED
```

#### Implemented UI Not Documented

**1. Principal Configuration Interface**
```html
<!-- Implemented in principal-config-section.html -->
<div class="permission-matrix">
    <table>
        <tr>
            <th>Role</th>
            <th>View</th>
            <th>Create</th>
            <th>Edit</th>
        </tr>
    </table>
</div>

Documentation: ❌ NOT IN text_wireframes.md
```

**2. Lesson Planning Interface**
```html
<!-- Implemented in secure_faculty.html -->
<div id="lesson-plans" class="section">
    <h2>Lesson Plans</h2>
    <!-- Complex lesson planning UI -->
</div>

Documentation: ❌ NOT IN text_wireframes.md
```

### Department-Based UI Restructuring Impact

#### Current: Single Dashboard Per Role
```
secure_admin.html    → Registrar Dashboard (all data)
secure_faculty.html  → Faculty Dashboard (assigned data)
secure_student.html  → Student Dashboard (own data)
```

#### Required: Department-Aware Dashboards
```
secure_admin.html
├── Department Selector (top bar)
├── Department-Scoped Stats
├── Department-Scoped Student List
└── Department-Scoped Reports

secure_faculty.html
├── My Departments (if multi-department)
├── Department-Scoped Classes
├── Department-Scoped Students
└── Department-Scoped Attendance

secure_principal.html
├── All Departments Overview
├── Department Comparison
├── Cross-Department Reports
└── Department Management
```

#### Components Requiring Modification

**1. Navigation Sidebar**
```javascript
// CURRENT: public/js/navigation-config.js
generateNavigation(role) {
    // Returns role-based navigation
}

// REQUIRED:
generateNavigation(user) {
    const departments = user.departments;
    const navigation = [];
    
    if (departments.length > 1) {
        navigation.push({
            type: 'department-selector',
            departments: departments
        });
    }
    
    departments.forEach(dept => {
        navigation.push({
            department: dept.name,
            sections: this.getSectionsForDepartment(dept.id, user.role)
        });
    });
    
    return navigation;
}
```

**2. Data Tables**
```javascript
// CURRENT: public/js/data-loader.js
renderStudentsTable(students) {
    return `<table>
        <tr><th>Name</th><th>Program</th></tr>
        ${students.map(s => `<tr><td>${s.name}</td><td>${s.program}</td></tr>`)}
    </table>`;
}

// REQUIRED:
renderStudentsTable(students, departmentId = null) {
    return `<table>
        <tr>
            <th>Name</th>
            <th>Program</th>
            ${!departmentId ? '<th>Department</th>' : ''}
        </tr>
        ${students.map(s => `
            <tr>
                <td>${s.name}</td>
                <td>${s.program}</td>
                ${!departmentId ? `<td>${s.department}</td>` : ''}
            </tr>
        `)}
    </table>`;
}
```

**3. Filter Components**
```html
<!-- CURRENT: No department filter -->
<div class="filters">
    <select id="programFilter">
        <option>All Programs</option>
    </select>
</div>

<!-- REQUIRED: Department filter -->
<div class="filters">
    <select id="departmentFilter" onchange="updateProgramFilter()">
        <option value="">All Departments</option>
        <option value="1">Science</option>
        <option value="2">Commerce</option>
    </select>
    <select id="programFilter">
        <option>All Programs</option>
        <!-- Populated based on selected department -->
    </select>
</div>
```

## 6. Workflow Process Mapping

### State Machines in the System

#### 1. Student Admission Workflow
**Location**: `app/Models/Student.php`
```php
// application_status field
'pending' → 'under_review' → 'approved' | 'rejected'
```

**Current Implementation**:
```php
// StudentController.php
public function approve($id)
{
    $student = Student::findOrFail($id);
    $student->update([
        'application_status' => 'approved',
        'approved_at' => now(),
    ]);
}
```

**Issues**:
- No state validation (can approve already approved)
- No department context (who approved?)
- No workflow tracking
- Hardcoded transitions

**Required**:
```php
// app/Services/WorkflowService.php
public function transitionStudentAdmission($studentId, $action, $departmentId)
{
    $student = Student::findOrFail($studentId);
    $currentState = $student->application_status;
    
    $allowedTransitions = [
        'pending' => ['under_review', 'rejected'],
        'under_review' => ['approved', 'rejected'],
        'approved' => [],
        'rejected' => []
    ];
    
    if (!in_array($action, $allowedTransitions[$currentState])) {
        throw new InvalidStateTransitionException();
    }
    
    DB::transaction(function() use ($student, $action, $departmentId) {
        $student->update(['application_status' => $action]);
        
        WorkflowHistory::create([
            'entity_type' => 'Student',
            'entity_id' => $student->id,
            'from_state' => $currentState,
            'to_state' => $action,
            'department_id' => $departmentId,
            'performed_by' => auth()->id(),
            'performed_at' => now()
        ]);
    });
}
```

#### 2. Fee Payment Workflow
**Location**: `app/Models/StudentFee.php`
```php
// payment_status field
'pending' → 'partial' → 'paid' | 'overdue'
```

**Current Implementation**:
```php
// FeeController.php
public function recordPayment(Request $request)
{
    $fee = StudentFee::find($request->fee_id);
    $fee->paid_amount += $request->amount;
    
    if ($fee->paid_amount >= $fee->payable_amount) {
        $fee->payment_status = 'paid';
    } else {
        $fee->payment_status = 'partial';
    }
    
    $fee->save();
}
```

**Issues**:
- No validation of payment amount
- No transaction handling
- No audit trail
- No department tracking

#### 3. Lesson Plan Approval Workflow
**Location**: `app/Models/LessonPlan.php`
```php
// approval_status field
'draft' → 'submitted' → 'hod_approved' → 'principal_approved' | 'rejected'
```

**Current Implementation**:
```php
// LessonPlanController.php
public function approve($id)
{
    $lessonPlan = LessonPlan::findOrFail($id);
    $user = auth()->user();
    
    if ($user->user_type === 'hod') {
        $lessonPlan->hod_approved_by = $user->id;
        $lessonPlan->approval_status = 'hod_approved';
    } elseif ($user->user_type === 'principal') {
        $lessonPlan->principal_approved_by = $user->id;
        $lessonPlan->approval_status = 'principal_approved';
    }
    
    $lessonPlan->save();
}
```

**Issues**:
- Hardcoded role checks
- No department validation (which HOD?)
- No state machine validation
- No workflow history

### Hardcoded Workflow Transitions

#### Location: Controllers
```php
// StudentController.php - Hardcoded
$student->application_status = 'approved';

// FeeController.php - Hardcoded
$fee->payment_status = 'paid';

// LessonPlanController.php - Hardcoded
$lessonPlan->approval_status = 'hod_approved';
```

**Required**: Configurable workflow engine
```php
// config/workflows.php
return [
    'student_admission' => [
        'states' => ['pending', 'under_review', 'approved', 'rejected'],
        'transitions' => [
            'pending' => ['under_review', 'rejected'],
            'under_review' => ['approved', 'rejected']
        ],
        'approvers' => [
            'under_review' => ['registrar'],
            'approved' => ['principal']
        ]
    ],
    'lesson_plan_approval' => [
        'states' => ['draft', 'submitted', 'hod_approved', 'principal_approved', 'rejected'],
        'transitions' => [
            'draft' => ['submitted'],
            'submitted' => ['hod_approved', 'rejected'],
            'hod_approved' => ['principal_approved', 'rejected']
        ],
        'approvers' => [
            'hod_approved' => ['department_head'],
            'principal_approved' => ['principal']
        ]
    ]
];
```

### Audit Trail Implementation

#### Current: Partial Implementation
**Location**: `app/Models/AuditLog.php`
```php
class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values'
    ];
}
```

**Issues**:
- No department_id field
- Not consistently used across controllers
- No workflow-specific tracking

#### Gaps:
```php
// StudentController.php - No audit logging
public function update(Request $request, $id)
{
    $student = Student::findOrFail($id);
    $student->update($request->all());
    // Missing: AuditLog::create([...]);
}

// FeeController.php - No audit logging
public function recordPayment(Request $request)
{
    $fee->paid_amount += $request->amount;
    $fee->save();
    // Missing: AuditLog::create([...]);
}
```

**Required**: Comprehensive audit trail
```php
// app/Traits/Auditable.php
trait Auditable
{
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'department_id' => $model->department_id ?? auth()->user()->primary_department_id,
                'action' => 'created',
                'entity_type' => get_class($model),
                'entity_id' => $model->id,
                'new_values' => $model->toArray()
            ]);
        });
        
        static::updated(function ($model) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'department_id' => $model->department_id ?? auth()->user()->primary_department_id,
                'action' => 'updated',
                'entity_type' => get_class($model),
                'entity_id' => $model->id,
                'old_values' => $model->getOriginal(),
                'new_values' => $model->getChanges()
            ]);
        });
    }
}
```

### Workflow Dependencies Between Modules

#### Dependency #1: Student Admission → Fee Assignment
```
Student approved → Auto-create fee record → Generate installments
```
**Current**: Manual process
**Required**: Automated workflow

#### Dependency #2: Attendance → Result Eligibility
```
Attendance < 75% → Student ineligible for exam → Block result entry
```
**Current**: Not enforced
**Required**: Validation in ExamController

#### Dependency #3: Fee Payment → Document Release
```
All fees paid → Allow document download → Generate certificates
```
**Current**: Not enforced
**Required**: Middleware check

### Phase_Planning.md Alignment

#### Phase 1 Workflows (Documented)
- ✅ Student admission workflow
- ❌ Department assignment workflow
- ❌ Fee structure assignment workflow

#### Phase 2 Workflows (Documented)
- ✅ Lesson plan approval workflow
- ❌ Cross-department transfer workflow
- ❌ Department budget approval workflow

#### Phase 3 Workflows (Documented)
- ❌ Multi-department reporting workflow
- ❌ Department performance review workflow
- ❌ Faculty cross-department assignment workflow

### Governance_Framework.md Alignment

#### Documented Approval Chains
```
Admission Approval:
Registrar → Department Head → Principal

Fee Waiver:
Student Request → Registrar → Department Head → Principal (if > ₹5000)

Lesson Plan:
Faculty → HOD → Principal
```

#### Implementation Status
- Admission: ✅ Partially (no department head step)
- Fee Waiver: ❌ Not implemented
- Lesson Plan: ✅ Implemented (but no department validation)

### Critical Workflow Issues for Department Implementation

#### Issue #1: No Department Context in Workflows
All workflows operate globally, not department-specific

#### Issue #2: No Department Head Role
Workflows reference "HOD" but no department_head role exists

#### Issue #3: No Cross-Department Workflow Support
Cannot handle students/faculty in multiple departments

#### Issue #4: No Workflow Configuration UI
All workflows hardcoded, Principal cannot configure

---

## Summary of Critical Blockers

### Architecture Blockers
1. No service layer consistency
2. Business logic in controllers
3. No dependency injection
4. No API versioning

### Permission Blockers
1. No department_permissions table
2. No user_departments relationship
3. Role-based instead of department-based
4. No permission caching

### Database Blockers
1. Missing department_id in core tables
2. No department hierarchy support
3. Missing indexes for department queries
4. No department-user junction table

### API Blockers
1. No department context parameters
2. Role-specific responses instead of department-scoped
3. No versioning strategy
4. Inconsistent error handling

### Frontend Blockers
1. Hardcoded role-based navigation
2. No department selector components
3. Business logic in JavaScript
4. Role-specific CSS classes

### Workflow Blockers
1. Hardcoded state transitions
2. No department context in workflows
3. Incomplete audit trail
4. No workflow configuration system
