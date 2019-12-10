<?php

header('Content-Type: application/json');

$contents = @file_get_contents('http://localhost:4040/api/tunnels');

if (!$contents)
    die(json_encode(['error' => 'not started']));

$ngrok = json_decode($contents, true);

$connectionUrl = $ngrok['tunnels'][0]['public_url'];

die(json_encode(['data' => $connectionUrl]));
