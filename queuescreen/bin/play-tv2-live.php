<?php

/**
 * Live TV2 Player (HTML5 - No OMX)
 * 
 * This script handles the Live TV2 streaming using HTML5 player
 * instead of OMX overlay. The video is embedded in the webpage.
 * 
 * Usage: php play-tv2-live.php [stream_url]
 */

use Rasque\Logger;

require_once __DIR__ . '/../sources/vendor/autoload.php';

$logger = Logger::instance();

// Get stream URL from argument or use default
$streamUrl = isset($argv[1]) ? $argv[1] : null;

// If no URL provided, try to extract it
if (!$streamUrl) {
    $logger->log('tv2_extracting_url');
    
    // Try to extract URL using yt-dlp
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        // Linux/Raspberry Pi
        $streamUrl = trim(shell_exec('yt-dlp -g "https://rtmklik.rtm.gov.my/live/tv2" 2>/dev/null | head -n 1'));
        
        if (empty($streamUrl)) {
            // Fallback to cached URL
            $cacheFile = __DIR__ . '/../test/stream-url.txt';
            if (file_exists($cacheFile)) {
                $streamUrl = trim(file_get_contents($cacheFile));
                $logger->log('tv2_using_cached_url');
            }
        }
    }
}

if (!$streamUrl) {
    $logger->log('tv2_no_stream_url');
    die('Error: Could not get stream URL. Please run: yt-dlp -g "https://rtmklik.rtm.gov.my/live/tv2"' . PHP_EOL);
}

// Save the stream URL for the HTML player to access
$urlCachePath = __DIR__ . '/../www/dev/tv2-stream-url.txt';
file_put_contents($urlCachePath, $streamUrl);

$logger->log('tv2_player_started', ['url' => $streamUrl]);

echo "Live TV2 Player Started" . PHP_EOL;
echo "Stream URL: " . $streamUrl . PHP_EOL;
echo "The video will be displayed in the webpage (no OMX overlay)" . PHP_EOL;
echo "Press Ctrl+C to stop" . PHP_EOL;

// Keep the script running to maintain the URL cache
while (true) {
    // Check if URL needs refresh every 4 hours
    $fileTime = filemtime($urlCachePath);
    if (time() - $fileTime > 14400) { // 4 hours
        // Try to refresh URL using smart extractor
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $logger->log('tv2_url_refresh_attempt');
            
            // Try automatic extractor first
            $autoExtractScript = __DIR__ . '/../extract-url-auto.sh';
            if (file_exists($autoExtractScript)) {
                shell_exec('chmod +x ' . $autoExtractScript);
                shell_exec('sh ' . $autoExtractScript . ' > /dev/null 2>&1');
            } else {
                // Fallback to yt-dlp
                $newUrl = trim(shell_exec('yt-dlp -g "https://rtmklik.rtm.gov.my/live/tv2" 2>/dev/null | head -n 1'));
                if (!empty($newUrl)) {
                    file_put_contents($urlCachePath, $newUrl);
                }
            }
            
            // Check if refresh was successful
            if (file_exists($urlCachePath)) {
                $newUrl = trim(file_get_contents($urlCachePath));
                if (!empty($newUrl)) {
                    $logger->log('tv2_url_refreshed', ['url' => substr($newUrl, 0, 60) . '...']);
                }
            }
        }
    }
    
    sleep(300); // Check every 5 minutes
}

