<?php

$config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);

echo $config['device_name'];
