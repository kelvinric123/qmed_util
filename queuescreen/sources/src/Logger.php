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
     * @var int
     */
    protected $screenId;

    /** @var string */
    protected $deviceId;

    public function __construct(Client $http, $deviceId)
    {
        $this->http = $http;
        $this->deviceId = $deviceId;
    }

    public static function create()
    {
        $configPath = __DIR__ . '/../../config.json';

        $config = json_decode(file_get_contents($configPath), true);

        return new static(new Client(['base_uri' => isset($config['host']) ? $config['host'] : 'https://qmed.asia']), DeviceInfo::create()->getDeviceId());
    }

    public function ping()
    {
        try {
            $response = $this->log('ping');

            return $response['data'];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param $type
     * @param array|null $params
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function log($type, array $params = null)
    {
        $data = [
            'resource' => (new ResourceParam())->toArray(),
            'params' => $params
        ];

        return $this->http->post('/apis/installations/screens/' . $this->deviceId . '/logs/' . $type, [
            'json' => [
                'data' => $data
            ]
        ]);
    }
}
