<?php

$config = parse_ini_file(__DIR__ . '/config.ini');

$base = isset($config['host']) ? $config['host'] : 'https://qmed.asia';

// ping
@file_get_contents($base . '/api/installation/' . $config['installation_id'] . '/ping/device/' . $config['device_name']);