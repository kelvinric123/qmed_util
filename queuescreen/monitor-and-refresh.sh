#!/bin/bash

# Monitor and Auto-Refresh - Checks URL validity and auto-refreshes if expired

CACHE_FILE="$(dirname "$0")/www/dev/tv2-stream-url.txt"
LOG_FILE="$(dirname "$0")/tmp/url-refresh.log"

mkdir -p "$(dirname "$LOG_FILE")"

log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

log_message "======================================"
log_message "URL Monitor and Auto-Refresh"
log_message "======================================"

# Check if cache file exists
if [ ! -f "$CACHE_FILE" ]; then
    log_message "❌ No cached URL found. Running initial extraction..."
    ./extract-url-auto.sh
    exit $?
fi

URL=$(cat "$CACHE_FILE")

# Validate current URL
log_message "Testing cached URL..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$URL" --max-time 10)

log_message "HTTP Response: $HTTP_CODE"

if [ "$HTTP_CODE" = "200" ]; then
    log_message "✅ URL is valid. No refresh needed."
    
    # Check age
    FILE_AGE=$(( ($(date +%s) - $(stat -c %Y "$CACHE_FILE" 2>/dev/null || stat -f %m "$CACHE_FILE")) / 3600 ))
    log_message "URL age: $FILE_AGE hours"
    
    if [ $FILE_AGE -gt 6 ]; then
        log_message "⚠️ URL is older than 6 hours. Refreshing as precaution..."
        ./extract-url-auto.sh >> "$LOG_FILE" 2>&1
        log_message "Refresh attempt completed."
    fi
    
    exit 0
else
    log_message "❌ URL is invalid (HTTP $HTTP_CODE). Refreshing..."
    
    # Backup old URL
    BACKUP_FILE="$CACHE_FILE.backup"
    cp "$CACHE_FILE" "$BACKUP_FILE"
    log_message "Old URL backed up to: $BACKUP_FILE"
    
    # Extract fresh URL
    ./extract-url-auto.sh >> "$LOG_FILE" 2>&1
    
    # Check if extraction succeeded
    if [ -f "$CACHE_FILE" ]; then
        NEW_URL=$(cat "$CACHE_FILE")
        if [ "$NEW_URL" != "$URL" ]; then
            log_message "✅ New URL extracted successfully!"
            log_message "Old: ${URL:0:60}..."
            log_message "New: ${NEW_URL:0:60}..."
        else
            log_message "⚠️ Same URL extracted. Method 5 may be using cached pattern."
        fi
    else
        log_message "❌ Extraction failed. Restoring backup."
        cp "$BACKUP_FILE" "$CACHE_FILE"
    fi
    
    exit 0
fi

