# 🚀 RTM TV2 Stream - Deployment Guide

## ✅ Stream URL Found!

```
https://d25tgymtnqzu8s.cloudfront.net/smil:tv2/chunklist_b2596000_slENG.m3u8?id=2
```

**Status:** ✅ Extracted and Saved  
**Date:** October 28, 2025  
**CDN:** CloudFront  
**Bitrate:** 2.5 Mbps  
**Format:** HLS (m3u8)  

---

## 📦 What's Been Done

✅ Stream URL extracted from RTM Klik  
✅ Saved to `stream-url.txt`  
✅ `play-stream.sh` updated with URL  
✅ `test-player.html` ready to test  
✅ All files configured and ready  

---

## 🧪 Testing (Windows - Now!)

### Option 1: Test Player (Easiest)
```
http://localhost/qmed-util/qmed-utils/queuescreen/test/test-player.html
```
- Should auto-load the URL from cache
- Click "Play Stream" if not
- Verify video plays smoothly

### Option 2: VLC Media Player
```
1. Open VLC
2. Media → Open Network Stream
3. Paste: https://d25tgymtnqzu8s.cloudfront.net/smil:tv2/chunklist_b2596000_slENG.m3u8?id=2
4. Click Play
```

### Option 3: PHP Script
```bash
cd c:\laragon\www\qmed-util\qmed-utils\queuescreen\test
php play-stream.php 0 0 1280 720
```

---

## 🚀 Deployment to Raspberry Pi

### Step 1: Copy Files to Pi

```bash
# From Windows, copy test folder to Pi
scp -r c:\laragon\www\qmed-util\qmed-utils\queuescreen\test\ pi@YOUR_PI_IP:~/qmed-utils/queuescreen/

# Or if already on Pi:
cd ~/qmed-utils/queuescreen
# test folder should already be here
```

### Step 2: Verify Files on Pi

```bash
cd ~/qmed-utils/queuescreen/test

# Check if URL file exists
cat stream-url.txt
# Should output: https://d25tgymtnqzu8s.cloudfront.net/smil:tv2/chunklist_b2596000_slENG.m3u8?id=2

# Check play-stream.sh
grep "STREAM_URL=" play-stream.sh
# Should show the URL
```

### Step 3: Test on Pi

```bash
# Make scripts executable
chmod +x play-stream.sh

# Test with PHP wrapper
php play-stream.php 0 0 1920 1080

# Or test directly with omxplayer
omxplayer --live "https://d25tgymtnqzu8s.cloudfront.net/smil:tv2/chunklist_b2596000_slENG.m3u8?id=2"
```

**Expected Result:** RTM TV2 should start playing full-screen, no ads!

---

## 🔗 Integration with Queuescreen

### Option A: Replace Ad Player Completely

Edit `bin/play-ad.php` to use TV stream instead:

```php
<?php
// bin/play-ad.php - MODIFIED to play TV stream

require_once __DIR__ . '/../sources/vendor/autoload.php';

$x = $argv[1] ?? 0;
$y = $argv[2] ?? 0;
$width = $argv[3] ?? 1920;
$height = $argv[4] ?? 1080;

// Use TV stream instead of ads
shell_exec("php " . __DIR__ . "/../test/play-stream.php $x $y $width $height &");
```

### Option B: Scheduled TV Streaming

Edit `bin/cron.php` to add TV schedule:

```php
// Add this job to bin/cron.php

$jobby->add('TVStream', [
    'closure' => function() use ($app) {
        $currentHour = (int)date('H');
        
        // Play TV during lunch (12-2pm) and evening (6-8pm)
        if (($currentHour >= 12 && $currentHour < 14) || 
            ($currentHour >= 18 && $currentHour < 20)) {
            
            // Kill ads
            $app->killAds();
            
            // Start TV stream
            $basePath = $app->getBasePath();
            shell_exec("php {$basePath}/test/play-stream.php 0 0 1920 1080 &");
            
            \Rasque\Logger::instance()->log('tv_stream_started');
        } else {
            // Resume ads during other times
            // (add logic to restart ads if needed)
        }
    },
    'schedule' => '0 * * * *' // Check every hour
]);
```

### Option C: TV Only (No Ads)

Edit `stubs/screen.sh.stub`:

```bash
#!/bin/bash

# Original: Queue screen
# HOST="$(php BIN_PATH/settings.php host)"
# DEVICE_ID="$(sh BIN_PATH/device-id.sh)"
# /usr/bin/chromium-browser --kiosk $HOST/queuescreen/screens/$DEVICE_ID

# New: TV stream only
php BIN_PATH/../test/play-stream.php 0 0 1920 1080
```

---

## 🔄 URL Maintenance

### Monitor Stream Health

Create `bin/check-stream.php`:

```php
<?php
// bin/check-stream.php - Monitor stream health

require_once __DIR__ . '/../sources/vendor/autoload.php';

$streamUrl = file_get_contents(__DIR__ . '/../test/stream-url.txt');
$streamUrl = trim($streamUrl);

// Simple health check
$headers = @get_headers($streamUrl);

if ($headers && strpos($headers[0], '200') !== false) {
    echo "✓ Stream is accessible\n";
    exit(0);
} else {
    echo "✗ Stream is down or inaccessible\n";
    \Rasque\Logger::instance()->log('stream_health_fail', ['url' => $streamUrl]);
    exit(1);
}
```

Add to crontab:
```bash
# Check stream health every 30 minutes
*/30 * * * * php ~/qmed-utils/queuescreen/bin/check-stream.php
```

### Auto-Restart on Failure

Add to `bin/cron.php`:

```php
$jobby->add('StreamMonitor', [
    'closure' => function() use ($app) {
        $streamUrl = trim(file_get_contents($app->getBasePath() . '/test/stream-url.txt'));
        
        // Check if stream is accessible
        $headers = @get_headers($streamUrl);
        
        if (!$headers || strpos($headers[0], '200') === false) {
            // Stream failed, restart
            $app->killProcess('play-stream.php');
            $app->killProcess('omxplayer');
            
            sleep(2);
            
            shell_exec('php ' . $app->getBasePath() . '/test/play-stream.php 0 0 1920 1080 &');
            
            \Rasque\Logger::instance()->log('stream_auto_restart');
        }
    },
    'schedule' => '*/15 * * * *' // Every 15 minutes
]);
```

---

## 📊 Stream Details

| Property | Value |
|----------|-------|
| **Provider** | RTM (Radio Televisyen Malaysia) |
| **CDN** | CloudFront (AWS) |
| **Format** | HLS (HTTP Live Streaming) |
| **Container** | MPEG-TS |
| **Bitrate** | ~2.5 Mbps |
| **Language** | English (slENG) |
| **Stability** | Good (CloudFront CDN) |

---

## ⚠️ Important Notes

### URL Expiration
- **CloudFront URLs are typically stable** but may include temporary tokens
- If stream stops working, re-extract URL using the extraction guide
- Consider implementing auto-refresh (see above)

### Network Requirements
- **Bandwidth:** 3+ Mbps recommended (for 2.5 Mbps stream + overhead)
- **Stability:** Stable connection required for smooth playback
- **Latency:** Not critical for live TV (5-30 seconds delay is normal)

### Legal Considerations
- **Personal Use:** Generally acceptable
- **Commercial/Public Display:** May require licensing
- **Contact RTM** for official commercial streaming rights if needed

---

## 🧪 Verification Checklist

Before deploying to production:

- [ ] Stream plays smoothly on Windows (VLC or test-player.html)
- [ ] Stream tested on Raspberry Pi with omxplayer
- [ ] `play-stream.sh` script works correctly
- [ ] `play-stream.php` wrapper tested
- [ ] Integration method chosen (replace ads / scheduled / TV only)
- [ ] Monitoring setup (optional but recommended)
- [ ] Auto-restart configured (optional but recommended)
- [ ] Network bandwidth verified (3+ Mbps available)

---

## 🎯 Quick Deploy Commands

```bash
# On Raspberry Pi:

# 1. Navigate to queuescreen
cd ~/qmed-utils/queuescreen/test

# 2. Make executable
chmod +x play-stream.sh

# 3. Test the stream
php play-stream.php 0 0 1920 1080

# 4. If working, integrate with queuescreen
# (Choose integration method from above)

# 5. Restart queuescreen system
sudo reboot
```

---

## 🆘 Troubleshooting

### Stream Won't Play

**Issue:** Black screen or error message

**Solutions:**
```bash
# 1. Verify URL is accessible
curl -I "https://d25tgymtnqzu8s.cloudfront.net/smil:tv2/chunklist_b2596000_slENG.m3u8?id=2"
# Should return: HTTP/2 200

# 2. Test with omxplayer directly
omxplayer --live "URL_HERE"

# 3. Check internet connection
ping -c 4 8.8.8.8

# 4. Try different player
cvlc "URL_HERE" --fullscreen
```

### Buffering Issues

**Issue:** Stream keeps buffering

**Solutions:**
- Check network bandwidth: `speedtest-cli`
- Lower quality if available (try different chunklist URLs)
- Increase buffer size in omxplayer
- Check for network congestion

### URL Expired

**Issue:** Stream was working, now returns 403 or 404

**Solution:**
- Re-extract URL using extraction guide
- Check if RTM changed their streaming setup
- Contact RTM for stable stream access

---

## 📞 Support

- **Extraction Guide:** `extract-guide.html`
- **Manual Guide:** `MANUAL-EXTRACTION-GUIDE.md`
- **Implementation:** `IMPLEMENTATION-COMPLETE.md`
- **All Solutions:** `SOLUTION-SUMMARY.md`

---

## ✅ Success Criteria

Your deployment is successful when:
- ✅ Stream plays without ads
- ✅ Video quality is good (no excessive buffering)
- ✅ Audio and video in sync
- ✅ System stable for 24+ hours
- ✅ Auto-recovery works (if implemented)

---

**Ready to deploy? Test first, then follow the integration steps above!** 🚀

**Last Updated:** October 28, 2025  
**Stream URL:** `d25tgymtnqzu8s.cloudfront.net` (CloudFront CDN)  
**Status:** ✅ Ready for Production

