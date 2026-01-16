#!/bin/bash
LOG_FILE="storage/logs/monitoring.log"
ALERT_EMAIL="admin@pvgs.edu"
CHECK_INTERVAL=60

mkdir -p storage/logs

while true; do
    TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
    
    # Check API health
    HEALTH=$(curl -s http://localhost:8000/health.php)
    STATUS=$(echo "$HEALTH" | grep -o '"status":"[^"]*' | cut -d'"' -f4)
    
    if [ "$STATUS" != "healthy" ]; then
        echo "[$TIMESTAMP] ❌ ALERT: System unhealthy" >> "$LOG_FILE"
        echo "System unhealthy at $TIMESTAMP" | mail -s "PVGS ERP Alert" "$ALERT_EMAIL" 2>/dev/null
    else
        echo "[$TIMESTAMP] ✅ System healthy" >> "$LOG_FILE"
    fi
    
    # Check response time
    RESPONSE_TIME=$(curl -s -w "%{time_total}" -o /dev/null http://localhost:8000/direct-api.php/api/departments -H "Authorization: Bearer test")
    if (( $(echo "$RESPONSE_TIME > 0.2" | bc -l) )); then
        echo "[$TIMESTAMP] ⚠️  Slow response: ${RESPONSE_TIME}s" >> "$LOG_FILE"
    fi
    
    sleep $CHECK_INTERVAL
done
