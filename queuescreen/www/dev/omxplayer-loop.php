<?php

use Rasque\App;

require_once __DIR__ . '/../../sources/vendor/autoload.php';

$app = App::instance();

$volume = $_GET['volume'];
$x = $_GET['x'];
$y = $_GET['y'];
$width = $_GET['width'];
$height = $_GET['height'];

if (isset($_GET['refresh'])) {
    // kill process
    $app->kill('play-ad.php');
    $app->kill('omxplayer');
}

// check first if process already running
if ($app->processIsRunning('play-ad.php'))
    die('already running');

$path = $app->getPath("bin/play-ad.php");

shell_exec('php ' . $path . ' ' . $x . ' ' . $y . ' ' . $width . ' ' . $height . ' ' . $volume);