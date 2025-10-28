# ⚡ Quick Start - Queuescreen Player Selection

## 🎯 What's New?

You can now choose between **OMX Player** (traditional overlay) or **Live TV2** (HTML5 embedded) during setup!

---

## 🚀 For New Setup

### Step 1: Run Setup
```bash
cd ~/qmed-utils/queuescreen
php bin/setup.php
```

### Step 2: Choose Player
```
Select the video player type:
  [1] OMX Player (Traditional - overlays on webpage with manual positioning)
  [2] Live TV2 (HTML5 Player - embedded in webpage, no OMX overlay)
>
```

**Choose [1] for OMX** (traditional, overlays video)  
**Choose [2] for Live TV2** (HTML5, embedded in page)

### Step 3: Complete Setup
Follow the prompts to complete setup.

---

## 📺 If You Chose Live TV2

### ✨ Automatic Setup!

The system will **automatically**:
1. ✅ Install yt-dlp
2. ✅ Extract the RTM TV2 stream URL
3. ✅ Cache the stream URL
4. ✅ Restart in 5 seconds to show the stream

**No manual steps needed!** Just sit back and let it complete. 🎉

### Manual Setup (Only if needed)

If automatic setup fails, you can manually:

#### 1. Install yt-dlp
```bash
pip3 install yt-dlp
```

#### 2. Extract Stream URL
```bash
cd ~/qmed-utils/queuescreen/bin
sh extract-tv2-url.sh
```

#### 3. Test It
```bash
chromium-browser http://localhost/.../www/dev/tv2-player.html
```

---

## 🔄 Switching Players Later

### Edit config.json
```bash
nano ~/qmed-utils/queuescreen/config.json
```

### Change player_type:
```json
{
    "host": "http://localhost:8000",
    "player_type": "live-tv2"
}
```

Options: `"omx"` or `"live-tv2"`

### Restart
```bash
sudo reboot
```

---

## 📁 Quick Reference

### OMX Player
- ✅ Default option
- ✅ Works immediately after setup
- ✅ Overlays on webpage
- 📌 Requires manual positioning

### Live TV2
- ✅ HTML5 embedded player
- ✅ No overlay, responsive
- ✅ Automatic dependency installation
- ✅ Automatic URL extraction
- 📌 Needs internet connection

---

## 🧪 Testing

### Test OMX
```bash
# Access your queuescreen normally
# Video will overlay on the page
```

### Test Live TV2
```bash
# Open the player directly
chromium-browser http://localhost/.../www/dev/tv2-player.html

# Or check if URL is cached
cat ~/qmed-utils/queuescreen/www/dev/tv2-stream-url.txt
```

---

## 🔧 Troubleshooting

### Live TV2 Not Working?

**1. Check if URL extracted:**
```bash
cat www/dev/tv2-stream-url.txt
```

**2. Re-extract URL:**
```bash
sh bin/extract-tv2-url.sh
```

**3. Check yt-dlp installed:**
```bash
yt-dlp --version
```

**4. Install if missing:**
```bash
pip3 install yt-dlp
```

### OMX Not Working?

**1. Check config:**
```bash
cat config.json
```

Should show: `"player_type": "omx"`

**2. Verify omxplayer installed:**
```bash
which omxplayer
```

---

## 📖 More Information

| Document | Purpose |
|----------|---------|
| `IMPLEMENTATION-COMPLETE.md` | Overview of changes |
| `PLAYER-SETUP-SUMMARY.md` | Quick reference guide |
| `LIVE-TV2-SETUP.md` | Complete Live TV2 setup |
| `INTEGRATION-GUIDE.md` | Integration examples |
| `CHANGELOG.md` | What changed |

---

## ⚡ TL;DR

### For OMX (Traditional)
```bash
php bin/setup.php
> Choose [1] OMX Player
> Done!
```

### For Live TV2 (HTML5)
```bash
php bin/setup.php
> Choose [2] Live TV2
> Wait for automatic installation...
> System restarts automatically
> Done!
```

---

## 💡 Pro Tips

1. **Testing?** Both options are now equally simple with automatic setup!
2. **Production?** Live TV2 for better integration
3. **Local videos?** Use OMX (lower CPU usage)
4. **Live streaming?** Use Live TV2 (designed for it, auto-installs everything)
5. **Switch anytime** by editing config.json

---

## 🎉 Ready to Go!

Choose your player type and enjoy your upgraded queuescreen system! 🚀

For detailed information, see **IMPLEMENTATION-COMPLETE.md**

---

**Happy Screening! 📺**

