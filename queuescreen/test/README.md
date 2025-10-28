# RTM TV Stream Test

This folder contains test files for implementing RTM TV2 live streaming without the main RTM website wrapper (no ads, just video).

## Files

- `index.html` - Simple test page to view the stream
- `stream-player.html` - Full-screen stream player
- `extract-stream-url.js` - Node.js script to extract direct m3u8 URL
- `play-stream.sh` - Shell script to play stream using omxplayer/VLC
- `play-stream.php` - PHP wrapper for integration with queuescreen system

## Testing on Windows (Development)

1. Open `stream-player.html` in a browser
2. Or serve via Laragon: `http://localhost/qmed-util/qmed-utils/queuescreen/test/`

## Deployment on Raspberry Pi

### Option 1: Extract Direct Stream URL
```bash
# Install yt-dlp
pip3 install yt-dlp

# Extract stream URL
yt-dlp -g "https://rtmklik.rtm.gov.my/live/tv2"

# Copy the URL and update play-stream.sh
```

### Option 2: Use the Full Page (with ad-skip attempts)
```bash
# Run the chromium player
sh play-stream.sh
```

## Integration with Queuescreen

To replace the ad player with RTM TV2 stream:
```php
// Instead of: php bin/play-ad.php
// Use: php test/play-stream.php 0 0 1920 1080
```

