<?php

require_once __DIR__ . '/../sources/vendor/autoload.php';

$jobby = new \Jobby\Jobby();

$jobby->add('Ping', [
    'closure' => function() {
        $config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);

        $base = isset($config['host']) ? $config['host'] : 'https://qmed.asia';

        @file_get_contents($base . '/api/installation/' . $config['installation_id'] . '/ping/device/' . $config['device_name']);
    },
   'schedule' => '* * * * *'
]);

$jobby->run();
