<?php

// todo wifi strenght
// todo internet 
// todo resource check

require_once __DIR__ . '/../../sources/vendor/autoload.php';

header('Content-Type: application/json');

$app = \Rasque\App::instance();

// update timestamp each time this file is accessed
file_put_contents($app->getBasePath() . '/running-timestamp', time());

echo json_encode((new \Rasque\ResourceParam())->toArray(), JSON_PRETTY_PRINT);
