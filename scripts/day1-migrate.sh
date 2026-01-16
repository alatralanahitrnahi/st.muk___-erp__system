#!/bin/bash
# Day 1: Database Migration & Seeding
# Execution Time: 45 minutes

set -e

echo "=========================================="
echo "PVGS ERP - Database Migration"
echo "=========================================="

cd /workspaces/st.muk___-erp__system

# Step 1: Check Migration Status
echo "[1/5] Checking migration status..."
php artisan migrate:status --no-interaction 2>&1 | tee logs/migrate-status-before.log

# Step 2: Run Fresh Migrations
echo "[2/5] Running fresh migrations..."
php artisan migrate:fresh --no-interaction 2>&1 | tee logs/migrate-fresh.log

if [ $? -ne 0 ]; then
    echo "❌ Migration failed! Check logs/migrate-fresh.log"
    exit 1
fi

# Step 3: Verify Database Structure
echo "[3/5] Verifying database structure..."
sqlite3 database/database.sqlite ".tables" > logs/tables-list.txt
TABLE_COUNT=$(sqlite3 database/database.sqlite ".tables" | wc -w)
echo "✅ Created $TABLE_COUNT tables"

# Step 4: Run Seeders
echo "[4/5] Running database seeders..."
php artisan db:seed --no-interaction 2>&1 | tee logs/db-seed.log

if [ $? -ne 0 ]; then
    echo "⚠️  Seeding failed - continuing with empty database"
fi

# Step 5: Verify Data
echo "[5/5] Verifying seeded data..."
echo "Users: $(sqlite3 database/database.sqlite 'SELECT COUNT(*) FROM users;')"
echo "Departments: $(sqlite3 database/database.sqlite 'SELECT COUNT(*) FROM departments;')"
echo "Roles: $(sqlite3 database/database.sqlite 'SELECT COUNT(*) FROM roles;')"

echo ""
echo "=========================================="
echo "✅ Database Ready!"
echo "=========================================="
echo "Next: Run 'bash scripts/day1-test.sh'"
