# Queuescreen Player Type Selection - Implementation Summary

## 🎯 Overview

The queuescreen setup process has been enhanced to include a **player type selection option**. Users can now choose between:

1. **OMX Player** (Traditional) - Video overlays on top of webpage
2. **Live TV2** (HTML5) - Video embedded directly in webpage

## ✅ Implementation Complete

### What Was Done

1. **Added player type selection during setup**
   - Modified `SetupScreenCommand.php` to prompt user for player type
   - Choice is saved in `config.json` as `"player_type": "omx"` or `"live-tv2"`
   - Default is OMX for backward compatibility

2. **Created Live TV2 player system**
   - `bin/play-tv2-live.php` - Manages Live TV2 streaming without OMX
   - `www/dev/tv2-player.html` - HTML5 video player with HLS.js
   - `www/dev/live-tv2-player.php` - API endpoint for stream URL
   - Auto-refresh mechanism for stream URLs

3. **Modified existing files**
   - `www/dev/omxplayer-loop.php` - Now routes to appropriate player based on config
   - `config.json.example` - Added `player_type` field

4. **Created helper scripts**
   - `bin/extract-tv2-url.sh` - Extract stream URL (Linux/Raspberry Pi)
   - `bin/extract-tv2-url.bat` - Extract stream URL (Windows)

5. **Documentation**
   - `LIVE-TV2-SETUP.md` - Complete setup and troubleshooting guide
   - `PLAYER-SETUP-SUMMARY.md` - Quick reference guide

## 📋 Key Differences

### OMX Player (Traditional)
```
Web Page (Chromium)
    ↓
calls omxplayer-loop.php
    ↓
launches play-ad.php
    ↓
omxplayer OVERLAYS video on top of page
```

**Characteristics:**
- ✅ Hardware accelerated
- ✅ Low CPU usage
- ❌ Overlaps webpage (manual positioning needed)
- ❌ Requires x, y, width, height parameters

### Live TV2 (HTML5)
```
Web Page (Chromium)
    ↓
calls omxplayer-loop.php
    ↓
launches play-tv2-live.php (background)
    ↓
extracts & caches stream URL
    ↓
HTML5 player (HLS.js) EMBEDS in webpage
```

**Characteristics:**
- ✅ Embedded in webpage (no overlay)
- ✅ Responsive and automatic
- ✅ No manual positioning needed
- ⚠️ Higher CPU usage (software decoding)
- ⚠️ Requires internet connection

## 🚀 Usage

### During Initial Setup

When running `php bin/setup.php`:

```
Which clinic are you setting this Raspberry up for?
> [clinic name]

Select the video player type:
  [1] OMX Player (Traditional - overlays on webpage with manual positioning)
  [2] Live TV2 (HTML5 Player - embedded in webpage, no OMX overlay)
> 1 or 2

Set-up this raspberry for [Clinic Name](y/n)?
> y
```

### For Live TV2 Setup (Additional Steps)

**On Raspberry Pi:**
```bash
# Install yt-dlp
pip3 install yt-dlp

# Extract stream URL
cd ~/qmed-utils/queuescreen/bin
sh extract-tv2-url.sh

# Test
chromium-browser http://localhost/.../www/dev/tv2-player.html
```

**On Windows (Development):**
```cmd
REM Extract stream URL
cd qmed-utils\queuescreen\bin
extract-tv2-url.bat

REM Test
start http://localhost/qmed-util/qmed-utils/queuescreen/www/dev/tv2-player.html
```

### Changing Player Type Later

Edit `config.json`:
```json
{
    "host": "http://localhost:8000",
    "player_type": "live-tv2"
}
```

Then restart the system.

## 📁 Files Created/Modified

### New Files (9 files)
```
qmed-utils/queuescreen/
├── bin/
│   ├── play-tv2-live.php          ⭐ Live TV2 player manager
│   ├── extract-tv2-url.sh         ⭐ URL extraction (Linux)
│   └── extract-tv2-url.bat        ⭐ URL extraction (Windows)
├── www/dev/
│   ├── tv2-player.html            ⭐ HTML5 video player
│   ├── live-tv2-player.php        ⭐ Stream URL API
│   └── tv2-stream-url.txt         📝 Cached stream URL (generated)
├── LIVE-TV2-SETUP.md              📖 Complete guide
└── PLAYER-SETUP-SUMMARY.md        📖 Quick reference
```

### Modified Files (3 files)
```
qmed-utils/queuescreen/
├── sources/src/Commands/
│   └── SetupScreenCommand.php     ✏️ Added player selection
├── www/dev/
│   └── omxplayer-loop.php         ✏️ Routes to appropriate player
└── config.json.example            ✏️ Added player_type field
```

## 🔄 How It Works

### Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                      SETUP PROCESS                          │
│                                                             │
│  php bin/setup.php                                         │
│         ↓                                                  │
│  [Select Clinic]                                           │
│         ↓                                                  │
│  ┌──────────────────────────────────────┐                 │
│  │ Select Player Type:                  │                 │
│  │  [1] OMX Player                      │                 │
│  │  [2] Live TV2                        │                 │
│  └──────────────────────────────────────┘                 │
│         ↓                                                  │
│  Save to config.json                                       │
│  {                                                         │
│    "player_type": "omx" or "live-tv2"                     │
│  }                                                         │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                    RUNTIME BEHAVIOR                         │
│                                                             │
│  Web Interface calls:                                       │
│  www/dev/omxplayer-loop.php                                │
│         ↓                                                  │
│  Check config.json                                         │
│         ↓                                                  │
│  ┌────────────────┐              ┌────────────────┐       │
│  │ player_type =  │              │ player_type =  │       │
│  │     "omx"      │              │  "live-tv2"    │       │
│  └────────────────┘              └────────────────┘       │
│         ↓                                  ↓              │
│  Launch play-ad.php              Launch play-tv2-live.php │
│         ↓                                  ↓              │
│  omxplayer overlays              HTML5 player embeds      │
│  video on webpage                video in webpage         │
└─────────────────────────────────────────────────────────────┘
```

## 🧪 Testing

### Test OMX Player
1. Set `"player_type": "omx"` in config.json
2. Access queuescreen normally
3. Video should overlay on webpage with configured positioning

### Test Live TV2 Player
1. Run `extract-tv2-url.sh` or `extract-tv2-url.bat`
2. Set `"player_type": "live-tv2"` in config.json
3. Access queuescreen
4. Or directly test: `http://localhost/.../www/dev/tv2-player.html`
5. Video should play embedded in page

## 📝 Notes

### OMX Player
- **No changes** to existing behavior
- Still uses manual positioning (x, y, width, height)
- Still overlaps on top of webpage
- Works exactly as before

### Live TV2 Player
- **No OMX overlay** - video is embedded in webpage
- **No manual positioning** - uses responsive HTML5 video
- **Requires URL extraction** - run extract script first
- **Auto-refresh** - URL refreshes every 4 hours automatically

## 🔧 Troubleshooting

### OMX Player Issues
**Problem:** Video positioning wrong
- **Solution:** Adjust x, y, width, height parameters in web interface
- This is expected - OMX overlays require manual positioning

### Live TV2 Issues
**Problem:** "Stream URL not available"
- **Solution:** Run `extract-tv2-url.sh` or `extract-tv2-url.bat`

**Problem:** yt-dlp not found
- **Solution:** Install it: `pip3 install yt-dlp`

**Problem:** Stream stops after a while
- **Solution:** System auto-refreshes every 4 hours. Check internet connection.

**Problem:** Black screen
- **Solution:** 
  1. Verify URL: `cat www/dev/tv2-stream-url.txt`
  2. Test URL: `omxplayer --live "<URL>"`
  3. Re-extract: `sh bin/extract-tv2-url.sh`

## 📦 Deployment

### For Development (Windows)
1. Code is in `qmed-utils/queuescreen/`
2. Test using local server (laragon, etc.)
3. Use `extract-tv2-url.bat` for URL extraction

### For Production (Raspberry Pi)
1. Deploy to Raspberry Pi
2. Run setup: `php bin/setup.php`
3. Choose player type during setup
4. For Live TV2: Install yt-dlp and extract URL
5. System will use selected player automatically

## ✨ Benefits

### For Users
- **Choice** between hardware-accelerated overlay and embedded HTML5
- **Flexibility** to switch between modes
- **Better integration** with webpage (Live TV2 mode)
- **Backward compatible** with existing OMX setups

### For Developers
- **Clean separation** of player types
- **Easy to extend** with new player types
- **Centralized routing** in omxplayer-loop.php
- **Well documented** implementation

## 📚 Documentation

- **LIVE-TV2-SETUP.md** - Complete setup guide, troubleshooting, integration
- **PLAYER-SETUP-SUMMARY.md** - Quick reference for setup and switching
- **This file** - Implementation overview and technical details

## 🎉 Summary

The queuescreen system now supports flexible player type selection:

✅ **Setup** - Choose during initial setup  
✅ **Configuration** - Stored in config.json  
✅ **Routing** - Automatic based on config  
✅ **OMX Mode** - Traditional overlay (unchanged)  
✅ **Live TV2 Mode** - HTML5 embedded player (new)  
✅ **Documentation** - Complete guides provided  
✅ **Backward Compatible** - Existing setups work as before  

The implementation is complete and ready for use! 🚀

