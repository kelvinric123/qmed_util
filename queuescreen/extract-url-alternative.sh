#!/bin/bash

# Alternative TV2 Stream URL Extractor
# Uses curl + grep instead of yt-dlp

echo "=========================================="
echo "RTM TV2 Stream URL Extractor (Alternative)"
echo "=========================================="
echo ""

URL="https://rtmklik.rtm.gov.my/live/tv2"

echo "Method 1: Fetching page source..."
echo ""

# Fetch the page and try to find m3u8 URLs
PAGE_CONTENT=$(curl -s -L "$URL")

# Look for m3u8 URLs in the page
STREAM_URL=$(echo "$PAGE_CONTENT" | grep -oP 'https?://[^"'\'']+\.m3u8[^"'\'']*' | head -n 1)

if [ ! -z "$STREAM_URL" ]; then
    echo "✅ Found stream URL!"
    echo ""
    echo "URL: $STREAM_URL"
    echo ""
    
    # Save to cache
    CACHE_FILE="$(dirname "$0")/www/dev/tv2-stream-url.txt"
    echo "$STREAM_URL" > "$CACHE_FILE"
    echo "✓ URL saved to cache: $CACHE_FILE"
    echo ""
    exit 0
fi

echo "Method 1 failed. Trying Method 2..."
echo ""

# Method 2: Try to find data-stream or similar attributes
STREAM_URL=$(echo "$PAGE_CONTENT" | grep -oP 'data-stream="?\K[^"'\'' ]+\.m3u8[^"'\'' ]*' | head -n 1)

if [ ! -z "$STREAM_URL" ]; then
    echo "✅ Found stream URL (Method 2)!"
    echo ""
    echo "URL: $STREAM_URL"
    echo ""
    
    CACHE_FILE="$(dirname "$0")/www/dev/tv2-stream-url.txt"
    echo "$STREAM_URL" > "$CACHE_FILE"
    echo "✓ URL saved to cache: $CACHE_FILE"
    echo ""
    exit 0
fi

echo "Method 2 failed. Trying Method 3..."
echo ""

# Method 3: Look for common CDN patterns
STREAM_URL=$(echo "$PAGE_CONTENT" | grep -oP 'https?://d[0-9a-z]+\.cloudfront\.net[^"'\'']+\.m3u8[^"'\'']*' | head -n 1)

if [ ! -z "$STREAM_URL" ]; then
    echo "✅ Found stream URL (Method 3)!"
    echo ""
    echo "URL: $STREAM_URL"
    echo ""
    
    CACHE_FILE="$(dirname "$0")/www/dev/tv2-stream-url.txt"
    echo "$STREAM_URL" > "$CACHE_FILE"
    echo "✓ URL saved to cache: $CACHE_FILE"
    echo ""
    exit 0
fi

echo "=========================================="
echo "❌ All methods failed"
echo "=========================================="
echo ""
echo "This might be because:"
echo "1. RTM website structure changed"
echo "2. Stream is embedded via JavaScript"
echo "3. Geo-restrictions are in place"
echo ""
echo "Alternative solutions:"
echo "1. Use browser developer tools to find the URL"
echo "2. Use a known working URL manually"
echo "3. Try youtube-dl instead of yt-dlp"
echo ""
echo "Manual extraction steps:"
echo "1. Open: $URL"
echo "2. Right-click → Inspect Element"
echo "3. Go to Network tab"
echo "4. Filter by 'm3u8'"
echo "5. Play the video and copy the .m3u8 URL"
echo ""

exit 1

