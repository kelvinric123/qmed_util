# Manual Stream URL Extraction Guide

If yt-dlp fails, you can manually extract the stream URL using your browser.

## 🔍 Method 1: Browser Developer Tools (Easiest)

### Step 1: Open the TV2 Live Page
```
https://rtmklik.rtm.gov.my/live/tv2
```

### Step 2: Open Developer Tools
- **Press F12** on keyboard
- Or **Right-click → Inspect Element**

### Step 3: Go to Network Tab
- Click on **Network** tab
- Click on **Filter icon** and type: `m3u8`

### Step 4: Play the Video
- Click play button on the RTM player
- Wait for video to start

### Step 5: Find the m3u8 URL
Look for requests like:
```
chunklist_b2596000_slENG.m3u8
master.m3u8
playlist.m3u8
```

### Step 6: Copy the Full URL
- Right-click on the m3u8 file
- Click **Copy → Copy URL**

Example URL format:
```
https://d25tgymtnqzu8s.cloudfront.net/smil:tv2/chunklist_b2596000_slENG.m3u8?id=2
```

### Step 7: Save the URL
```bash
# On Raspberry Pi
echo "YOUR_COPIED_URL_HERE" > ~/qmed_util/queuescreen/www/dev/tv2-stream-url.txt
```

---

## 🔍 Method 2: Use youtube-dl (Alternative to yt-dlp)

```bash
# Install youtube-dl
pip3 install youtube-dl

# Extract URL
youtube-dl -g "https://rtmklik.rtm.gov.my/live/tv2"
```

---

## 🔍 Method 3: Use FFmpeg Probe

```bash
# Install ffmpeg if not installed
sudo apt install ffmpeg -y

# Try to probe the stream (if you know a base URL)
ffprobe "https://rtmklik.rtm.gov.my/live/tv2"
```

---

## 🔍 Method 4: Check RTM's Player Source Code

```bash
# Download the page
curl -s "https://rtmklik.rtm.gov.my/live/tv2" > rtm_page.html

# Search for m3u8 URLs
grep -oP 'https?://[^"'\'']+\.m3u8[^"'\'']*' rtm_page.html

# Search for CloudFront URLs
grep -oP 'https?://d[0-9a-z]+\.cloudfront\.net[^"'\'']+' rtm_page.html

# Search for any video sources
grep -i "video\|stream\|source" rtm_page.html | grep -oP 'https?://[^"'\'']+' 
```

---

## 🔍 Method 5: Use a Known Working URL

If extraction keeps failing, you can use a known URL format and just update the parameters:

**Common RTM TV2 URL patterns:**
```
https://d25tgymtnqzu8s.cloudfront.net/smil:tv2/chunklist_b2596000_slENG.m3u8?id=2
https://rtm2mobile.secureswiftcontent.com/Origin02/ngrp:RTM2/chunklist_b2596000_slENG.m3u8
```

The URL may change, but the pattern often stays similar.

---

## 💡 Pro Tips

### Check if URL is Working
```bash
# Test with curl
curl -I "YOUR_URL_HERE"

# Test with omxplayer
omxplayer --live "YOUR_URL_HERE"

# Test with ffplay
ffplay "YOUR_URL_HERE"
```

### Find All Possible Streams
```bash
# Some streams have master.m3u8 that lists all quality options
curl "https://d25tgymtnqzu8s.cloudfront.net/smil:tv2/master.m3u8"
```

### Monitor Network Traffic
```bash
# Install tcpdump
sudo apt install tcpdump -y

# Capture m3u8 requests
sudo tcpdump -i any -A | grep m3u8
```

---

## 🆘 Still Not Working?

If all methods fail, possible reasons:

1. **Geo-blocking** - RTM might block non-Malaysia IP addresses
2. **DRM Protection** - Stream might be encrypted
3. **JavaScript Required** - Modern sites embed streams via JS
4. **Captcha/Authentication** - Site might require human verification
5. **Website Down** - RTM server might be temporarily unavailable

### Workarounds:
- Use VPN with Malaysia IP
- Use browser automation (Selenium)
- Contact RTM for API access
- Use official RTM mobile app and inspect traffic

---

## 📝 Save for Later Use

Once you find a working URL, save it:

```bash
# Save the URL
echo "YOUR_WORKING_URL" > ~/qmed_util/queuescreen/www/dev/tv2-stream-url.txt

# Test the player
chromium-browser ~/qmed_util/queuescreen/test-player-simple.html
```

---

**Remember:** Stream URLs usually expire after a few hours, so you'll need to extract a fresh one periodically!

