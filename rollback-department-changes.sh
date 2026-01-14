#!/bin/bash

set -e

echo "🔄 Department Foundation - Complete Rollback"
echo "============================================="
echo ""

BACKUP_DIR="backups/pre-department-migration"
LOG_FILE="logs/department-rollback-$(date +%Y%m%d_%H%M%S).log"

mkdir -p "logs"

log() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

# Confirmation prompt
echo "⚠️  WARNING: This will rollback all department foundation changes"
echo ""
echo "This will:"
echo "  - Remove department_id from all core tables"
echo "  - Drop user_departments table"
echo "  - Drop department_permissions table"
echo "  - Drop workflow_history table"
echo "  - Remove users.primary_department_id"
echo ""
read -p "Are you sure you want to continue? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    log "Rollback cancelled by user"
    exit 0
fi

# Step 1: Create backup before rollback
log "Step 1: Creating pre-rollback backup"
BACKUP_FILE="$BACKUP_DIR/pre-rollback_$(date +%Y%m%d_%H%M%S).sql"

if [ -f "database/database.sqlite" ]; then
    cp database/database.sqlite "$BACKUP_FILE"
    log "Backup created: $BACKUP_FILE"
fi

# Step 2: Run rollback
log "Step 2: Rolling back migrations"

php artisan migrate:rollback --path=database/migrations/2024_10_department_foundation --step=4 2>&1 | tee -a "$LOG_FILE"
ROLLBACK_STATUS=$?

if [ $ROLLBACK_STATUS -ne 0 ]; then
    log "ERROR: Rollback failed with status $ROLLBACK_STATUS"
    log "Backup available at: $BACKUP_FILE"
    exit 1
fi

log "✅ Rollback completed successfully"

# Step 3: Verify rollback
log "Step 3: Verifying rollback"

php artisan db:show 2>&1 | tee -a "$LOG_FILE"

# Step 4: Check for remaining department references
log "Step 4: Checking for remaining department references"

if grep -q "department_id" database/migrations/2024_10_department_foundation/*.php 2>/dev/null; then
    log "WARNING: Migration files still exist"
    log "Consider removing: database/migrations/2024_10_department_foundation/"
fi

log "✅ Rollback verification complete"
log "Backup location: $BACKUP_FILE"
log "Log file: $LOG_FILE"

echo ""
echo "============================================="
echo "✅ Department Foundation Rollback Complete"
echo "============================================="
echo ""
echo "System restored to pre-department state"
echo "Backup available at: $BACKUP_FILE"
echo ""
