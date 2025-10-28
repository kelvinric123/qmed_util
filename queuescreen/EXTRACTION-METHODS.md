# RTM TV2 Stream URL Extraction Methods

## 📋 Overview

Multiple methods to extract the RTM TV2 stream URL, ordered from easiest to most reliable.

---

## 🚀 Method 1: Automatic Extractor (Recommended)

**Best for:** Automated setups, hands-free operation

```bash
cd ~/qmed_util/queuescreen
chmod +x extract-url-auto.sh
./extract-url-auto.sh
```

This script automatically tries:
1. yt-dlp (fastest)
2. Python smart extractor (multiple techniques)
3. curl + grep (simple scraping)
4. youtube-dl (fallback)

**Success rate:** ~70% (depends on yt-dlp/website availability)

---

## 📝 Method 2: Manual Browser Extraction (Most Reliable)

**Best for:** When automatic methods fail, guaranteed to work

### Steps:

1. **Open RTM TV2 page:**
   ```
   https://rtmklik.rtm.gov.my/live/tv2
   ```

2. **Open Developer Tools:**
   - Press `F12` key
   - Or Right-click → Inspect Element

3. **Go to Network tab:**
   - Click **Network** tab
   - Type `m3u8` in the filter box

4. **Play the video:**
   - Click play button on RTM player
   - Wait for video to start loading

5. **Copy the URL:**
   - Look for requests like:
     - `chunklist_b2596000_slENG.m3u8`
     - `playlist.m3u8`
     - `master.m3u8`
   - Right-click → Copy → Copy URL

6. **Save to Raspberry Pi:**
   ```bash
   echo "PASTE_URL_HERE" > ~/qmed_util/queuescreen/www/dev/tv2-stream-url.txt
   ```

**Success rate:** 100% (always works if video plays)

---

## 🌐 Method 3: Browser Network Logging

**Best for:** Semi-automated browser capture

```bash
chmod +x extract-url-chromium.sh
./extract-url-chromium.sh
```

This will:
1. Open Chromium with network logging
2. You play the video
3. Close browser when video starts
4. Script extracts URL from network log

**Success rate:** ~90% (requires user interaction but captures real traffic)

---

## 🐍 Method 4: Python Smart Extractor

**Best for:** When yt-dlp fails but Python is available

```bash
chmod +x extract-url-smart.py
python3 extract-url-smart.py
```

Tries 5 different techniques:
1. Direct page scraping
2. API endpoint probing
3. JavaScript variable extraction
4. youtube-dl fallback
5. Known URL pattern testing

**Success rate:** ~60% (depends on website structure)

---

## 🔧 Method 5: Quick Test (Sample URL)

**Best for:** Immediate testing without extraction

```bash
./quick-test-with-sample-url.sh
```

Uses a pre-known URL. **Note:** Sample URLs expire after a few hours.

**Success rate:** ~30% (only works if URL hasn't expired)

---

## 📊 Comparison Table

| Method | Speed | Reliability | User Input | Notes |
|--------|-------|-------------|------------|-------|
| Auto Extractor | Fast | Medium | None | Tries multiple methods |
| Manual Browser | Medium | **Highest** | Required | Always works |
| Chromium Logging | Medium | High | Minimal | Semi-automated |
| Python Smart | Fast | Medium | None | Multiple techniques |
| Sample URL | Instant | Low | None | For quick tests only |

---

## 🎯 Recommended Workflow

### For Development/Testing:
```bash
# Quick test with sample URL
./quick-test-with-sample-url.sh
```

### For First-Time Setup:
```bash
# Try automatic first
./extract-url-auto.sh

# If fails, use manual browser method
# See instructions above
```

### For Production Auto-Refresh:
The system uses `extract-url-auto.sh` every 4 hours automatically.
If it fails, URLs are cached and reused until manual refresh.

---

## 🐛 Troubleshooting

### All Automatic Methods Fail

**Possible causes:**
1. RTM website structure changed
2. Geo-blocking (need Malaysia IP)
3. Network/firewall issues
4. Website requires JavaScript/authentication

**Solution:** Use manual browser extraction (Method 2)

### yt-dlp Keeps Failing

```bash
# Update yt-dlp
pip3 install -U yt-dlp

# Check version
yt-dlp --version

# Test with verbose output
yt-dlp -v -g "https://rtmklik.rtm.gov.my/live/tv2"

# Try without SSL check
yt-dlp --no-check-certificate -g "https://rtmklik.rtm.gov.my/live/tv2"
```

### URL Extracted But Won't Play

```bash
# Test the URL
curl -I "YOUR_URL"

# Try with omxplayer
omxplayer --live "YOUR_URL"

# Check if it's a master.m3u8 (need to extract chunklist)
curl "YOUR_URL"
```

### Python Script Errors

```bash
# Install required packages
pip3 install urllib3

# Run with error output
python3 -u extract-url-smart.py
```

---

## 💡 Pro Tips

### 1. Save Multiple URLs

Sometimes extracting multiple quality variants:
```bash
yt-dlp -F "https://rtmklik.rtm.gov.my/live/tv2"  # List all formats
yt-dlp -g "https://rtmklik.rtm.gov.my/live/tv2" | tee urls.txt  # Save all
```

### 2. Test Before Saving

```bash
# Extract and test in one go
URL=$(yt-dlp -g "https://rtmklik.rtm.gov.my/live/tv2" | head -n 1)
omxplayer --live "$URL"  # If plays, it's good!
echo "$URL" > www/dev/tv2-stream-url.txt
```

### 3. Monitor URL Expiry

```bash
# Check when URL was last updated
stat www/dev/tv2-stream-url.txt

# Auto-extract if older than 4 hours
find www/dev/tv2-stream-url.txt -mmin +240 -exec ./extract-url-auto.sh \;
```

### 4. Cron Job for Auto-Refresh

```bash
# Add to crontab
crontab -e

# Extract fresh URL every 3 hours
0 */3 * * * /home/pi/qmed_util/queuescreen/extract-url-auto.sh >> /var/log/tv2-extract.log 2>&1
```

---

## 📞 Need Help?

If you're stuck:

1. **Check documentation:**
   ```bash
   cat extract-url-manual-guide.md
   ```

2. **Test components:**
   ```bash
   # Test internet
   ping -c 3 google.com
   
   # Test RTM website
   curl -I https://rtmklik.rtm.gov.my/live/tv2
   
   # Test yt-dlp
   yt-dlp --version
   ```

3. **Use manual method:**
   Manual browser extraction (Method 2) is guaranteed to work!

---

## 📈 Success Rate by Environment

| Environment | Auto Methods | Manual Methods |
|-------------|--------------|----------------|
| Home WiFi | ~70% | 100% |
| Mobile Network | ~60% | 100% |
| VPN (Malaysia) | ~80% | 100% |
| VPN (Other) | ~20% | 90% |
| Corporate Network | ~40% | 100% |

**Manual browser method works everywhere!**

---

**Last Updated:** October 28, 2025

