<?php

use Rasque\App;

require_once  __DIR__ . '/../../sources/vendor/autoload.php';

$app = App::instance();

error_reporting(E_ALL);

if (!isset($_POST))
    return;

$data = isset($_POST['data']) ? $_POST['data'] : [];

$commands = [];
//Header('Content-Type: application/json');

$commands['reboot'] = function() {
    return shell_exec('sudo reboot -f');
};

$commands['soft_reboot'] = function() {
    return shell_exec('sudo reboot');
};

$commands['ngrok_start'] = function() {
    $contents = @file_get_contents('http://localhost:4040/api/tunnels');

    if (!$contents) {
        if (!isset($_POST['authtoken']))
            die(json_encode(['error' => 'authtoken is missing']));

        shell_exec('/home/pi/ngrok tcp 22 --authtoken=' . $_POST['authtoken'] . ' > /dev/null &');
    }
};

$commands['ngrok_url'] = function() {
    $contents = @file_get_contents('http://localhost:4040/api/tunnels');

    $ngrok = json_decode($contents, true);

    $connectionUrl = $ngrok['tunnels'][0]['public_url'];

    die(json_encode(['data' => $connectionUrl]));
};

$commands['ngrok_end'] = function() {
    $aux = shell_exec('ps auxww | grep ngrok');
    
    foreach (explode("\n", $aux) as $line) {
        if (strpos($line, 'home') === false)
            continue;
            
        if (strpos($line, 'sh -c') !== false)
            continue;
            
        $parts = preg_split('/\s+/', $line);
        
        $pid = $parts[1];
        
        shell_exec('kill ' . $pid);
    }
};

$commands['update'] = function() use ($app) {
    // run code update
    $path = $app->getPath('bin/update.sh');

    shell_exec('sh ' . $path);
};

$commands['kill_ads'] = function() use ($app) {
    $app->killAds();
};

if (isset($_POST['command']) && isset($commands[$_POST['command']])) {
    $commands[$_POST['command']]();
}

if (isset($_POST['shell'])) {
    echo json_encode(['data' => 'output']);
}
