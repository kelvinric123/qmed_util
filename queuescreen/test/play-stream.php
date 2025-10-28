<?php
/**
 * RTM TV2 Stream Player - PHP Wrapper
 * 
 * This script integrates RTM TV2 streaming with the queuescreen system.
 * Usage: php play-stream.php [x] [y] [width] [height] [volume]
 * 
 * Example: php play-stream.php 0 0 1920 1080 500
 */

// Check if running on Windows (development) or Linux (production)
$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

// Get arguments
$x = isset($argv[1]) ? (int)$argv[1] : 0;
$y = isset($argv[2]) ? (int)$argv[2] : 0;
$width = isset($argv[3]) ? (int)$argv[3] : 1920;
$height = isset($argv[4]) ? (int)$argv[4] : 1080;
$volume = isset($argv[5]) ? (int)$argv[5] : 0; // 0 = max volume for omxplayer

echo "RTM TV2 Stream Player\n";
echo "=====================\n";
echo "Position: ($x, $y)\n";
echo "Size: {$width}x{$height}\n";
echo "Volume: $volume\n\n";

if ($isWindows) {
    // Windows development mode - open in default browser
    echo "Running in Windows development mode...\n";
    
    $streamUrl = realpath(__DIR__ . '/stream-player.html');
    
    if (!$streamUrl) {
        echo "Error: stream-player.html not found!\n";
        exit(1);
    }
    
    // Convert to file:// URL
    $streamUrl = 'file://' . str_replace('\\', '/', $streamUrl);
    
    echo "Opening: $streamUrl\n";
    
    // Open in default browser
    shell_exec('start "" "' . $streamUrl . '"');
    
    echo "\n✓ Stream opened in browser\n";
    echo "Press Ctrl+C to stop\n";
    
    // Keep script running
    while (true) {
        sleep(5);
    }
    
} else {
    // Linux (Raspberry Pi) mode - use the shell script
    echo "Running in Raspberry Pi mode...\n";
    
    $scriptPath = __DIR__ . '/play-stream.sh';
    
    if (!file_exists($scriptPath)) {
        echo "Error: play-stream.sh not found!\n";
        exit(1);
    }
    
    // Make sure script is executable
    chmod($scriptPath, 0755);
    
    // Execute the shell script
    $command = "sh $scriptPath $x $y $width $height $volume";
    
    echo "Executing: $command\n";
    
    passthru($command, $exitCode);
    
    if ($exitCode !== 0) {
        echo "Error: Stream player exited with code $exitCode\n";
        exit($exitCode);
    }
}

