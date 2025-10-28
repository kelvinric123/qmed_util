# Manual Stream URL Extraction Guide

## ⚠️ yt-dlp Doesn't Support RTM Klik

Unfortunately, yt-dlp doesn't have a built-in extractor for RTM Klik Malaysia. 

**Solution:** Extract the URL manually using browser developer tools.

---

## 🔍 Method 1: Browser DevTools (Easy & Reliable)

### Step-by-Step Instructions:

1. **Open RTM TV2 in Browser:**
   ```
   https://rtmklik.rtm.gov.my/live/tv2
   ```

2. **Open Developer Tools:**
   - **Windows/Linux**: Press `F12` or `Ctrl+Shift+I`
   - **Mac**: Press `Cmd+Option+I`

3. **Go to Network Tab:**
   - Click on the "Network" tab in DevTools
   - Clear existing requests (click the 🚫 icon)

4. **Filter for Media Files:**
   - In the filter box, type: `m3u8` or `playlist`
   - This will show only HLS stream files

5. **Reload the Page:**
   - Press `F5` or `Ctrl+R` to reload
   - Wait for the video to start playing

6. **Find the Stream URL:**
   - Look for requests ending in `.m3u8` or containing `playlist`
   - Common patterns:
     ```
     master.m3u8
     playlist.m3u8
     index.m3u8
     chunklist.m3u8
     ```

7. **Copy the URL:**
   - Right-click on the m3u8 request
   - Select "Copy" → "Copy URL" or "Copy as cURL"
   - The URL will look something like:
     ```
     https://rtm-live.glueapi.io/smil:rtm2/playlist.m3u8
     https://stream.rtm.gov.my/live/tv2/playlist.m3u8
     ```

8. **Test the URL:**
   - Paste it in VLC: Media → Open Network Stream
   - Or paste in our test player: `test-player.html`

---

## 🎥 Method 2: Video Element Inspection

### Alternative Method:

1. **Open RTM page** and wait for video to load

2. **Right-click on the video** → "Inspect Element"

3. **In the HTML**, look for:
   ```html
   <video src="...">
   <source src="...">
   ```

4. **Or search in Console**:
   ```javascript
   // In browser console, type:
   document.querySelector('video').src
   document.querySelector('video').currentSrc
   
   // Or find all video sources:
   Array.from(document.querySelectorAll('source')).map(s => s.src)
   ```

---

## 📱 Method 3: Network Monitoring Tools

### Using Advanced Tools:

1. **Charles Proxy** (Mac/Windows)
2. **Fiddler** (Windows)
3. **Wireshark** (Advanced)

These tools can capture all network traffic and filter for m3u8 URLs.

---

## ✅ Example URLs Format

RTM streams typically use these formats:

```
Master Playlist (Multi-quality):
https://domain.com/path/master.m3u8

Single Quality:
https://domain.com/path/playlist.m3u8

With CDN:
https://cdn.rtm.gov.my/live/tv2_1080p/playlist.m3u8
```

---

## 🧪 Testing the Extracted URL

### Option 1: VLC Media Player
```bash
# Windows
vlc "PASTE_URL_HERE"

# Or: File → Open Network Stream → Paste URL
```

### Option 2: Our Test Player
```
1. Open: http://localhost/qmed-util/qmed-utils/queuescreen/test/test-player.html
2. Paste the URL
3. Click "Play Stream"
```

### Option 3: Command Line (Raspberry Pi)
```bash
# With omxplayer
omxplayer --live "PASTE_URL_HERE"

# With ffplay
ffplay "PASTE_URL_HERE"
```

---

## 💾 Save for Later Use

Once you find the URL, save it:

### Create cache file:
```bash
# Windows
echo "YOUR_URL_HERE" > c:\laragon\www\qmed-util\qmed-utils\queuescreen\test\stream-url.txt

# Linux/Mac/Pi
echo "YOUR_URL_HERE" > ~/qmed-utils/queuescreen/test/stream-url.txt
```

### Then use "Load from Cache" in test-player.html

---

## 🔄 URL May Change

**Important:** Stream URLs may:
- Expire after some time
- Change when RTM updates their system
- Include authentication tokens

**Solution:** 
- Save the extraction steps
- Re-extract when needed
- Consider writing a custom scraper for RTM specifically

---

## 📝 For Raspberry Pi Production

Once you have the URL:

1. **Edit play-stream.sh** (line ~55):
   ```bash
   STREAM_URL="your_extracted_url_here"
   ```

2. **Test it:**
   ```bash
   php ~/qmed-utils/queuescreen/test/play-stream.php 0 0 1920 1080
   ```

3. **Set up auto-refresh:**
   - Create a script to re-extract daily
   - Or monitor if stream fails and re-extract automatically

---

## 🎯 Quick Reference

| Step | Action |
|------|--------|
| 1 | Open RTM page |
| 2 | Press F12 |
| 3 | Network tab |
| 4 | Filter: m3u8 |
| 5 | Reload page |
| 6 | Copy URL from .m3u8 request |
| 7 | Test in VLC or test-player.html |
| 8 | Save to stream-url.txt |

---

## 💡 Pro Tip

If you need to do this regularly, consider:

1. **Create a browser bookmark** with these instructions
2. **Take screenshots** of the DevTools steps
3. **Document the exact URL pattern** for your region
4. **Write a simple web scraper** specifically for RTM (legal gray area!)

---

## 🆘 Still Can't Find It?

Try these alternatives:

1. **Use the full RTM page** (with ads):
   ```bash
   chromium-browser --kiosk https://rtmklik.rtm.gov.my/live/tv2
   ```

2. **Try other Malaysian TV options**:
   - YouTube live streams (many official channels)
   - TV3 via https://tv3live.me/ (third-party)
   - Astro channels (if you have subscription)

3. **Contact RTM** for official stream access for commercial use

