#!/bin/bash

# URL Validator - Check if cached URL still works

CACHE_FILE="$(dirname "$0")/www/dev/tv2-stream-url.txt"

echo "=========================================="
echo "RTM TV2 URL Validator"
echo "=========================================="
echo ""

if [ ! -f "$CACHE_FILE" ]; then
    echo "❌ No cached URL found: $CACHE_FILE"
    echo ""
    echo "Run extraction first:"
    echo "  ./extract-url-auto.sh"
    exit 1
fi

URL=$(cat "$CACHE_FILE")

if [ -z "$URL" ]; then
    echo "❌ Cache file is empty"
    exit 1
fi

echo "Cached URL:"
echo "$URL"
echo ""

echo "Testing URL..."
echo ""

# Test with HTTP HEAD request
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$URL" --max-time 10)

echo "HTTP Response: $HTTP_CODE"
echo ""

if [ "$HTTP_CODE" = "200" ]; then
    echo "✅ URL is VALID and working!"
    echo ""
    
    # Get file age
    FILE_AGE=$(( ($(date +%s) - $(stat -c %Y "$CACHE_FILE" 2>/dev/null || stat -f %m "$CACHE_FILE")) / 3600 ))
    echo "URL age: $FILE_AGE hours"
    
    if [ $FILE_AGE -gt 12 ]; then
        echo "⚠️ URL is quite old. Consider refreshing:"
        echo "  ./extract-url-auto.sh"
    fi
    
    echo ""
    exit 0
    
elif [ "$HTTP_CODE" = "403" ]; then
    echo "❌ URL EXPIRED or FORBIDDEN (403)"
    echo ""
    echo "Extract a fresh URL:"
    echo "  ./extract-url-auto.sh"
    exit 1
    
elif [ "$HTTP_CODE" = "404" ]; then
    echo "❌ URL NOT FOUND (404)"
    echo ""
    echo "Extract a fresh URL:"
    echo "  ./extract-url-auto.sh"
    exit 1
    
elif [ "$HTTP_CODE" = "000" ]; then
    echo "❌ CONNECTION FAILED (timeout or network error)"
    echo ""
    echo "Check:"
    echo "1. Internet connection: ping google.com"
    echo "2. RTM website: curl -I https://rtmklik.rtm.gov.my"
    exit 1
    
else
    echo "⚠️ Unexpected response: $HTTP_CODE"
    echo ""
    echo "URL might not be working. Extract a fresh one:"
    echo "  ./extract-url-auto.sh"
    exit 1
fi

