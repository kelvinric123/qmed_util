<?php

namespace Rasque;

use GuzzleHttp\Client;

class Logger
{
    /**
     * @var Client
     */
    protected $http;

    /**
     * @var string
     */
    protected $installationId;

    /**
     * @var int
     */
    protected $screenId;

    public function __construct(Client $http, $installationId, $screenId)
    {
        $this->http = $http;
        $this->installationId = $installationId;
        $this->screenId = $screenId;
    }

    public static function create()
    {
        $configPath = __DIR__ . '/../../config.json';

        $config = json_decode(file_get_contents($configPath), true);

        return new static(new Client(['base_uri' => isset($config['host']) ? $config['host'] : 'https://qmed.asia']), $config['installation_id'], $config['screen_id']);
    }

    public function log($type, array $params = null)
    {
        $data = [
            'resource' => [],
            'params' => $params
        ];

        $this->http->post('/apis/installations/' . $this->installationId . '/screens/' . $this->screenId . '/logs/' . $type, [
            'json' => [
                'data' => $data
            ]
        ]);
    }
}