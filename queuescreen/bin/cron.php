<?php

require_once __DIR__ . '/../sources/vendor/autoload.php';

$app = \Rasque\App::instance();

$jobby = new \Jobby\Jobby();

$jobby->add('Ping', [
    'closure' => function() {
        \Rasque\Logger::create()->log('ping');
    },
   'schedule' => '* * * * *'
]);

$jobby->add('RunningCheck', [
    'closure' => function() use ($app) {
        $time = @file_get_contents($app->getBasePath() . '/running-timestamp');
        
        // skip first
        if (!$time)
            return;
            
        // if in-active for the last 10 minutes, restart
        if (time() > strtotime('+5 minutes', $time)) {
            \Rasque\Logger::create()->log('inactive_reboot');
            shell_exec('sudo reboot -f');
            return;         
        }
    },
    'schedule' => '*/5 * * * *'
]);

$jobby->run();
