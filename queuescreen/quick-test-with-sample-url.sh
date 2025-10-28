#!/bin/bash

# Quick Test - Uses a sample URL to test the player immediately
# No yt-dlp needed!

echo "=========================================="
echo "Quick Player Test (Sample URL)"
echo "=========================================="
echo ""
echo "This script will test the player with a sample URL."
echo "Note: Sample URLs may expire after a few hours."
echo ""

# Sample URL (you can replace this with a current one)
SAMPLE_URL="https://d25tgymtnqzu8s.cloudfront.net/smil:tv2/chunklist_b2596000_slENG.m3u8?id=2"

echo "Using sample URL:"
echo "$SAMPLE_URL"
echo ""

# Save to cache
CACHE_FILE="$(dirname "$0")/www/dev/tv2-stream-url.txt"
CACHE_DIR=$(dirname "$CACHE_FILE")

if [ ! -d "$CACHE_DIR" ]; then
    mkdir -p "$CACHE_DIR"
fi

echo "$SAMPLE_URL" > "$CACHE_FILE"

echo "✅ URL saved to: $CACHE_FILE"
echo ""
echo "=========================================="
echo "Testing stream..."
echo "=========================================="
echo ""

# Test if omxplayer is available
if command -v omxplayer &> /dev/null; then
    echo "Playing with omxplayer (Press Ctrl+C to stop)..."
    echo ""
    omxplayer --live "$SAMPLE_URL"
else
    echo "omxplayer not found. Opening in browser..."
    echo ""
    
    HTML_FILE="$(dirname "$0")/test-player-simple.html"
    
    if [ -f "$HTML_FILE" ]; then
        if command -v chromium-browser &> /dev/null; then
            chromium-browser "$HTML_FILE"
        elif command -v firefox &> /dev/null; then
            firefox "$HTML_FILE"
        else
            echo "Please open this file in a browser:"
            echo "file://$(realpath $HTML_FILE)"
        fi
    else
        echo "Test player not found. URL is ready at:"
        echo "$CACHE_FILE"
    fi
fi

echo ""
echo "=========================================="
echo "Test complete!"
echo "=========================================="
echo ""
echo "If the stream doesn't work:"
echo "1. The sample URL may have expired"
echo "2. Extract a fresh URL using browser developer tools"
echo "3. See: extract-url-manual-guide.md"
echo ""

