# Principal Configuration Interface - Implementation Guide

## Overview
Dynamic permission management system allowing Principal to configure role-based access, approval workflows, and data visibility without code changes.

## Database Schema

### 1. role_module_access
Stores module-level permissions for each role.

```sql
- role_id: FK to roles table
- module_name: students, attendance, fees, etc.
- can_view, can_create, can_edit, can_delete: boolean flags
```

### 2. approval_workflows
Defines multi-step approval chains.

```sql
- workflow_name: fee_approval, lesson_plan, etc.
- entity_type: FeePayment, LessonPlan
- approval_order: 1, 2, 3 (sequential steps)
- approver_role_id: FK to roles
- is_required: boolean
```

### 3. data_visibility_rules
Controls data access scope per role.

```sql
- role_id: FK to roles
- entity_type: Student, Attendance, Result
- scope: all, department, program, own, assigned
- filters: JSON for additional conditions
```

## API Endpoints

### Module Access
```
GET  /api/config/modules/{roleId}
POST /api/config/modules/{roleId}
```

### Approval Workflows
```
GET  /api/config/workflows
POST /api/config/workflows
```

### Data Visibility
```
GET  /api/config/visibility/{roleId}
POST /api/config/visibility/{roleId}
```

## UI Implementation

### Navigation Addition
Add to Principal navigation in `navigation-config.js`:

```javascript
{
    title: 'System Configuration',
    items: [
        { icon: '⚙️', label: 'Permissions', section: 'system-config' }
    ]
}
```

### Configuration Panel Structure

**Tab 1: Module Access**
- Select role dropdown
- Table with modules × CRUD permissions
- Real-time preview of changes

**Tab 2: Approval Workflows**
- Pre-defined workflows (Fee, Lesson Plan, etc.)
- Multi-step approval chain builder
- Required/Optional flag per step

**Tab 3: Data Visibility**
- Select role dropdown
- Entity type × Scope mapping
- Description of what each scope means

## Backward Compatibility

### Existing Permission Checks
```php
// Old way (still works)
if ($user->hasPermission('view students')) { }

// New way (checks dynamic config)
if (PermissionChecker::canAccess($user, 'students', 'view')) { }
```

### Migration Strategy
1. Seed `role_module_access` with current permissions
2. Add middleware to check dynamic permissions
3. Gradually migrate permission checks

## Implementation Steps

### Phase 1: Database Setup
```bash
php artisan migrate --path=database/migrations/2024_08_configuration
```

### Phase 2: Seed Default Configuration
```php
// Seed current role permissions into new tables
DB::table('role_module_access')->insert([
    ['role_id' => 4, 'module_name' => 'students', 'can_view' => true, ...],
    // ... more entries
]);
```

### Phase 3: Add Configuration Section
1. Copy `principal-config-section.html` content
2. Paste into `secure_principal.html` before closing `</div>` of main-content
3. Add navigation item for "System Configuration"

### Phase 4: Connect API
Update JavaScript functions to call real API:

```javascript
async function loadModuleAccess() {
    const roleId = document.getElementById('roleSelect').value;
    const response = await fetch(`/api/config/modules/${roleId}`);
    const data = await response.json();
    // Populate table with real data
}
```

## Security Considerations

1. **Principal-Only Access**: Middleware ensures only Principal can access config endpoints
2. **Audit Logging**: All configuration changes logged to activity_logs
3. **Validation**: Backend validates all permission changes
4. **Rollback**: Keep configuration history for rollback capability

## Example Workflows

### Fee Approval Chain
```
Student submits fee → Registrar approves → Principal final approval
```

### Lesson Plan Approval
```
Faculty creates → HOD reviews → Principal approves (optional)
```

### Data Visibility Examples

**Faculty Role - Students:**
- Scope: `assigned` → Only students in their classes

**Registrar Role - Fees:**
- Scope: `all` → All fee records

**Student Role - Results:**
- Scope: `own` → Only their own results

## Testing Checklist

- [ ] Principal can view all roles' permissions
- [ ] Module access changes save correctly
- [ ] Approval workflows update in database
- [ ] Data visibility rules apply to queries
- [ ] Non-principal users cannot access config
- [ ] Changes reflect immediately in UI
- [ ] Backward compatibility maintained

## Future Enhancements

1. **Permission Templates**: Pre-defined permission sets
2. **Bulk Operations**: Apply permissions to multiple roles
3. **Permission History**: Track who changed what when
4. **Visual Workflow Builder**: Drag-and-drop approval chains
5. **Conditional Rules**: If-then permission logic