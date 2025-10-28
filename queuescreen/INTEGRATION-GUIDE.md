# Queuescreen Live TV2 Integration Guide

## 🎬 Overview

This guide explains how to integrate the Live TV2 player into your queuescreen display.

## 🔧 Integration Options

### Option 1: Full-Screen Live TV (No Queue Display)

Replace the queue display entirely with Live TV2.

**Use Case:** Waiting areas where TV entertainment is primary

**Setup:**
1. Choose "Live TV2" during setup
2. Extract stream URL: `sh bin/extract-tv2-url.sh`
3. The queuescreen will display Live TV2 in the browser

**Result:** Full-screen RTM TV2 stream embedded in the page

---

### Option 2: Side-by-Side (Queue + TV)

Display queue information alongside Live TV2.

**Use Case:** Large displays where both queue and TV can fit

**Implementation:**
```html
<!-- In your queue display page -->
<div style="display: flex;">
    <!-- Queue section -->
    <div style="flex: 1;">
        <!-- Your queue content here -->
    </div>
    
    <!-- Live TV section -->
    <div style="flex: 1;">
        <iframe src="/www/dev/tv2-player.html" 
                style="width: 100%; height: 600px; border: none;">
        </iframe>
    </div>
</div>
```

**Configuration:**
- Set `"player_type": "live-tv2"` in config.json
- Extract stream URL
- Embed the player in your page layout

---

### Option 3: Picture-in-Picture (Queue with TV Corner)

Show queue with TV in a small corner overlay.

**Use Case:** Priority to queue display with TV as secondary

**Implementation:**
```html
<!-- Your main queue display -->
<div id="queue-content">
    <!-- Queue information -->
</div>

<!-- TV in corner -->
<div style="position: fixed; bottom: 20px; right: 20px; 
            width: 320px; height: 180px; z-index: 1000;
            border: 2px solid #333; border-radius: 8px; overflow: hidden;">
    <iframe src="/www/dev/tv2-player.html" 
            style="width: 100%; height: 100%; border: none;">
    </iframe>
</div>
```

**Styling Options:**
```css
/* Draggable position */
.tv-corner {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 400px;
    height: 225px;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    border-radius: 8px;
    overflow: hidden;
}

/* Adjustable sizes */
.tv-small { width: 320px; height: 180px; }
.tv-medium { width: 480px; height: 270px; }
.tv-large { width: 640px; height: 360px; }
```

---

### Option 4: Scheduled Display (Queue → TV → Queue)

Automatically switch between queue and TV at specific times.

**Use Case:** Show TV during break times, queue during clinic hours

**Implementation in `bin/cron.php`:**
```php
$jobby->add('ScheduledTV', [
    'closure' => function() use ($app) {
        $hour = (int)date('H');
        $minute = (int)date('i');
        
        // TV Time: 12:00-13:00 (lunch) and 17:00-18:00 (evening)
        $isTVTime = ($hour == 12 || $hour == 17);
        
        $config = \Rasque\Config::instance();
        $currentMode = $config->get('display_mode', 'queue');
        
        if ($isTVTime && $currentMode !== 'tv') {
            // Switch to TV
            $config->set('display_mode', 'tv');
            
            // Kill queue display processes if needed
            // Start TV player
            $basePath = $app->getBasePath();
            shell_exec("nohup php {$basePath}/bin/play-tv2-live.php > /dev/null 2>&1 &");
            
            \Rasque\Logger::instance()->log('switched_to_tv');
            
        } elseif (!$isTVTime && $currentMode !== 'queue') {
            // Switch back to queue
            $config->set('display_mode', 'queue');
            
            // Kill TV processes
            $app->kill('play-tv2-live.php');
            
            \Rasque\Logger::instance()->log('switched_to_queue');
        }
    },
    'schedule' => '* * * * *' // Check every minute
]);
```

**Custom Schedule:**
```php
// Example: Different schedules for different days
function shouldShowTV() {
    $day = date('w'); // 0 (Sunday) to 6 (Saturday)
    $hour = (int)date('H');
    
    // Weekdays: Lunch (12-13) and evening (17-18)
    if ($day >= 1 && $day <= 5) {
        return ($hour == 12 || $hour == 17);
    }
    
    // Weekends: Show TV all day
    if ($day == 0 || $day == 6) {
        return true;
    }
    
    return false;
}
```

---

## 🎯 Comparison: OMX vs Live TV2 Integration

### OMX Player (Traditional)

**Pros:**
- Hardware accelerated
- Low CPU usage
- Works with local video files
- Reliable for offline content

**Cons:**
- Overlaps webpage (requires manual positioning)
- Cannot interact with webpage elements underneath
- Positioning needs to be adjusted for each display
- Z-index cannot be controlled

**Best For:**
- Local ad playback
- Simple displays without complex layouts
- Reliable offline operation

### Live TV2 (HTML5)

**Pros:**
- Embeds in webpage naturally
- Responsive to page layout
- Can be positioned anywhere in DOM
- Works with CSS/flexbox/grid
- Interactive page elements not blocked

**Cons:**
- Higher CPU usage (software decoding)
- Requires internet for live streaming
- Depends on browser HLS support

**Best For:**
- Live streaming integration
- Complex page layouts
- Side-by-side or PIP displays
- Responsive designs

---

## 📐 Layout Examples

### Full Width TV, Queue Below

```html
<div class="container">
    <!-- TV at top -->
    <div class="tv-section" style="width: 100%; height: 400px;">
        <iframe src="/www/dev/tv2-player.html" 
                style="width: 100%; height: 100%; border: none;">
        </iframe>
    </div>
    
    <!-- Queue below -->
    <div class="queue-section" style="padding: 20px;">
        <!-- Queue numbers and information -->
    </div>
</div>
```

### Split Screen (50/50)

```html
<div style="display: flex; height: 100vh;">
    <div style="flex: 1; overflow: auto;">
        <!-- Queue content -->
    </div>
    <div style="flex: 1;">
        <iframe src="/www/dev/tv2-player.html" 
                style="width: 100%; height: 100%; border: none;">
        </iframe>
    </div>
</div>
```

### TV in Waiting Area Layout

```html
<div class="waiting-area">
    <!-- Header -->
    <header class="clinic-header">
        <h1>Welcome to [Clinic Name]</h1>
    </header>
    
    <!-- Main content area -->
    <div class="main-content" style="display: flex; gap: 20px; padding: 20px;">
        <!-- Queue display (left) -->
        <div class="queue-board" style="flex: 1;">
            <h2>Current Queue</h2>
            <!-- Queue numbers -->
        </div>
        
        <!-- TV (right) -->
        <div class="tv-display" style="flex: 2;">
            <iframe src="/www/dev/tv2-player.html" 
                    style="width: 100%; height: 600px; border: none; border-radius: 8px;">
            </iframe>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="clinic-footer">
        <p>For assistance, please see the reception desk</p>
    </footer>
</div>
```

---

## 🔄 Dynamic Content Switching

### JavaScript-based Switching

```javascript
// Switch between queue and TV dynamically
function showQueue() {
    document.getElementById('queue-display').style.display = 'block';
    document.getElementById('tv-display').style.display = 'none';
}

function showTV() {
    document.getElementById('queue-display').style.display = 'none';
    document.getElementById('tv-display').style.display = 'block';
}

function showBoth() {
    document.getElementById('queue-display').style.display = 'block';
    document.getElementById('tv-display').style.display = 'block';
}

// Auto-switch based on time
function autoSwitch() {
    const hour = new Date().getHours();
    
    if (hour >= 12 && hour < 13) {
        showTV(); // Lunch time - show TV
    } else if (hour >= 17 && hour < 18) {
        showTV(); // Evening break - show TV
    } else {
        showQueue(); // Clinic hours - show queue
    }
}

// Check every minute
setInterval(autoSwitch, 60000);
autoSwitch(); // Initial check
```

---

## 🎨 Styling the TV Player

### Custom Frame Styling

```css
.tv-player-frame {
    border: 3px solid #333;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    overflow: hidden;
}

.tv-player-frame.fullscreen {
    border: none;
    border-radius: 0;
}
```

### Responsive TV Display

```css
.tv-container {
    position: relative;
    width: 100%;
    padding-bottom: 56.25%; /* 16:9 aspect ratio */
}

.tv-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: none;
}
```

---

## 📱 Responsive Design

### Mobile/Tablet Considerations

```css
/* Desktop: Side by side */
@media (min-width: 1024px) {
    .layout {
        display: flex;
    }
    .queue { flex: 1; }
    .tv { flex: 1; }
}

/* Tablet: Stacked */
@media (min-width: 768px) and (max-width: 1023px) {
    .layout {
        display: block;
    }
    .tv { height: 400px; }
}

/* Mobile: Queue only (hide TV) */
@media (max-width: 767px) {
    .tv { display: none; }
}
```

---

## 🧪 Testing Your Integration

### 1. Test Player Alone
```
http://localhost/.../www/dev/tv2-player.html
```

### 2. Test in Your Page
```html
<iframe src="/www/dev/tv2-player.html" width="800" height="450"></iframe>
```

### 3. Check Stream URL
```bash
cat www/dev/tv2-stream-url.txt
```

### 4. Monitor Logs
```bash
tail -f /path/to/logs
```

---

## 📊 Performance Monitoring

### CPU Usage
```bash
# Monitor CPU usage
top -p $(pgrep -f "play-tv2-live.php")
```

### Memory Usage
```bash
# Monitor memory
ps aux | grep chromium
```

### Network Usage
```bash
# Monitor network traffic
iftop
```

---

## 🚨 Troubleshooting Integration

### TV Not Showing
1. Check if player type is set: `cat config.json`
2. Check if URL extracted: `cat www/dev/tv2-stream-url.txt`
3. Check if process running: `ps aux | grep play-tv2-live`
4. Check browser console for errors

### Layout Issues
1. Use browser dev tools (F12) to inspect
2. Check iframe dimensions
3. Verify CSS not conflicting
4. Test player alone first

### Performance Issues
1. Reduce video quality if possible
2. Check network bandwidth
3. Monitor CPU/memory usage
4. Consider switching to OMX for local content

---

## 💡 Best Practices

1. **Test Offline**: Ensure queue display works if TV stream fails
2. **Graceful Degradation**: Hide TV player if stream unavailable
3. **Responsive Design**: Adapt layout to screen size
4. **Performance**: Monitor CPU/memory, optimize as needed
5. **User Experience**: Don't let TV distract from queue information
6. **Accessibility**: Ensure queue info is clearly visible
7. **Maintenance**: Regularly update stream URLs

---

## 📞 Support

For more information:
- Setup Guide: `LIVE-TV2-SETUP.md`
- Quick Reference: `PLAYER-SETUP-SUMMARY.md`
- Implementation: `QUEUESCREEN-PLAYER-UPDATE.md`

---

**Happy Integrating! 🎉**

