<?php

use Rasque\Logger;

require_once __DIR__ . '/../sources/vendor/autoload.php';

$logger = Logger::instance();

if (!isset($argv[4]))
    return $logger->log('play_lack_args');

$x = $argv[1];
$y = $argv[2];
$width = $argv[3];
$height = $argv[4];

$volume = isset($argv[5]) ? $argv[5] : 1500;

// hard cap
//$volume = $volume > 2000 ? $volume: 2700;
$logger->log('ad_player_start');

while (true) {
    $adsPath = realpath(__DIR__ . '/../www/ads');
    $playlistPath = realpath(__DIR__ . '/../www/ads/playlist-map.json');
    
    $playlist = @file_get_contents($playlistPath);

    $playlist = json_decode($playlist, true);

    foreach ($playlist['playlist'] as $media) {
        $name = $media['filename'];
        
        $path = $adsPath . '/' . $name;
        
        if (!file_exists($path))
            continue;

        $start = date('Y-m-d H:i:s');
        shell_exec('omxplayer --win "' . $y .' ' . $x . ' ' . $width .' ' . $height . '" ' . $path . ' --vol -' . $volume);
        $end = date('Y-m-d H:i:s');

        $logger->logPlaytime($media['id'], $start, $end);
    }
}
