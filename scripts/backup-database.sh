#!/bin/bash
BACKUP_DIR="backups"
DATE=$(date +%Y%m%d-%H%M)
DB_FILE="database/database.sqlite"

mkdir -p "$BACKUP_DIR"

echo "🔄 Starting database backup..."
cp "$DB_FILE" "$BACKUP_DIR/db-$DATE.sqlite"

if [ $? -eq 0 ]; then
    echo "✅ Backup created: $BACKUP_DIR/db-$DATE.sqlite"
    
    # Clean old backups (keep last 7 days)
    find "$BACKUP_DIR" -name "db-*.sqlite" -mtime +7 -delete
    echo "🧹 Cleaned old backups (>7 days)"
    
    # Show backup size
    SIZE=$(du -h "$BACKUP_DIR/db-$DATE.sqlite" | cut -f1)
    echo "📊 Backup size: $SIZE"
else
    echo "❌ Backup failed!"
    exit 1
fi
