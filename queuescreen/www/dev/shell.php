<?php

if (!isset($_POST))
    return;
    
$commands = [];

Header('Content-Type: application/json');

$commands['reboot'] = function() {
    return shell_exec('sudo reboot -f');
};

$commands['ngrok_start'] = function() {
    $contents = @file_get_contents('http://localhost:4040/api/tunnels');

    if (!$contents) {
        if (!isset($_POST['authtoken']))
            die(json_encode(['error' => 'authtoken is missing']));

        shell_exec('/home/pi/ngrok tcp 22 authtoken ' . $_POST['authtoken']);

        $contents = @file_get_contents('http://localhost:4040/api/tunnels');

        if (!$contents)
            die(json_encode(['error' => 'authtoken is incorrect']));
    }

    $ngrokXml = simplexml_load_string($contents);

    $connectionUrl = $ngrokXml->Tunnels->PublicURL;

    die(json_encode(['data' => $connectionUrl]));
};

if (isset($_POST['command']) && isset($commands[$_POST['command']])) {
    $commands[$_POST['command']]();
}

if (isset($_POST['shell'])) {
    echo json_encode(['data' => 'output']);
}
