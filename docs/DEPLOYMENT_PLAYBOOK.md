# PVGS ERP - Deployment Playbook

## System Health Status
✅ **Status**: HEALTHY  
✅ **Active Users**: 51  
✅ **Database**: Connected (0.96ms)  
✅ **Disk Space**: 68.98% free (21.61 GB)  
✅ **Tests**: 27/27 passing

---

## Quick Start

### Start Production Server
```bash
cd /var/www/pvgs-erp/public
php -S 0.0.0.0:8000 > /var/log/pvgs-erp.log 2>&1 &
echo $! > /var/run/pvgs-erp.pid
```

### Check Health
```bash
curl http://localhost:8000/health-check.php
```

### Run Tests
```bash
bash scripts/comprehensive-test-suite.sh
```

---

## Daily Operations

### Morning Checklist (9:00 AM)
1. Check system health: `curl http://localhost:8000/health-check.php`
2. Review logs: `tail -100 storage/logs/monitoring.log`
3. Verify active users: Should show 51
4. Check disk space: Should be > 20%

### Evening Checklist (6:00 PM)
1. Run full test suite: `bash scripts/comprehensive-test-suite.sh`
2. Backup database: `bash scripts/backup-database.sh`
3. Review error logs: `grep ERROR storage/logs/*.log`
4. Plan next day maintenance

---

## Backup & Recovery

### Daily Backup (Automated)
```bash
# Add to crontab: 0 2 * * * /var/www/pvgs-erp/scripts/backup-database.sh
#!/bin/bash
DATE=$(date +%Y%m%d)
cp database/database.sqlite backups/db-$DATE.sqlite
find backups/ -name "db-*.sqlite" -mtime +7 -delete
```

### Manual Backup
```bash
cp database/database.sqlite backups/db-manual-$(date +%Y%m%d-%H%M).sqlite
```

### Restore from Backup
```bash
# Stop server
kill $(cat /var/run/pvgs-erp.pid)

# Restore database
cp backups/db-20260116.sqlite database/database.sqlite

# Restart server
cd public && php -S 0.0.0.0:8000 &
```

---

## Emergency Procedures

### If API Not Responding
```bash
# 1. Check if server running
ps aux | grep "php.*8000"

# 2. Check health
curl http://localhost:8000/health-check.php

# 3. Restart server
kill $(cat /var/run/pvgs-erp.pid)
cd /var/www/pvgs-erp/public
php -S 0.0.0.0:8000 &

# 4. Verify
bash scripts/test-direct-api.sh
```

### If Database Corrupted
```bash
# 1. Stop server immediately
kill $(cat /var/run/pvgs-erp.pid)

# 2. Restore from last backup
cp backups/db-$(date +%Y%m%d).sqlite database/database.sqlite

# 3. Verify integrity
sqlite3 database/database.sqlite "PRAGMA integrity_check;"

# 4. Restart
cd public && php -S 0.0.0.0:8000 &
```

### If Disk Full
```bash
# 1. Check disk usage
df -h

# 2. Clean old logs
find storage/logs/ -name "*.log" -mtime +30 -delete

# 3. Clean old backups
find backups/ -name "db-*.sqlite" -mtime +7 -delete

# 4. Verify space
df -h
```

---

## Monitoring

### Real-time Monitoring
```bash
# Start monitoring (runs every 60 seconds)
bash scripts/monitor-production.sh &
echo $! > /var/run/monitor.pid

# View logs
tail -f storage/logs/monitoring.log
```

### Performance Metrics
```bash
# API response time
curl -w "@curl-format.txt" -o /dev/null -s http://localhost:8000/direct-api.php/api/students

# Database query time
time sqlite3 database/database.sqlite "SELECT COUNT(*) FROM users;"

# Concurrent users test
ab -n 100 -c 10 http://localhost:8000/direct-api.php/api/departments
```

---

## Troubleshooting

### Problem: Users Can't Login
**Symptoms**: Login returns 401 error  
**Solution**:
```bash
# Check database
sqlite3 database/database.sqlite "SELECT COUNT(*) FROM users WHERE is_active = 1;"

# Verify password hashing
php -r "echo password_verify('password123', '\$2y\$10\$...');"

# Check JWT secret
grep JWT_SECRET public/direct-api.php
```

### Problem: Slow Response Times
**Symptoms**: API takes > 200ms  
**Solution**:
```bash
# Check database size
ls -lh database/database.sqlite

# Optimize database
sqlite3 database/database.sqlite "VACUUM;"

# Check indexes
sqlite3 database/database.sqlite ".schema" | grep INDEX
```

### Problem: Department Data Not Showing
**Symptoms**: Empty results for department queries  
**Solution**:
```bash
# Verify department data
sqlite3 database/database.sqlite "SELECT * FROM departments;"

# Check student-department links
sqlite3 database/database.sqlite "SELECT COUNT(*), department_id FROM students GROUP BY department_id;"
```

---

## User Management

### Reset User Password
```bash
sqlite3 database/database.sqlite "UPDATE users SET password = '\$2y\$10\$...' WHERE email = 'user@pvgs.edu';"
```

### Add New User
```bash
sqlite3 database/database.sqlite "INSERT INTO users (name, email, password, user_type, role, is_active, created_at, updated_at) VALUES ('New User', 'new@pvgs.edu', '\$2y\$10\$...', 'faculty', 'faculty', 1, datetime('now'), datetime('now'));"
```

### Deactivate User
```bash
sqlite3 database/database.sqlite "UPDATE users SET is_active = 0 WHERE email = 'user@pvgs.edu';"
```

---

## Security

### Change JWT Secret
```bash
# 1. Generate new secret (min 64 chars)
openssl rand -base64 64

# 2. Update in code
sed -i 's/JWT_SECRET, .*/JWT_SECRET, "NEW_SECRET_HERE");/' public/direct-api.php

# 3. All users must re-login
```

### Enable HTTPS
```bash
# Install certbot
apt-get install certbot

# Get certificate
certbot certonly --standalone -d erp.pvgs.edu

# Update nginx config
# ssl_certificate /etc/letsencrypt/live/erp.pvgs.edu/fullchain.pem;
# ssl_certificate_key /etc/letsencrypt/live/erp.pvgs.edu/privkey.pem;
```

---

## Contact Information

**System Administrator**: admin@pvgs.edu  
**Emergency Contact**: +91-XXXXXXXXXX  
**Backup Admin**: backup@pvgs.edu

---

## Maintenance Windows

**Daily**: 2:00 AM - 2:30 AM (Automated backups)  
**Weekly**: Sunday 1:00 AM - 3:00 AM (System updates)  
**Monthly**: First Sunday 12:00 AM - 4:00 AM (Major updates)

---

**Last Updated**: 2026-01-16  
**Version**: 1.0.0  
**Status**: Production Ready
