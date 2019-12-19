<?php

require_once __DIR__ . '/../../sources/vendor/autoload.php';

$app = \Rasque\App::instance();

echo $app->processIsRunning('play-ad.php') ? 'is_running' : 'is_not_running';