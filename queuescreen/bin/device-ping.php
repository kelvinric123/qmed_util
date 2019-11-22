<?php

$config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);

$base = isset($config['host']) ? $config['host'] : 'https://qmed.asia';

// ping
@file_get_contents($base . '/api/installation/' . $config['installation_id'] . '/ping/device/' . $config['device_name']);