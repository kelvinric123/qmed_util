#!/bin/bash

# Automatic URL Extractor - Tries all methods in order
# This is the main extraction script you should use

echo "=========================================="
echo "RTM TV2 Automatic URL Extractor"
echo "=========================================="
echo ""
echo "This script will try multiple methods to extract the stream URL."
echo ""

SCRIPT_DIR="$(dirname "$0")"
CACHE_FILE="$SCRIPT_DIR/www/dev/tv2-stream-url.txt"

# Method 1: Try yt-dlp (fastest if working)
echo "Method 1: Trying yt-dlp..."
if command -v yt-dlp &> /dev/null; then
    STREAM_URL=$(yt-dlp -g "https://rtmklik.rtm.gov.my/live/tv2" 2>/dev/null | head -n 1)
    
    if [ ! -z "$STREAM_URL" ]; then
        echo "  ✅ Success with yt-dlp!"
        echo ""
        echo "$STREAM_URL" > "$CACHE_FILE"
        echo "Stream URL: $STREAM_URL"
        echo ""
        echo "✓ URL saved to: $CACHE_FILE"
        exit 0
    else
        echo "  ❌ yt-dlp failed"
    fi
else
    echo "  ⚠️ yt-dlp not installed"
fi
echo ""

# Method 2: Try Python smart extractor
echo "Method 2: Trying Python smart extractor..."
if [ -f "$SCRIPT_DIR/extract-url-smart.py" ]; then
    chmod +x "$SCRIPT_DIR/extract-url-smart.py"
    
    if python3 "$SCRIPT_DIR/extract-url-smart.py"; then
        echo ""
        echo "✅ Extraction successful!"
        exit 0
    else
        echo "  ❌ Python extractor failed"
    fi
else
    echo "  ⚠️ extract-url-smart.py not found"
fi
echo ""

# Method 3: Try curl + grep method
echo "Method 3: Trying curl + grep..."
if [ -f "$SCRIPT_DIR/extract-url-alternative.sh" ]; then
    chmod +x "$SCRIPT_DIR/extract-url-alternative.sh"
    
    if bash "$SCRIPT_DIR/extract-url-alternative.sh"; then
        echo ""
        echo "✅ Extraction successful!"
        exit 0
    else
        echo "  ❌ Alternative extractor failed"
    fi
else
    echo "  ⚠️ extract-url-alternative.sh not found"
fi
echo ""

# Method 4: Try youtube-dl
echo "Method 4: Trying youtube-dl..."
if command -v youtube-dl &> /dev/null; then
    STREAM_URL=$(youtube-dl -g "https://rtmklik.rtm.gov.my/live/tv2" 2>/dev/null | head -n 1)
    
    if [ ! -z "$STREAM_URL" ]; then
        echo "  ✅ Success with youtube-dl!"
        echo ""
        echo "$STREAM_URL" > "$CACHE_FILE"
        echo "Stream URL: $STREAM_URL"
        echo ""
        echo "✓ URL saved to: $CACHE_FILE"
        exit 0
    else
        echo "  ❌ youtube-dl failed"
    fi
else
    echo "  ⚠️ youtube-dl not installed"
fi
echo ""

# All automatic methods failed
echo "=========================================="
echo "❌ All automatic methods failed"
echo "=========================================="
echo ""
echo "Options:"
echo ""
echo "1. Use browser-based extraction:"
echo "   bash extract-url-chromium.sh"
echo ""
echo "2. Manual extraction (most reliable):"
echo "   cat extract-url-manual-guide.md"
echo ""
echo "3. Use last known working URL:"
echo "   bash quick-test-with-sample-url.sh"
echo ""
echo "The most reliable method is manual extraction using"
echo "browser Developer Tools (F12 → Network tab)."
echo ""

exit 1

