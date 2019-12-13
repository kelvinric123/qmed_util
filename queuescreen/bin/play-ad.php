<?php

while (true) {
    $adsPath = realpath(__DIR__ . '/../www/omx-ads');
    $playlistPath = realpath(__DIR__ . '/../www/omx-ads/playlist-map.json');
    
    $playlist = @file_get_contents($playlistPath);

    $playlist = json_decode($playlist, true);

    foreach ($playlist['playlist'] as $media) {
        $name = $media['filename'];
        
        $path = $adsPath . '/' . $name;
        
        if (!file_exists($path))
            continue;
            
        $top = 10;
        $left = 10;
        $width = 1581;
        $height = 960;
        
        shell_exec('omxplayer --win "' . $top .' ' . $left . ' ' . $width .' ' . $height . '" ' . $path);
    }
}
