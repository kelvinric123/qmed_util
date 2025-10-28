# Quick Start Guide - RTM Stream Integration

## ⚠️ X-Frame-Options Issue

RTM Klik website sets `X-Frame-Options: sameorigin` which **prevents iframe embedding**.

**Solution:** Extract the direct HLS stream URL instead of embedding the webpage.

---

## 🚀 Quick Setup for Raspberry Pi

### Step 1: Install yt-dlp
```bash
pip3 install yt-dlp
```

### Step 2: Extract Stream URL
```bash
# Get the direct m3u8 stream URL
yt-dlp -g "https://rtmklik.rtm.gov.my/live/tv2"

# Example output:
# https://rtm-live.glueapi.io/smil:rtm2/playlist.m3u8
```

### Step 3: Update play-stream.sh
Edit `play-stream.sh` and uncomment/update line ~55:
```bash
STREAM_URL="paste_your_m3u8_url_here"
```

### Step 4: Test the Stream
```bash
cd ~/qmed-utils/queuescreen/test
php play-stream.php 0 0 1920 1080
```

---

## 💡 Alternative Methods

### Method 1: Direct Stream URL (Best - No Ads!)
- Uses omxplayer or VLC
- Lightweight and fast
- No webpage overhead
- **Recommended for production**

```bash
omxplayer --live "YOUR_M3U8_URL"
```

### Method 2: Chromium Browser (Simple but has ads)
- Opens full webpage
- May show ads
- Easier setup but less control

```bash
chromium-browser --kiosk "https://rtmklik.rtm.gov.my/live/tv2"
```

### Method 3: HTML5 HLS Player (Good for web interface)
- Use `stream-player-hls.html`
- Works in browser with HLS.js
- Good for testing

---

## 🔧 Files Explained

| File | Purpose |
|------|---------|
| `stream-player-hls.html` | HTML5 video player with HLS.js |
| `stream-player-workaround.html` | Documentation of all workarounds |
| `play-stream.sh` | Shell script for Raspberry Pi |
| `play-stream.php` | PHP wrapper for integration |
| `index.html` | Main test interface |

---

## 🎯 Integration with Queuescreen

### Replace Ad Player with TV Stream

Edit `bin/cron.php` to add scheduled TV streaming:

```php
$jobby->add('TVStream', [
    'closure' => function() use ($app) {
        $currentHour = (int)date('H');
        
        // Play TV during specific hours (e.g., lunch time)
        if ($currentHour >= 12 && $currentHour < 14) {
            // Kill ads
            $app->killAds();
            
            // Start TV stream
            shell_exec('php /home/pi/qmed-utils/queuescreen/test/play-stream.php 0 0 1920 1080 &');
        }
    },
    'schedule' => '0 12 * * *' // Daily at noon
]);
```

### Or Replace Ads Completely

Modify `stubs/screen.sh.stub` to launch TV instead of queue screen:

```bash
# Original queue screen:
# /usr/bin/chromium-browser --kiosk $HOST/queuescreen/screens/$DEVICE_ID

# New: TV stream
php /home/pi/qmed-utils/queuescreen/test/play-stream.php 0 0 1920 1080
```

---

## 🔄 Auto-Update Stream URL

Stream URLs may expire. Create a cron job to refresh:

```bash
# Add to crontab (update daily at 3 AM)
0 3 * * * yt-dlp -g "https://rtmklik.rtm.gov.my/live/tv2" > /home/pi/qmed-utils/queuescreen/test/stream-url.txt
```

Then modify `play-stream.sh` to read from this file:
```bash
STREAM_URL=$(cat /home/pi/qmed-utils/queuescreen/test/stream-url.txt)
```

---

## 📝 Testing on Windows

1. Open `http://localhost/qmed-util/qmed-utils/queuescreen/test/test-launcher.html`
2. Click "HLS Player"
3. Get stream URL from Raspberry Pi and paste it
4. Or just test with the workaround methods

---

## ❓ Troubleshooting

**Problem:** yt-dlp can't extract URL
```bash
# Update yt-dlp
pip3 install --upgrade yt-dlp

# Try with verbose output
yt-dlp -v -g "https://rtmklik.rtm.gov.my/live/tv2"
```

**Problem:** Stream won't play
- Check internet connection
- Try VLC instead of omxplayer: `cvlc URL --fullscreen`
- Update stream URL (may have expired)

**Problem:** Black screen
- Stream URL may have changed
- Re-extract URL with yt-dlp
- Check if RTM service is online

---

## 📞 Support

For issues specific to:
- **Queuescreen system**: Contact QueueMed support
- **RTM streaming**: Visit https://rtmklik.rtm.gov.my/
- **This integration**: Check the test files and documentation

