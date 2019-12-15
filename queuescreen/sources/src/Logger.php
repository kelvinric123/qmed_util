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

    /** @var Logger $instance */
    protected static $instance;

    protected function __construct(Client $http, $deviceId)
    {
        $this->http = $http;
        $this->deviceId = $deviceId;
    }

    public static function instance()
    {
        if (!static::$instance) {
//            $configPath = __DIR__ . '/../../config.json';
//
//            $config = json_decode(file_get_contents($configPath), true);

            $config = Config::instance();

            return new static(new Client(['base_uri' => $config->get('host', 'http://qmed.asia')]), DeviceInfo::create()->getDeviceId());
        }

        return static::$instance;
    }

    public function ping()
    {
        try {
            $response = $this->log('ping');

            if (!$response)
                return null;

            $response = @json_decode($response->getBody(), true);

            if (!$response)
                return null;

            return $response['data'];
        } catch (\Exception $e) {
            return null;
        }
    }

    public function logPlaytime($mediaId, $start, $end)
    {
        return $this->http->post('/apis/installations/screens/' . $this->deviceId . '/ads/playtime/logs', [
            'json' => [
                'media_id' => $mediaId,
                'start_at' => $start,
                'end_at' => $end
            ]
        ]);
    }

    /**
     * @param $type
     * @param array|null $params
     * @return \Psr\Http\Message\ResponseInterface|null
     */
    public function log($type, array $params = null)
    {
        $data = [
            'version' => App::instance()->getVersion(),
            'resource' => (new ResourceParam())->toArray(),
            'params' => $params
        ];

        try {
            return $this->http->post('/apis/installations/screens/' . $this->deviceId . '/logs/' . $type, [
                'json' => [
                    'data' => $data
                ]
            ]);
        } catch (\Exception $e) {
            return null;
        }
    }
}
