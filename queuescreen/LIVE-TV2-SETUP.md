# Live TV2 Player Setup Guide

This guide explains how to set up and use the Live TV2 HTML5 player option in the queuescreen system.

## Overview

The queuescreen system now supports two player types:

1. **OMX Player (Traditional)** - Uses omxplayer overlay on top of the webpage with manual positioning
2. **Live TV2 (HTML5)** - Embeds video directly in the webpage using HLS.js, no OMX overlay

## Key Differences

| Feature | OMX Player | Live TV2 |
|---------|------------|----------|
| Technology | omxplayer (hardware accelerated) | HTML5 video + HLS.js |
| Display | Overlays on top of webpage | Embedded in webpage |
| Positioning | Manual (x, y, width, height) | Automatic (responsive) |
| Setup | Simple | Requires URL extraction |
| Ads | Plays local ad files | Can play RTM TV2 live stream |

## Setup Process

### During Initial Setup

When running `php bin/setup.php`, you will be asked to choose a player type:

```
Select the video player type:
  [1] OMX Player (Traditional - overlays on webpage with manual positioning)
  [2] Live TV2 (HTML5 Player - embedded in webpage, no OMX overlay)
```

**If you select Live TV2**, the setup will automatically:
- Install yt-dlp dependency
- Extract and cache the stream URL
- Restart the system in 5 seconds

Your choice will be saved in `config.json` as:
```json
{
    "host": "http://localhost:8000",
    "player_type": "omx"
}
```

or

```json
{
    "host": "http://localhost:8000",
    "player_type": "live-tv2"
}
```

## Live TV2 Setup (For Raspberry Pi)

### Automatic Setup (Recommended)

When you select "Live TV2" during the initial setup process (`php bin/setup.php`), the system will **automatically**:

1. ✅ Install yt-dlp if not already installed
2. ✅ Extract the RTM TV2 stream URL
3. ✅ Cache the URL for the HTML5 player
4. ✅ Restart the system to show the stream

**No manual intervention required!** The system will handle everything and reboot automatically.

### Manual Setup (If Needed)

If you need to manually install dependencies or refresh the stream URL:

#### Step 1: Install yt-dlp

```bash
pip3 install yt-dlp
```

#### Step 2: Extract Stream URL

Run the extraction script:

```bash
cd ~/qmed-utils/queuescreen/bin
sh extract-tv2-url.sh
```

This will:
- Extract the current RTM TV2 stream URL
- Cache it to `www/dev/tv2-stream-url.txt`
- Make it available to the HTML5 player

### Test the Player

Open the test player in Chromium:

```bash
chromium-browser http://localhost/qmed-utils/queuescreen/www/dev/tv2-player.html
```

You should see the RTM TV2 live stream playing.

## Live TV2 Setup (For Windows Development)

### Step 1: Extract Stream URL

Double-click:
```
bin\extract-tv2-url.bat
```

Or run in Command Prompt:
```cmd
cd qmed-utils\queuescreen\bin
extract-tv2-url.bat
```

### Step 2: Test the Player

Open in browser:
```
http://localhost/qmed-util/qmed-utils/queuescreen/www/dev/tv2-player.html
```

## How It Works

### OMX Player Flow

1. Web interface calls `www/dev/omxplayer-loop.php` with positioning parameters
2. Script launches `bin/play-ad.php` with x, y, width, height
3. `play-ad.php` uses omxplayer to play ads with overlay positioning
4. Video appears on top of the webpage

### Live TV2 Flow

1. Web interface calls `www/dev/omxplayer-loop.php` (same endpoint)
2. Script detects `player_type: "live-tv2"` in config
3. Script launches `bin/play-tv2-live.php` in background
4. `play-tv2-live.php` extracts/caches stream URL
5. Web interface embeds `tv2-player.html` as iframe
6. HTML5 player fetches URL from `live-tv2-player.php` API
7. HLS.js plays the stream directly in browser

## Files Created/Modified

### New Files
- `bin/play-tv2-live.php` - Live TV2 player manager
- `bin/extract-tv2-url.sh` - URL extraction script (Linux)
- `bin/extract-tv2-url.bat` - URL extraction script (Windows)
- `www/dev/tv2-player.html` - HTML5 video player
- `www/dev/live-tv2-player.php` - Stream URL API endpoint

### Modified Files
- `sources/src/Commands/SetupScreenCommand.php` - Added player type selection
- `www/dev/omxplayer-loop.php` - Routes to appropriate player
- `config.json.example` - Added player_type field

## Changing Player Type

To change the player type after initial setup:

1. Edit `config.json`:
```json
{
    "player_type": "live-tv2"
}
```

2. Restart the queuescreen:
```bash
sudo reboot
```

Or manually restart the services.

## Troubleshooting

### Live TV2 Player

**Problem**: "Stream URL not available"
- **Solution**: Run `extract-tv2-url.sh` to extract and cache the URL

**Problem**: Stream stops playing after a while
- **Solution**: The player automatically refreshes the URL every hour. If issues persist, check internet connection.

**Problem**: "yt-dlp not found"
- **Solution**: Install yt-dlp: `pip3 install yt-dlp`

**Problem**: Black screen in HTML5 player
- **Solution**: 
  1. Check if stream URL is valid: `cat www/dev/tv2-stream-url.txt`
  2. Test the URL manually: `omxplayer --live "<URL>"`
  3. Re-extract URL: `sh bin/extract-tv2-url.sh`

### OMX Player

**Problem**: Video not positioning correctly
- **Solution**: Adjust x, y, width, height parameters in the web interface

**Problem**: Video overlaps webpage content
- **Solution**: This is expected behavior. Adjust positioning or switch to Live TV2 mode.

## Performance Considerations

### OMX Player
- ✅ Hardware accelerated
- ✅ Low CPU usage
- ✅ Reliable for local files
- ❌ Requires manual positioning
- ❌ Overlaps webpage

### Live TV2 Player
- ✅ No overlay, embedded in webpage
- ✅ Responsive and automatic
- ✅ Easy to integrate
- ⚠️ Higher CPU usage (software decoding)
- ⚠️ Requires internet for live streaming

## URL Refresh

The stream URL may expire. The system handles this automatically:

1. **Manual refresh**: Run `sh bin/extract-tv2-url.sh`
2. **Automatic refresh**: The `play-tv2-live.php` script refreshes every 4 hours
3. **HTML5 player refresh**: Reloads stream every hour

## Integration with Queuescreen

The Live TV2 player can be integrated into your queuescreen display:

### Option 1: Full-screen TV (No Queue)
Embed the player as the main content

### Option 2: Picture-in-Picture
Display queue screen with TV in corner

### Option 3: Scheduled TV
Use cron to switch between queue and TV at specific times

Refer to the main queuescreen documentation for integration examples.

## Support

For issues or questions:
1. Check this documentation
2. Review the test files in `test/` directory
3. Check logs for error messages

## See Also

- `test/MANUAL-EXTRACTION-GUIDE.md` - Detailed extraction process
- `test/test-player.html` - Test player with controls
- `test/QUICK-START.md` - Quick start guide

