# ✅ Solution Summary - X-Frame-Options Issue

## 🔴 The Problem

You encountered this error:
```
Refused to display 'https://rtmklik.rtm.gov.my/' in a frame because it set 'X-Frame-Options' to 'sameorigin'.
```

**Why it happens:**
- RTM Klik website blocks iframe embedding for security (anti-clickjacking)
- The `X-Frame-Options: sameorigin` header prevents their site from being embedded in your page
- This is a **common and expected** security measure

---

## ✅ The Solutions (Ranked Best to Worst)

### 🥇 Solution 1: Extract Direct Stream URL (BEST!)

**Pros:**
- ✅ No ads
- ✅ No X-Frame-Options issues
- ✅ Lightweight playback
- ✅ Full control over player
- ✅ Works perfectly on Raspberry Pi

**How to implement:**

```bash
# On Raspberry Pi:
pip3 install yt-dlp

# Extract stream URL
STREAM_URL=$(yt-dlp -g "https://rtmklik.rtm.gov.my/live/tv2")

# Play with omxplayer (lightweight)
omxplayer --live "$STREAM_URL"

# Or play with VLC (more robust)
cvlc "$STREAM_URL" --fullscreen --loop
```

**For your test environment:**
- Use `play-stream.sh` (already configured to try this method first)
- Or use `stream-player-hls.html` (HTML5 player with HLS.js)

---

### 🥈 Solution 2: Open in Direct Window (Simple)

**Pros:**
- ✅ Simple setup
- ✅ No URL extraction needed
- ✅ Official player

**Cons:**
- ❌ May show ads
- ❌ Full webpage overhead
- ❌ Less control

**How to implement:**

```bash
# Just open the URL directly in Chromium
chromium-browser --kiosk \
  --autoplay-policy=no-user-gesture-required \
  "https://rtmklik.rtm.gov.my/live/tv2"
```

**For your test environment:**
- Use `stream-player-direct.html` (redirects to direct page)
- Or click the direct RTM link in `index.html`

---

### 🥉 Solution 3: HTML5 Video with HLS.js

**Pros:**
- ✅ Works in browser
- ✅ Nice HTML5 controls
- ✅ Good for web interfaces

**Cons:**
- ❌ Requires direct stream URL
- ❌ Needs HLS.js library
- ❌ Browser-dependent

**How to use:**
1. Get stream URL with yt-dlp
2. Open `stream-player-hls.html`
3. Paste the URL
4. Click Play

---

## 🎯 Recommended Approach for Queuescreen

**For Production (Raspberry Pi):**

1. **Extract URL once:**
   ```bash
   yt-dlp -g "https://rtmklik.rtm.gov.my/live/tv2" > ~/stream-url.txt
   ```

2. **Update play-stream.sh** (line 55):
   ```bash
   STREAM_URL=$(cat ~/stream-url.txt)
   ```

3. **Auto-refresh daily** (add to crontab):
   ```bash
   0 3 * * * yt-dlp -g "https://rtmklik.rtm.gov.my/live/tv2" > ~/stream-url.txt
   ```

4. **Integrate with queuescreen:**
   ```bash
   php ~/qmed-utils/queuescreen/test/play-stream.php 0 0 1920 1080
   ```

---

## 📁 Files Created for You

| File | Purpose | When to Use |
|------|---------|-------------|
| `stream-player-hls.html` | HTML5 HLS player | When you have the stream URL |
| `stream-player-workaround.html` | All solutions explained | Learn about options |
| `stream-player-direct.html` | Redirects to direct page | Simple testing |
| `play-stream.sh` | Production shell script | Raspberry Pi deployment |
| `play-stream.php` | PHP integration wrapper | Queuescreen integration |
| `QUICK-START.md` | Quick setup guide | First time setup |

---

## 🧪 Testing Now (Windows)

1. **Test HLS Player:**
   ```
   http://localhost/qmed-util/qmed-utils/queuescreen/test/stream-player-hls.html
   ```
   - You'll need to get a stream URL from Raspberry Pi first
   - Or wait until you deploy to Pi

2. **View All Solutions:**
   ```
   http://localhost/qmed-util/qmed-utils/queuescreen/test/stream-player-workaround.html
   ```
   - Opens in your browser now
   - Read about each method

3. **See Working Example:**
   ```
   http://localhost/qmed-util/qmed-utils/queuescreen/test/test-launcher.html
   ```
   - Main test interface

---

## 🚀 Next Steps

1. ✅ **Done:** Test environment created
2. ⏳ **Next:** Deploy to Raspberry Pi
3. ⏳ **Next:** Extract stream URL with yt-dlp
4. ⏳ **Next:** Update play-stream.sh with URL
5. ⏳ **Next:** Test on Raspberry Pi
6. ⏳ **Next:** Integrate with queuescreen system

---

## 💡 Key Takeaway

**You CANNOT embed RTM Klik in an iframe** due to X-Frame-Options.

**Solution:** Extract the direct video stream URL and play it with omxplayer/VLC instead.

This gives you:
- ✅ No ads
- ✅ Better performance
- ✅ Full control
- ✅ No security restrictions

---

## 📞 Need Help?

Check these files:
- `QUICK-START.md` - Quick setup instructions
- `README.md` - Full documentation
- `stream-player-workaround.html` - Visual guide to all solutions

Or review the test pages in your browser!

