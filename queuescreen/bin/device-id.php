<?php

require_once __DIR__ . '/../sources/vendor/autoload.php';

echo \Rasque\DeviceInfo::create()->getDeviceId();
