# Kilo Code Rules for PVGS ERP System

## Project Context
- Project: PVGS College ERP System
- Framework: Laravel 10.x
- PHP Version: 8.2
- Database: MySQL 8.0
- Frontend: Blade + Alpine.js (later)
- API: RESTful using Laravel Sanctum

## Branch Strategy
- Main development branch: `development`
- Feature branches: `feature/feature-name`
- Never push directly to `main`

## Project Structure
app/
├── Models/
│   ├── Academic/      # Programs, Subjects, Batches, Semesters
│   ├── Financial/     # Fees, Payments, Transactions
│   ├── User/          # Students, Faculty, Staff
│   ├── Attendance/    # Attendance sessions, records
│   └── Examination/   # Exams, Marks, Results, ATKT
├── Http/
│   ├── Controllers/Api/
│   │   ├── Academic/
│   │   ├── Financial/
│   │   ├── Attendance/
│   │   └── Examination/
│   └── Requests/      # Form validation requests
├── Services/          # Business logic
└── Repositories/      # Data access layer

## Coding Standards

### Naming Conventions
- **Files:** PascalCase (StudentController.php)
- **Classes:** PascalCase (Student, StudentController)
- **Methods:** camelCase (getStudentById, markAttendance)
- **Variables:** camelCase ($studentId, $firstName)
- **Database tables:** snake_case (students, academic_years)
- **Database columns:** snake_case (first_name, student_id)
- **Routes:** kebab-case (/api/students, /api/academic-years)

### Migration Naming
Format: `YYYY_MM_DD_HHMMSS_create_tablename_table.php`
Example: `2024_01_01_100000_create_students_table.php`

Use migration numbering in folders:
- `database/migrations/2024_01_foundation/001_create_users_table.php`
- `database/migrations/2024_01_foundation/002_create_roles_table.php`

### Model Conventions
- Always use proper namespaces based on category
- Example: `App\Models\User\Student`
- Use traits: HasFactory, SoftDeletes (when needed)
- Define relationships clearly
- Add PHPDoc blocks for IDE support
- Use type hints for all parameters and return types

Example:
```php
<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $student_id
 * @property string $first_name
 * @property string $last_name
 */
class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'student_id',
        'first_name',
        'last_name',
        // ... other fields
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'documents' => 'array',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
```

### Controller Conventions
- Extend BaseController for API controllers
- Use Form Request classes for validation
- Return consistent JSON responses
- Use Resource classes for transforming data
- Include proper error handling
- Use transactions for multi-step operations

### Validation Rules Location
- Always create separate Form Request classes
- Location: `app/Http/Requests/`
- Naming: `StoreStudentRequest.php`, `UpdateStudentRequest.php`

### Database Conventions
- Use foreign keys with proper constraints
- Add indexes on frequently queried columns
- Use enums for status fields
- Always add timestamps
- Use soft deletes for important data
- Add unique constraints where needed

### Authorization
- Use Spatie Laravel Permission package
- Permission format: `resource.action` (students.create, fees.read)
- Roles: Super Admin, Admin, Faculty, Student, Accountant, Staff
- Check permissions in controllers using middleware

### API Response Format
```php
// Success
{
    "success": true,
    "message": "Student created successfully",
    "data": { ... }
}

// Error
{
    "success": false,
    "message": "Validation failed",
    "errors": { ... }
}
```

### Commit Message Format
<type>: <short description>
Types:

feat: New feature
fix: Bug fix
refactor: Code improvement
docs: Documentation
test: Tests
chore: Maintenance

Examples:

feat: add Student CRUD API endpoints
fix: resolve foreign key constraint in fees table
refactor: improve attendance calculation logic
docs: update API documentation for student endpoints


## Environment-Specific Rules

### Local Development
- Generate code structure and logic
- Don't execute commands (no artisan, git, composer)
- Show code for me to review before creating files
- Provide complete file paths
- Include all necessary imports

### Codespace Development
- Execute commands (migrations, tinker, serve)
- Run tests and verify functionality
- Install packages if needed
- Pull from git before working
- Test database operations

## Code Generation Preferences

### When Generating Migrations
- Include all necessary columns
- Add proper foreign key constraints with onDelete actions
- Add indexes on frequently queried columns
- Add unique constraints where needed
- Use proper data types
- Include comments for complex logic

### When Generating Models
- Include all relationships
- Add fillable/guarded properties
- Define casts for special fields (dates, JSON, boolean)
- Add accessors for computed fields
- Add scopes for common queries
- Include PHPDoc blocks

### When Generating Controllers
- Extend BaseController for API
- Use dependency injection
- Use Form Request classes for validation
- Return using Resource classes
- Add proper error handling with try-catch
- Use database transactions where needed
- Add authorization checks

### When Generating Seeders
- Use factories where possible
- Use Faker for realistic data
- Ensure referential integrity (proper foreign keys)
- Include variety of data (active/inactive, different roles)
- Use Indian names and realistic data for our context
- Add timestamps properly

## Additional Preferences

### Error Messages
- User-friendly messages
- Include field names in validation errors
- Provide helpful suggestions

### Documentation
- Add comments for complex logic
- Document all public methods
- Include examples in comments
- Keep README updated

### Testing
- Write tests for critical functionality
- Test both success and failure scenarios
- Use database transactions in tests
- Mock external services

## Database Migration Plan Reference

Follow the phased approach:
- Phase 1: Foundation (users, roles, programs, subjects)
- Phase 2: User Management (students, faculty, staff)
- Phase 3: Academic Structure (enrollments, assignments)
- Phase 4: Attendance System
- Phase 5: Financial System
- Phase 6: Examination System

## Remember
- Always follow Laravel best practices
- Keep code DRY (Don't Repeat Yourself)
- Use meaningful variable and function names
- Prioritize readability over cleverness
- Security first - validate all inputs
- Consider scalability in design decisions