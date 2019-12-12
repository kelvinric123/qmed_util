<?php

require_once __DIR__ . '/../sources/vendor/autoload.php';

$app = \Rasque\App::instance();

$jobby = new \Jobby\Jobby();

$jobby->add('Ping', [
    'closure' => function() use ($app) {
        $response = \Rasque\Logger::create()->ping();

        if (!$response)
            return;

        if (isset($response['ads_need_reboot'])) {
            $app->reboot('reboot_ads_inactive');
            return;
        }
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
            return $app->reboot('reboot_inactive');
//            \Rasque\Logger::create()->log('inactive_reboot');
//            shell_exec('sudo reboot -f');
        }

        return null;
    },
    'schedule' => '*/5 * * * *'
]);

$jobby->run();
