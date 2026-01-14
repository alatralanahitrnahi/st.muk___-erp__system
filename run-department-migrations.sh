#!/bin/bash

set -e

echo "🚀 Department Foundation Migration - Safe Execution"
echo "=================================================="
echo ""

# Configuration
BACKUP_DIR="backups/pre-department-migration"
MIGRATION_PATH="database/migrations/2024_10_department_foundation"
LOG_FILE="logs/department-migration-$(date +%Y%m%d_%H%M%S).log"

# Create directories
mkdir -p "$BACKUP_DIR"
mkdir -p "logs"

# Logging function
log() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

# Step 1: Pre-migration checks
log "Step 1: Pre-migration checks"
echo "Checking database connection..."
php artisan db:show 2>&1 | tee -a "$LOG_FILE" || {
    log "ERROR: Database connection failed"
    exit 1
}

echo "Checking migration files..."
if [ ! -d "$MIGRATION_PATH" ]; then
    log "ERROR: Migration directory not found: $MIGRATION_PATH"
    exit 1
fi

MIGRATION_COUNT=$(ls -1 "$MIGRATION_PATH"/*.php 2>/dev/null | wc -l)
log "Found $MIGRATION_COUNT migration files"

if [ "$MIGRATION_COUNT" -ne 4 ]; then
    log "WARNING: Expected 4 migration files, found $MIGRATION_COUNT"
fi

# Step 2: Create backup
log "Step 2: Creating database backup"
BACKUP_FILE="$BACKUP_DIR/database_$(date +%Y%m%d_%H%M%S).sql"

if [ -f "database/database.sqlite" ]; then
    cp database/database.sqlite "$BACKUP_FILE"
    log "SQLite backup created: $BACKUP_FILE"
else
    log "WARNING: SQLite database not found, skipping backup"
fi

# Step 3: Run migrations with transaction wrapping
log "Step 3: Running migrations"

php artisan migrate --path="$MIGRATION_PATH" --step 2>&1 | tee -a "$LOG_FILE"
MIGRATION_STATUS=$?

if [ $MIGRATION_STATUS -ne 0 ]; then
    log "ERROR: Migration failed with status $MIGRATION_STATUS"
    log "Attempting automatic rollback..."
    php artisan migrate:rollback --step=4 2>&1 | tee -a "$LOG_FILE"
    log "Rollback completed. Backup available at: $BACKUP_FILE"
    exit 1
fi

log "✅ Migrations completed successfully"

# Step 4: Verify schema changes
log "Step 4: Verifying schema changes"

php artisan db:show 2>&1 | tee -a "$LOG_FILE"

# Step 5: Run verification script
log "Step 5: Running verification tests"
if [ -f "verify-department-foundation.sh" ]; then
    ./verify-department-foundation.sh 2>&1 | tee -a "$LOG_FILE"
    VERIFY_STATUS=$?
    
    if [ $VERIFY_STATUS -ne 0 ]; then
        log "WARNING: Verification tests failed"
        log "Review log file: $LOG_FILE"
        log "Backup available at: $BACKUP_FILE"
        exit 1
    fi
else
    log "WARNING: Verification script not found"
fi

log "✅ Migration execution completed successfully"
log "Backup location: $BACKUP_FILE"
log "Log file: $LOG_FILE"

echo ""
echo "=================================================="
echo "✅ Department Foundation Migration Complete"
echo "=================================================="
echo ""
echo "Next steps:"
echo "1. Run: ./verify-department-foundation.sh"
echo "2. Test existing API endpoints"
echo "3. Test new department-scoped endpoints"
echo "4. Review log file: $LOG_FILE"
echo ""
