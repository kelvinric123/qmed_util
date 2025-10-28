#!/bin/bash

# Extract RTM TV2 Stream URL
# This script extracts the direct HLS stream URL from RTM Klik

echo "========================================"
echo "RTM TV2 Stream URL Extractor"
echo "========================================"
echo ""

# Check if yt-dlp is installed
if ! command -v yt-dlp &> /dev/null; then
    echo "❌ Error: yt-dlp is not installed"
    echo ""
    echo "To install yt-dlp, run:"
    echo "  pip3 install yt-dlp"
    echo "  or"
    echo "  sudo pip3 install yt-dlp"
    echo ""
    exit 1
fi

echo "✓ yt-dlp found"
echo ""
echo "Extracting stream URL from RTM Klik..."
echo ""

# Extract the stream URL
STREAM_URL=$(yt-dlp -g "https://rtmklik.rtm.gov.my/live/tv2" 2>/dev/null | head -n 1)

if [ -z "$STREAM_URL" ]; then
    echo "❌ Failed to extract stream URL"
    echo ""
    echo "Please check:"
    echo "  1. Internet connection"
    echo "  2. RTM Klik website is accessible"
    echo "  3. yt-dlp is up to date (run: pip3 install -U yt-dlp)"
    echo ""
    exit 1
fi

echo "✅ Stream URL extracted successfully!"
echo ""
echo "URL: $STREAM_URL"
echo ""

# Save to cache file
CACHE_FILE="$(dirname "$0")/../www/dev/tv2-stream-url.txt"
echo "$STREAM_URL" > "$CACHE_FILE"

echo "✓ URL saved to cache: $CACHE_FILE"
echo ""
echo "========================================"
echo "Setup Complete!"
echo "========================================"
echo ""
echo "The Live TV2 player is now ready to use."
echo ""

