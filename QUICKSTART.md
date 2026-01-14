# 🚀 Quick Start - PVGS ERP System

## System is Running!

**Server**: http://localhost:8000

## Available Pages

### Authentication
- **Login**: http://localhost:8000/secure_login.html

### Role Dashboards
- **Super Admin**: http://localhost:8000/secure_super_admin.html
- **Principal**: http://localhost:8000/secure_principal.html
- **Admin/Registrar**: http://localhost:8000/secure_admin.html
- **Faculty**: http://localhost:8000/secure_faculty.html
- **Student**: http://localhost:8000/secure_student.html

### Legacy Pages (for reference)
- http://localhost:8000/admin.html
- http://localhost:8000/principal.html
- http://localhost:8000/faculty.html
- http://localhost:8000/student.html

## Test the System

### 1. Open Login Page
```bash
open http://localhost:8000/secure_login.html
```

### 2. Test API Endpoints

**Check Server Status**:
```bash
curl http://localhost:8000/
```

**Test Department API** (requires authentication):
```bash
curl http://localhost:8000/api/v1/departments/1/students
```

## Database Operations

### Run Migrations
```bash
php -d xdebug.mode=off artisan migrate
```

### Check Migration Status
```bash
php -d xdebug.mode=off artisan migrate:status
```

### Seed Data
```bash
php -d xdebug.mode=off db:seed --class=DepartmentPermissionsSeeder
```

## Department Commands

### Backfill Department Data
```bash
php -d xdebug.mode=off artisan department:backfill
```

### Assign Users to Departments
```bash
php -d xdebug.mode=off artisan department:assign-users
```

### Validate Data
```bash
php -d xdebug.mode=off artisan department:validate
```

## Stop Server

```bash
pkill -f "php.*8000"
```

## Restart Server

```bash
pkill -f "php.*8000"
php -d xdebug.mode=off -S 0.0.0.0:8000 -t public/ &
```

## View Server Logs

```bash
tail -f /tmp/server.log
```

## Troubleshooting

### Port Already in Use
```bash
# Find process
lsof -i :8000

# Kill process
kill -9 <PID>
```

### Database Issues
```bash
# Check database file
ls -lh database/database.sqlite

# Create if missing
touch database/database.sqlite
chmod 664 database/database.sqlite
```

### Permission Issues
```bash
chmod -R 775 storage bootstrap/cache
```

## Next Steps

1. ✅ Server running on port 8000
2. Run migrations to set up database
3. Seed initial data
4. Test login and department switching
5. Review API documentation: `development/api_contracts.md`

## Documentation

- **Setup Guide**: LOCAL_SETUP_GUIDE.md
- **API Contracts**: development/api_contracts.md
- **Security & Performance**: docs/SECURITY_PERFORMANCE_COMPLETE.md
- **Department Features**: docs/DEPARTMENT_TRANSFORMATION_SUMMARY.md

---

**Status**: ✅ Running
**Port**: 8000
**Database**: SQLite (database/database.sqlite)
