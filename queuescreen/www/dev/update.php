<?php

use Rasque\App;

require_once __DIR__ . '/../../sources/vendor/autoload.php';

$app = App::instance();

if (isset($_GET['soft-update'])) {
    $path = $app->getPath('bin/soft-update.sh');
    shell_exec('sh ' . $path);
    return;
}

// run code update
$path = $app->getPath('bin/update.sh');

$app->log('device_update' . (isset($_GET['version']) ? '_' . $_GET['version'] : ''));

if (isset($_GET['version']))
    shell_exec('sh ' . $path . ' ' . $_GET['version']);
else
    shell_exec('sh ' . $path);