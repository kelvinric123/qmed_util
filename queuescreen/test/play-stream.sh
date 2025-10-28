#!/bin/bash

# RTM TV2 Stream Player for Raspberry Pi
# This script extracts and plays the direct stream URL

# Arguments (for positioning the video window)
X=${1:-0}
Y=${2:-0}
WIDTH=${3:-1920}
HEIGHT=${4:-1080}
VOLUME=${5:-0}  # Volume for omxplayer (-millibels, 0 = max)

echo "RTM TV2 Stream Player"
echo "Position: X=$X, Y=$Y, Size: ${WIDTH}x${HEIGHT}"

# Method 1: Try to get direct stream URL using yt-dlp
echo "Attempting to extract stream URL..."

# Check if yt-dlp is installed
if command -v yt-dlp &> /dev/null; then
    echo "✓ yt-dlp found, extracting stream URL..."
    STREAM_URL=$(yt-dlp -g "https://rtmklik.rtm.gov.my/live/tv2" 2>/dev/null | head -n 1)
    
    if [ ! -z "$STREAM_URL" ]; then
        echo "✓ Stream URL extracted: $STREAM_URL"
        
        # Kill any existing omxplayer instances
        pkill -f omxplayer
        
        # Play with omxplayer (lightweight, hardware accelerated)
        echo "Starting omxplayer..."
        omxplayer \
            --win "$Y $X $WIDTH $HEIGHT" \
            --live \
            --vol -$VOLUME \
            --timeout 60 \
            "$STREAM_URL"
        
        exit 0
    else
        echo "✗ Failed to extract stream URL"
    fi
else
    echo "✗ yt-dlp not installed"
    echo "  Install with: pip3 install yt-dlp"
fi

# Method 2: Try with youtube-dl (fallback)
if command -v youtube-dl &> /dev/null; then
    echo "Trying youtube-dl..."
    STREAM_URL=$(youtube-dl -g "https://rtmklik.rtm.gov.my/live/tv2" 2>/dev/null | head -n 1)
    
    if [ ! -z "$STREAM_URL" ]; then
        echo "✓ Stream URL extracted: $STREAM_URL"
        pkill -f omxplayer
        omxplayer --win "$Y $X $WIDTH $HEIGHT" --live --vol -$VOLUME "$STREAM_URL"
        exit 0
    fi
fi

# Method 3: Use hardcoded m3u8 URL (FOUND!)
# ✅ URL extracted from RTM Klik on 2025-10-28
STREAM_URL="https://d25tgymtnqzu8s.cloudfront.net/smil:tv2/chunklist_b2596000_slENG.m3u8?id=2"

echo "✓ Using cached stream URL"
echo "  Playing: $STREAM_URL"

pkill -f omxplayer
omxplayer --win "$Y $X $WIDTH $HEIGHT" --live --vol -$VOLUME "$STREAM_URL"
exit 0

# Method 4: Fallback to Chromium browser in kiosk mode
echo "Falling back to Chromium browser..."

# Kill existing chromium instances for RTM
pkill -f "chromium.*rtmklik"

# Launch Chromium in kiosk mode
DISPLAY=:0 chromium-browser \
    --window-position=$X,$Y \
    --window-size=$WIDTH,$HEIGHT \
    --kiosk \
    --noerrdialogs \
    --disable-infobars \
    --no-first-run \
    --autoplay-policy=no-user-gesture-required \
    --disable-session-crashed-bubble \
    "https://rtmklik.rtm.gov.my/live/tv2" &

echo "✓ Stream opened in Chromium"
echo "Note: This method may show ads. Use Method 1 (yt-dlp) for ad-free experience."

# Save the PID for later control
echo $! > /tmp/rtm-stream.pid

