<?php

/**
 * Live TV2 Player Endpoint
 * 
 * This endpoint serves the stream URL for the HTML5 player
 * Returns JSON with the current stream URL
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$urlCachePath = __DIR__ . '/tv2-stream-url.txt';

$response = [
    'status' => 'error',
    'message' => 'Stream URL not available',
    'url' => null,
    'timestamp' => time()
];

if (file_exists($urlCachePath)) {
    $streamUrl = trim(file_get_contents($urlCachePath));
    
    if (!empty($streamUrl)) {
        $response = [
            'status' => 'success',
            'message' => 'Stream URL available',
            'url' => $streamUrl,
            'timestamp' => time(),
            'cached_at' => filemtime($urlCachePath)
        ];
    }
}

echo json_encode($response, JSON_PRETTY_PRINT);

