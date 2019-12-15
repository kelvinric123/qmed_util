<?php

use Rasque\App;

require_once __DIR__ . '/../../sources/vendor/autoload.php';

$app = App::instance();

// run code update
$path = $app->getPath('bin/update.sh');

shell_exec('sh ' . $path);