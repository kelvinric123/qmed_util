<?php
/**
 * Standalone Live TV2 Player Test
 * No setup required - just test if the player works
 */

echo "========================================\n";
echo "Live TV2 Player - Standalone Test\n";
echo "========================================\n\n";

// Check if yt-dlp is installed
echo "Step 1: Checking yt-dlp...\n";
$ytdlpCheck = shell_exec('command -v yt-dlp 2>/dev/null');

if (empty($ytdlpCheck)) {
    echo "  yt-dlp not found. Installing...\n";
    echo "  This may take a moment...\n\n";
    
    shell_exec('pip3 install yt-dlp 2>&1');
    
    $ytdlpCheck = shell_exec('command -v yt-dlp 2>/dev/null');
    
    if (empty($ytdlpCheck)) {
        echo "  Trying with sudo...\n";
        shell_exec('sudo pip3 install yt-dlp 2>&1');
        
        $ytdlpCheck = shell_exec('command -v yt-dlp 2>/dev/null');
        
        if (empty($ytdlpCheck)) {
            die("  ❌ Failed to install yt-dlp. Please run: sudo pip3 install yt-dlp\n\n");
        }
    }
    
    echo "  ✅ yt-dlp installed!\n\n";
} else {
    echo "  ✅ yt-dlp already installed\n\n";
}

// Extract stream URL
echo "Step 2: Extracting RTM TV2 stream URL...\n";
echo "  This may take 30-60 seconds...\n";
echo "  Please wait...\n\n";

$streamUrl = trim(shell_exec('yt-dlp -g "https://rtmklik.rtm.gov.my/live/tv2" 2>/dev/null | head -n 1'));

if (empty($streamUrl)) {
    echo "  ❌ Failed to extract URL\n\n";
    echo "  Possible reasons:\n";
    echo "  1. No internet connection\n";
    echo "  2. RTM website is down\n";
    echo "  3. yt-dlp needs update: pip3 install -U yt-dlp\n\n";
    
    echo "  You can try manually:\n";
    echo "  yt-dlp -g \"https://rtmklik.rtm.gov.my/live/tv2\"\n\n";
    exit(1);
}

echo "  ✅ Stream URL extracted!\n\n";
echo "Stream URL:\n";
echo $streamUrl . "\n\n";

// Save to cache file
$cacheFile = __DIR__ . '/www/dev/tv2-stream-url.txt';
$cacheDir = dirname($cacheFile);

if (!file_exists($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

file_put_contents($cacheFile, $streamUrl);

echo "✅ URL saved to: $cacheFile\n\n";

echo "========================================\n";
echo "Test Complete!\n";
echo "========================================\n\n";

echo "Now you can:\n";
echo "1. Open browser to: http://localhost/qmed_util/queuescreen/www/dev/tv2-player.html\n";
echo "2. Or use omxplayer: omxplayer --live \"$streamUrl\"\n\n";

echo "The player is ready to use!\n\n";

