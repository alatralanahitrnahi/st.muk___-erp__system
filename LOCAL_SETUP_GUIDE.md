# Local Setup Guide - PVGS ERP System

## Prerequisites

- PHP 8.1+
- Composer
- MySQL 8.0+
- Node.js 16+ (optional, for frontend assets)

## Step 1: Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

## Step 2: Database Configuration

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pvgs_erp
DB_USERNAME=root
DB_PASSWORD=your_password

# Session Configuration
SESSION_DRIVER=database
SESSION_LIFETIME=15

# Cache Configuration
CACHE_DRIVER=database
```

Create database:

```bash
mysql -u root -p
CREATE DATABASE pvgs_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

## Step 3: Install Dependencies

```bash
composer install
```

## Step 4: Run Migrations

```bash
# Run all migrations in order
php artisan migrate

# Expected output:
# ✓ 2024_10_01_000001_add_department_id_to_core_tables
# ✓ 2024_10_01_000002_create_user_departments_table
# ✓ 2024_10_01_000003_create_department_permissions_table
# ✓ 2024_10_01_000004_create_workflow_history_table
# ✓ 2024_10_02_000001_add_department_head_and_audit_enhancements
# ✓ 2024_10_03_000001_add_department_performance_indexes
# ✓ 2024_10_03_000002_create_security_audit_tables
```

## Step 5: Seed Initial Data

```bash
# Seed departments and permissions
php artisan db:seed --class=DepartmentPermissionsSeeder
```

## Step 6: Backfill Department Data

```bash
# Assign department_id to existing records
php artisan department:backfill

# Assign users to departments
php artisan department:assign-users

# Validate data integrity
php artisan department:validate
```

## Step 7: Start Local Server

```bash
php artisan serve
```

Server runs at: `http://localhost:8000`

## Step 8: Test API Endpoints

### Authentication

```bash
# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@pvgs.edu","password":"password"}'

# Response includes session cookie
```

### Department Endpoints

```bash
# Get department students
curl http://localhost:8000/api/v1/departments/1/students \
  -H "Cookie: pvgs_session=YOUR_SESSION_ID"

# Get department dashboard
curl http://localhost:8000/api/v1/departments/1/dashboard \
  -H "Cookie: pvgs_session=YOUR_SESSION_ID"
```

## Step 9: Access Frontend

Open browser: `http://localhost:8000`

1. Login with credentials
2. Department selector appears in top navigation
3. Switch departments to test context switching
4. Verify session rotation in browser DevTools

## Verification Checklist

### Database

- [ ] All migrations executed successfully
- [ ] `department_id` column exists in 6 core tables
- [ ] `user_departments` junction table created
- [ ] `department_permissions` table populated
- [ ] `workflow_history` table created
- [ ] `security_audit_logs` table created
- [ ] 18 performance indexes created

### API

- [ ] `/api/v1/departments/{id}/students` returns data
- [ ] `/api/v1/departments/{id}/dashboard` returns metrics
- [ ] Session cookie set as HTTP-only
- [ ] Permission checks use cache (3ms response)
- [ ] Security audit logs created on access

### Frontend

- [ ] Department selector visible
- [ ] Department switching works (<300ms)
- [ ] Navigation menu filters by permissions
- [ ] Data loader injects department context
- [ ] Offline mode works with cached data

### Performance

- [ ] Dashboard loads in <100ms
- [ ] Students query in <50ms
- [ ] Permission checks in <10ms
- [ ] No N+1 queries in logs

### Security

- [ ] Sessions use HTTP-only cookies
- [ ] No sensitive data in localStorage
- [ ] Session timeout after 15 minutes
- [ ] Session rotation on department switch
- [ ] Security audit logs capture all access

## Troubleshooting

### Migration Errors

```bash
# Rollback and retry
php artisan migrate:rollback
php artisan migrate
```

### Permission Issues

```bash
# Clear cache
php artisan cache:clear
php artisan config:clear

# Re-seed permissions
php artisan db:seed --class=DepartmentPermissionsSeeder
```

### Session Issues

```bash
# Create sessions table
php artisan session:table
php artisan migrate

# Clear sessions
php artisan session:flush
```

### Performance Issues

```bash
# Verify indexes
php artisan db:show

# Clear all caches
php artisan optimize:clear
```

## Development Tools

### Artisan Commands

```bash
# List all department commands
php artisan list department

# Available commands:
# department:backfill       - Backfill department_id
# department:assign-users   - Assign users to departments
# department:validate       - Validate data integrity
# department:import-manual  - Import manual assignments
```

### Database Inspection

```bash
# Check department assignments
php artisan tinker
>>> DB::table('students')->whereNull('department_id')->count();
>>> DB::table('user_departments')->count();
```

### Cache Inspection

```bash
# Check permission cache
php artisan tinker
>>> Cache::get('permissions:1:1');
```

## Performance Benchmarking

```bash
# Make script executable
chmod +x scripts/benchmark-department-performance.sh

# Set environment variables
export BASE_URL="http://localhost:8000/api"
export DEPARTMENT_ID="1"
export AUTH_TOKEN="your_session_token"

# Run benchmark
./scripts/benchmark-department-performance.sh
```

Expected results:
- Dashboard: <100ms ✅
- Students: <50ms ✅
- Permissions: <10ms ✅

## Next Steps

1. **Import Real Data**: Use CSV templates to import actual student/faculty data
2. **Configure Workflows**: Customize workflow states in `config/workflows.php`
3. **Assign Department Heads**: Use HOD management endpoints
4. **Test Workflows**: Submit test admission/fee waiver requests
5. **Generate Reports**: Test NAAC compliance reports

## Support

For issues or questions:
1. Check migration status: `php artisan migrate:status`
2. Review logs: `storage/logs/laravel.log`
3. Validate data: `php artisan department:validate`
4. Export failure report: Check `storage/app/department_failures.csv`

---

**Status**: Ready for local development and testing
**Last Updated**: 2024-10-03
