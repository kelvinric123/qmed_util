<?php

// todo wifi strenght
// todo internet 
// todo resource check

require_once __DIR__ . '/../../sources/vendor/autoload.php';

header('Content-Type: application/json');

echo json_encode((new \Rasque\ResourceParam())->toArray(), JSON_PRETTY_PRINT);
