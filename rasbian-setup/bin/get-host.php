<?php

$config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);

echo isset($config['host']) ? $config['host'] : 'http://qmed.asia';
