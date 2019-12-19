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

    public function ping($noWait = false)
    {
        try {
            if ($noWait)
                return $this->log('ping', [], ['timeout' => 1]);

            $response = $this->log('ping', []);

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
     * @param array $guzzleOpts
     * @return null|\Psr\Http\Message\ResponseInterface
     */
    public function log($type, array $params = null, $guzzleOpts = [])
    {
        $data = [
            'version' => App::instance()->getVersion(),
            'resource' => (new ResourceParam())->toArray(),
            'params' => $params
        ];

        $options = [
            'json' => [
                'data' => $data
        ]];

        $options = array_merge($options, $guzzleOpts);

        try {
            return $this->http->post('/apis/installations/screens/' . $this->deviceId . '/logs/' . $type, $options);
        } catch (\Exception $e) {
            return null;
        }
    }
}
