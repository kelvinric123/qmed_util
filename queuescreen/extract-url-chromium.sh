#!/bin/bash

# Smart URL Extractor - Uses Chromium to capture the actual stream URL
# This is the most reliable method as it works exactly like a real browser

echo "=========================================="
echo "RTM TV2 Smart URL Extractor (Chromium)"
echo "=========================================="
echo ""

CACHE_FILE="$(dirname "$0")/www/dev/tv2-stream-url.txt"
CACHE_DIR=$(dirname "$CACHE_FILE")
CAPTURE_FILE="/tmp/rtm-tv2-capture.txt"

if [ ! -d "$CACHE_DIR" ]; then
    mkdir -p "$CACHE_DIR"
fi

# Check if chromium is installed
if ! command -v chromium-browser &> /dev/null; then
    echo "❌ Chromium not found. Please install:"
    echo "   sudo apt install chromium-browser -y"
    exit 1
fi

echo "✓ Chromium found"
echo ""
echo "Method: Browser network capture"
echo ""
echo "Instructions:"
echo "1. Chromium will open the RTM TV2 page"
echo "2. Click the PLAY button on the video"
echo "3. Wait 5-10 seconds for video to start"
echo "4. Close the browser window (or press Ctrl+C here)"
echo "5. The script will extract the URL automatically"
echo ""
echo "Press Enter to continue..."
read

# Clear any previous capture
rm -f "$CAPTURE_FILE"

echo "Opening RTM TV2 page with network logging..."
echo ""

# Run chromium with network logging
# This captures all network requests including the m3u8 URLs
chromium-browser \
    --enable-logging \
    --log-net-log=/tmp/rtm-netlog.json \
    --user-data-dir=/tmp/rtm-chrome-session \
    "https://rtmklik.rtm.gov.my/live/tv2" &

CHROME_PID=$!

echo "Chromium PID: $CHROME_PID"
echo ""
echo "Waiting for you to play the video..."
echo "Close the browser when video starts playing..."
echo ""

# Wait for chromium to close
wait $CHROME_PID

echo ""
echo "Browser closed. Extracting URL from network log..."
echo ""

# Extract m3u8 URLs from the network log
if [ -f "/tmp/rtm-netlog.json" ]; then
    # Look for m3u8 URLs in the network log
    STREAM_URL=$(grep -oP 'https?://[^"'\'']+\.m3u8[^"'\'']*' /tmp/rtm-netlog.json | grep -v "master.m3u8" | head -n 1)
    
    if [ ! -z "$STREAM_URL" ]; then
        echo "✅ Stream URL extracted!"
        echo ""
        echo "URL: $STREAM_URL"
        echo ""
        
        echo "$STREAM_URL" > "$CACHE_FILE"
        echo "✓ URL saved to: $CACHE_FILE"
        echo ""
        
        # Clean up
        rm -f /tmp/rtm-netlog.json
        rm -rf /tmp/rtm-chrome-session
        
        echo "=========================================="
        echo "Success! You can now test the player:"
        echo "  ./quick-test-with-sample-url.sh"
        echo "  or"
        echo "  chromium-browser test-player-simple.html"
        echo "=========================================="
        exit 0
    fi
fi

echo "⚠️ Could not extract URL from network log."
echo ""
echo "Alternative method: Manual extraction"
echo "1. Open RTM TV2 in browser"
echo "2. Press F12 → Network tab"
echo "3. Filter by 'm3u8'"
echo "4. Play video and copy the .m3u8 URL"
echo ""

# Clean up
rm -f /tmp/rtm-netlog.json
rm -rf /tmp/rtm-chrome-session

exit 1

