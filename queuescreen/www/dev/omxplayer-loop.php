<?php

use Rasque\App;
use Rasque\Config;

require_once __DIR__ . '/../../sources/vendor/autoload.php';

$app = App::instance();
$config = Config::instance();

$volume = $_GET['volume'];
$x = $_GET['x'];
$y = $_GET['y'];
$width = $_GET['width'];
$height = $_GET['height'];

// Get player type from config
$playerType = $config->get('player_type', 'omx');

if (isset($_GET['refresh'])) {
    // kill processes based on player type
    if ($playerType === 'live-tv2') {
        $app->kill('play-tv2-live.php');
    } else {
        $app->kill('play-ad.php');
        $app->kill('omxplayer');
    }
}

// Route to appropriate player based on config
if ($playerType === 'live-tv2') {
    // Live TV2 HTML5 Player - no OMX overlay
    
    // check first if process already running
    if ($app->processIsRunning('play-tv2-live.php'))
        die('already running');
    
    $path = $app->getPath("bin/play-tv2-live.php");
    
    // Start the Live TV2 player in background
    shell_exec('nohup php ' . $path . ' > /dev/null 2>&1 &');
    
    echo 'Live TV2 player started (HTML5 mode - no OMX overlay)';
    
} else {
    // Traditional OMX Player (existing flow)
    
    // check first if process already running
    if ($app->processIsRunning('play-ad.php'))
        die('already running');
    
    $path = $app->getPath("bin/play-ad.php");
    
    shell_exec('php ' . $path . ' ' . $x . ' ' . $y . ' ' . $width . ' ' . $height . ' ' . $volume);
}