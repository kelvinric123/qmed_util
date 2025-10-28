# Queuescreen Player Setup - Quick Reference

## ✅ What's New

The queuescreen setup now includes a **player type selection** during initial configuration.

### During Setup

When running `php bin/setup.php`, you'll see:

```
Select the video player type:
  [1] OMX Player (Traditional - overlays on webpage with manual positioning)
  [2] Live TV2 (HTML5 Player - embedded in webpage, no OMX overlay)
```

## 🎯 Quick Start

### For OMX Player (Default)
1. Select option 1 during setup
2. Done! Use as before

### For Live TV2 Player
1. Select option 2 during setup
2. Install yt-dlp: `pip3 install yt-dlp`
3. Extract stream URL:
   - **Linux/Pi**: `sh bin/extract-tv2-url.sh`
   - **Windows**: `bin\extract-tv2-url.bat`
4. Done! The player will use the HTML5 stream

## 📁 Key Files

| File | Purpose |
|------|---------|
| `config.json` | Stores player type: `"player_type": "omx"` or `"live-tv2"` |
| `bin/play-tv2-live.php` | Manages Live TV2 streaming |
| `www/dev/tv2-player.html` | HTML5 video player interface |
| `www/dev/live-tv2-player.php` | API endpoint for stream URL |
| `bin/extract-tv2-url.sh` | Extract stream URL (Linux) |
| `bin/extract-tv2-url.bat` | Extract stream URL (Windows) |

## 🔄 Switching Player Types

Edit `config.json` and change:
```json
{
    "player_type": "omx"
}
```
to
```json
{
    "player_type": "live-tv2"
}
```

Then restart the system.

## 📖 Full Documentation

See `LIVE-TV2-SETUP.md` for complete details, troubleshooting, and integration guide.

## 🧪 Testing

### Test OMX Player
Access the queuescreen normally - video will overlay on webpage

### Test Live TV2 Player
1. Extract URL first (see above)
2. Open: `http://localhost/.../www/dev/tv2-player.html`
3. Should see RTM TV2 playing in browser

## ⚡ Quick Troubleshooting

**Live TV2 not working?**
1. Check if URL extracted: `cat www/dev/tv2-stream-url.txt`
2. Re-run: `sh bin/extract-tv2-url.sh`
3. Check internet connection
4. Verify yt-dlp installed: `yt-dlp --version`

**OMX Player positioning wrong?**
- Adjust x, y, width, height in the web interface
- This is the expected behavior with overlay mode

## 💡 Notes

- **OMX overlaps** on top of webpage (manual positioning needed)
- **Live TV2 embeds** in webpage (no overlay, responsive)
- Choice is made during setup and stored in `config.json`
- Can be changed anytime by editing config and restarting

